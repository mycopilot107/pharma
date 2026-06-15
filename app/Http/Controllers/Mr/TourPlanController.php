<?php

namespace App\Http\Controllers\Mr;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TourPlan;
use App\Models\TourPlanStop;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TourPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = TourPlan::where('user_id', $request->user()->id)
            ->withCount('stops')
            ->latest('week_start')
            ->paginate(10);

        return view('mr.tour-plans.index', compact('plans'));
    }

    public function create(Request $request)
    {
        $weekStart = Carbon::parse(
            $request->input('week_start', Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString())
        )->startOfWeek(Carbon::MONDAY);

        $existing = TourPlan::where('user_id', $request->user()->id)
            ->where('week_start', $weekStart->toDateString())
            ->first();

        if ($existing) {
            return redirect()->route('mr.tour-plans.show', $existing)
                ->with('info', 'A plan for this week already exists.');
        }

        [$customers, $days] = $this->formData($request, $weekStart);

        return view('mr.tour-plans.create', compact('weekStart', 'customers', 'days'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'week_start'              => ['required', 'date'],
            'notes'                   => ['nullable', 'string', 'max:2000'],
            'stops'                   => ['nullable', 'array'],
            'stops.*.customer_id'     => ['required', 'exists:customers,id'],
            'stops.*.day_of_week'     => ['required', 'integer', 'between:1,6'],
            'stops.*.area'            => ['nullable', 'string', 'max:255'],
        ]);

        $user      = $request->user();
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY)->toDateString();

        if (TourPlan::where('user_id', $user->id)->where('week_start', $weekStart)->exists()) {
            return back()->with('error', 'A tour plan for this week already exists.');
        }

        $plan = TourPlan::create([
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'week_start' => $weekStart,
            'notes'      => $validated['notes'] ?? null,
            'status'     => 'draft',
        ]);

        $this->saveStops($plan, $validated['stops'] ?? []);

        return redirect()->route('mr.tour-plans.show', $plan)
            ->with('success', 'Tour plan created. Add more stops or submit for approval.');
    }

    public function show(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id, 403);

        $tourPlan->load([
            'stops' => fn ($q) => $q->with('customer')->orderBy('day_of_week')->orderBy('sort_order'),
            'reviewer',
        ]);

        $planVsActual = $this->buildPlanVsActual($tourPlan, $request->user()->id);

        return view('mr.tour-plans.show', compact('tourPlan', 'planVsActual'));
    }

    public function edit(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id && $tourPlan->isDraft(), 403);

        $tourPlan->load('stops.customer');
        $weekStart = $tourPlan->week_start;
        [$customers, $days] = $this->formData($request, $weekStart);

        return view('mr.tour-plans.create', compact('tourPlan', 'weekStart', 'customers', 'days'));
    }

    public function update(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id && $tourPlan->isDraft(), 403);

        $validated = $request->validate([
            'notes'               => ['nullable', 'string', 'max:2000'],
            'stops'               => ['nullable', 'array'],
            'stops.*.customer_id' => ['required', 'exists:customers,id'],
            'stops.*.day_of_week' => ['required', 'integer', 'between:1,6'],
            'stops.*.area'        => ['nullable', 'string', 'max:255'],
        ]);

        $tourPlan->update(['notes' => $validated['notes'] ?? null]);
        $tourPlan->stops()->delete();
        $this->saveStops($tourPlan, $validated['stops'] ?? []);

        return redirect()->route('mr.tour-plans.show', $tourPlan)
            ->with('success', 'Tour plan updated.');
    }

    public function submit(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id && $tourPlan->isDraft(), 403);

        if ($tourPlan->stops()->count() === 0) {
            return back()->with('error', 'Add at least one stop before submitting.');
        }

        $tourPlan->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('mr.tour-plans.show', $tourPlan)
            ->with('success', 'Tour plan submitted for approval.');
    }

    public function destroy(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id && $tourPlan->isDraft(), 403);

        $tourPlan->delete();

        return redirect()->route('mr.tour-plans.index')
            ->with('success', 'Plan deleted.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function formData(Request $request, Carbon $weekStart): array
    {
        $customers = Customer::where('company_id', $request->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'area']);

        $days = [];
        for ($i = 1; $i <= 6; $i++) {
            $days[$i] = $weekStart->copy()->addDays($i - 1)->format('D, d M');
        }

        return [$customers, $days];
    }

    private function saveStops(TourPlan $plan, array $stops): void
    {
        $order = [];
        foreach ($stops as $stop) {
            $day = (int) $stop['day_of_week'];
            $order[$day] = ($order[$day] ?? 0) + 1;

            TourPlanStop::create([
                'tour_plan_id' => $plan->id,
                'customer_id'  => $stop['customer_id'],
                'day_of_week'  => $day,
                'sort_order'   => $order[$day],
                'area'         => $stop['area'] ?? null,
            ]);
        }
    }

    private function buildPlanVsActual(TourPlan $plan, int $userId): array
    {
        $result = [];
        for ($day = 1; $day <= 6; $day++) {
            $date    = $plan->dayDate($day);
            $planned = $plan->stops->where('day_of_week', $day)->values();
            $actual  = Visit::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->with('customer')
                ->get();

            $result[$day] = [
                'date'    => $date,
                'label'   => $date->format('D, d M'),
                'planned' => $planned,
                'actual'  => $actual,
            ];
        }
        return $result;
    }
}

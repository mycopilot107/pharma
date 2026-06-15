<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TourPlan;
use App\Models\TourPlanStop;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TourPlanController extends Controller
{
    /** GET /api/v1/tour-plans?week_start=2026-06-16 */
    public function index(Request $request)
    {
        $query = TourPlan::where('user_id', $request->user()->id)
            ->with(['stops.customer']);

        if ($ws = $request->input('week_start')) {
            $query->where('week_start', Carbon::parse($ws)->startOfWeek(Carbon::MONDAY)->toDateString());
        }

        $plans = $query->latest('week_start')->get();

        return response()->json(['data' => $plans->map(fn ($p) => $this->formatPlan($p))]);
    }

    /** POST /api/v1/tour-plans */
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
            return response()->json(['message' => 'A tour plan for this week already exists.'], 422);
        }

        $plan = TourPlan::create([
            'company_id' => $user->company_id,
            'user_id'    => $user->id,
            'week_start' => $weekStart,
            'notes'      => $validated['notes'] ?? null,
            'status'     => 'draft',
        ]);

        $this->saveStops($plan, $validated['stops'] ?? []);
        $plan->load('stops.customer');

        return response()->json($this->formatPlan($plan), 201);
    }

    /** GET /api/v1/tour-plans/{id} */
    public function show(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id, 403);

        $tourPlan->load('stops.customer');
        $withActual = true;

        return response()->json($this->formatPlan($tourPlan, $withActual));
    }

    /** POST /api/v1/tour-plans/{id}/submit */
    public function submit(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id, 403);
        abort_unless($tourPlan->isDraft(), 422);

        if ($tourPlan->stops()->count() === 0) {
            return response()->json(['message' => 'Add at least one stop before submitting.'], 422);
        }

        $tourPlan->update(['status' => 'submitted', 'submitted_at' => now()]);

        return response()->json(['message' => 'Tour plan submitted for approval.']);
    }

    /** PUT /api/v1/tour-plans/{id} */
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
        $tourPlan->load('stops.customer');

        return response()->json($this->formatPlan($tourPlan));
    }

    /** DELETE /api/v1/tour-plans/{id} */
    public function destroy(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->user_id === $request->user()->id && $tourPlan->isDraft(), 403);

        $tourPlan->delete();

        return response()->json(['message' => 'Plan deleted.']);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function saveStops(TourPlan $plan, array $stops): void
    {
        $order = [];
        foreach ($stops as $stop) {
            $day          = (int) $stop['day_of_week'];
            $order[$day]  = ($order[$day] ?? 0) + 1;
            TourPlanStop::create([
                'tour_plan_id' => $plan->id,
                'customer_id'  => $stop['customer_id'],
                'day_of_week'  => $day,
                'sort_order'   => $order[$day],
                'area'         => $stop['area'] ?? null,
            ]);
        }
    }

    private function formatPlan(TourPlan $plan, bool $withActual = false): array
    {
        $stopsFormatted = $plan->stops->map(function (TourPlanStop $stop) use ($plan, $withActual) {
            $data = [
                'id'          => $stop->id,
                'day_of_week' => $stop->day_of_week,
                'day_name'    => $stop->dayName(),
                'date'        => $plan->dayDate($stop->day_of_week)->toDateString(),
                'customer_id' => $stop->customer_id,
                'customer'    => $stop->customer ? [
                    'id'   => $stop->customer->id,
                    'name' => $stop->customer->name,
                    'type' => $stop->customer->type ?? null,
                    'area' => $stop->customer->area ?? null,
                ] : null,
                'area'       => $stop->area,
                'sort_order' => $stop->sort_order,
            ];

            if ($withActual) {
                $visitDate = $plan->dayDate($stop->day_of_week);
                $visit = Visit::where('user_id', $plan->user_id)
                    ->where('customer_id', $stop->customer_id)
                    ->whereDate('created_at', $visitDate)
                    ->latest()
                    ->first();

                $data['actual_visit'] = $visit ? [
                    'id'             => $visit->id,
                    'status'         => $visit->status instanceof \BackedEnum ? $visit->status->value : $visit->status,
                    'checked_in_at'  => $visit->checked_in_at?->toIso8601String(),
                    'checked_out_at' => $visit->checked_out_at?->toIso8601String(),
                ] : null;

                $data['is_visited'] = $visit !== null;
            }

            return $data;
        });

        return [
            'id'               => $plan->id,
            'week_start'       => $plan->week_start->toDateString(),
            'week_label'       => $plan->weekLabel(),
            'status'           => $plan->status,
            'status_label'     => $plan->statusLabel(),
            'notes'            => $plan->notes,
            'rejection_reason' => $plan->rejection_reason,
            'submitted_at'     => $plan->submitted_at?->toIso8601String(),
            'approved_at'      => $plan->approved_at?->toIso8601String(),
            'stops'            => $stopsFormatted,
            'stops_count'      => $plan->stops->count(),
        ];
    }
}

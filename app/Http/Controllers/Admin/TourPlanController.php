<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPlan;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;

class TourPlanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'submitted');
        $mrId   = $request->input('mr_id');

        $plans = TourPlan::where('company_id', $request->user()->company_id)
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->when($mrId,   fn ($q, $id) => $q->where('user_id', $id))
            ->with(['user'])
            ->withCount('stops')
            ->latest('week_start')
            ->paginate(15);

        $mrs = User::where('company_id', $request->user()->company_id)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.tour-plans.index', compact('plans', 'mrs', 'status', 'mrId'));
    }

    public function show(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->company_id === $request->user()->company_id, 403);

        $tourPlan->load([
            'user',
            'stops'    => fn ($q) => $q->with('customer')->orderBy('day_of_week')->orderBy('sort_order'),
            'reviewer',
        ]);

        $planVsActual = $this->buildPlanVsActual($tourPlan);

        return view('admin.tour-plans.show', compact('tourPlan', 'planVsActual'));
    }

    public function approve(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->company_id === $request->user()->company_id, 403);
        abort_unless($tourPlan->isSubmitted(), 422);

        $tourPlan->update([
            'status'           => 'approved',
            'approved_at'      => now(),
            'reviewed_by'      => $request->user()->id,
            'rejection_reason' => null,
        ]);

        return redirect()->route('admin.tour-plans.show', $tourPlan)
            ->with('success', "Tour plan for {$tourPlan->user->name} approved.");
    }

    public function reject(Request $request, TourPlan $tourPlan)
    {
        abort_unless($tourPlan->company_id === $request->user()->company_id, 403);
        abort_unless($tourPlan->isSubmitted(), 422);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $tourPlan->update([
            'status'           => 'rejected',
            'reviewed_by'      => $request->user()->id,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.tour-plans.show', $tourPlan)
            ->with('success', 'Tour plan rejected. MR will be notified to revise.');
    }

    private function buildPlanVsActual(TourPlan $plan): array
    {
        $result = [];
        for ($day = 1; $day <= 6; $day++) {
            $date    = $plan->dayDate($day);
            $planned = $plan->stops->where('day_of_week', $day)->values();
            $actual  = Visit::where('user_id', $plan->user_id)
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

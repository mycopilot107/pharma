<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TrackingService;
use App\Services\VisitValidationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function __construct(
        protected TrackingService $tracking,
        protected VisitValidationService $visitValidation,
    ) {}

    public function index()
    {
        $companyId = Auth::user()->company_id;

        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $live = $this->tracking->liveRepresentatives($companyId);
        $attendance = $this->tracking->todayAttendanceSummary($companyId);

        $summary = [
            'total_mrs' => $representatives->count(),
            'live_now' => $live->where('is_live', true)->count(),
            'clocked_in' => $live->filter(fn ($r) => $r['attendance']['active'] ?? false)->count(),
            'on_visit' => $live->filter(fn ($r) => $r['active_visit'] !== null)->count(),
            'visits_completed' => $live->sum(fn ($r) => $r['visits_today']['completed']),
            'visits_total' => $live->sum(fn ($r) => $r['visits_today']['total']),
        ];

        $suspiciousVisits = $this->visitValidation->suspiciousVisitsForCompany($companyId);

        return view('admin.tracking.index', [
            'representatives' => $representatives,
            'liveReps' => $live,
            'attendance' => $attendance,
            'summary' => $summary,
            'suspiciousVisits' => $suspiciousVisits,
            'googleMapsKey' => google_maps_api_key(),
        ]);
    }

    public function liveData()
    {
        $companyId = Auth::user()->company_id;

        return response()->json([
            'reps' => $this->tracking->liveRepresentatives($companyId),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function routeHistory(Request $request, User $user)
    {
        if ($user->company_id !== Auth::user()->company_id || ! $user->isRepresentative()) {
            abort(403);
        }

        $date = $request->date
            ? Carbon::parse($request->date)
            : today();

        return response()->json(
            $this->tracking->routeHistory(Auth::user()->company_id, $user->id, $date)
        );
    }

    public function fraudAlerts()
    {
        return response()->json([
            'alerts' => $this->visitValidation->suspiciousVisitsForCompany(Auth::user()->company_id)
                ->map(fn ($v) => [
                    'visit_id' => $v->visit_id,
                    'rep' => $v->visit->user?->name,
                    'place' => $v->visit->place_name,
                    'customer' => $v->visit->customer?->name,
                    'risk_score' => $v->risk_score,
                    'flags' => $v->flags,
                    'validated_at' => $v->validated_at->toIso8601String(),
                ]),
        ]);
    }
}

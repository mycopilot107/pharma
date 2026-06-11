<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TargetResource;
use App\Http\Resources\VisitResource;
use App\Models\DailyRoute;
use App\Models\Expense;
use App\Models\LocationPing;
use App\Models\MrAttendance;
use App\Models\Target;
use App\Models\Visit;
use App\Services\CustomerCrmService;
use App\Services\ReminderService;
use App\Services\TargetStatsService;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected TargetStatsService $targetStats,
        protected CustomerCrmService $crmService,
        protected ReminderService $reminders,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $this->reminders->syncForUser($user);

        $todayRoute = DailyRoute::where('user_id', $user->id)
            ->whereDate('route_date', $today)
            ->first();

        $todayVisits = Visit::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->with(['customer', 'photos'])
            ->latest()
            ->get();

        $stats = [
            'planned' => $todayVisits->where('status', VisitStatus::Planned)->count(),
            'in_progress' => $todayVisits->where('status', VisitStatus::InProgress)->count(),
            'completed' => $todayVisits->where('status', VisitStatus::Completed)->count(),
        ];

        $activeVisit = $user->activeVisit();
        if ($activeVisit) {
            $activeVisit->load(['customer', 'photos']);
        }

        $attendance = MrAttendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        // Distance today from location pings
        $todayDistanceKm = 0.0;
        $pings = LocationPing::where('user_id', $user->id)
            ->whereDate('recorded_at', $today)
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude']);
        if ($pings->count() > 1) {
            for ($i = 1; $i < $pings->count(); $i++) {
                $todayDistanceKm += $this->haversineKm(
                    $pings[$i-1]->latitude, $pings[$i-1]->longitude,
                    $pings[$i]->latitude,   $pings[$i]->longitude
                );
            }
        }

        $monthVisits = Visit::where('user_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $pendingExpenses = Expense::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            // ── Flat fields for Android app ───────────────────────────────
            'today_visits'       => $todayVisits->count(),
            'today_distance_km'  => round($todayDistanceKm, 2),
            'month_visits'       => $monthVisits,
            'pending_expenses'   => $pendingExpenses,
            'clocked_in'         => $attendance?->isActive() ?? false,
            'clock_in_time'      => $attendance?->clock_in_at?->format('H:i'),
            'pending_orders'     => 0,
            'unread_notifications' => $this->reminders->unreadCount($user->id),
            'recent_visits'      => VisitResource::collection($todayVisits->take(5)),
            // ── Rich fields for web dashboard ─────────────────────────────
            'today_route' => $todayRoute ? [
                'id' => $todayRoute->id,
                'status' => $todayRoute->status,
                'route_date' => $todayRoute->route_date?->toDateString(),
            ] : null,
            'active_visit' => $activeVisit ? new VisitResource($activeVisit) : null,
            'stats' => $stats,
            'target_stats' => $this->targetStats->forUser($user->id),
            'active_targets' => TargetResource::collection(
                Target::where('user_id', $user->id)
                    ->where('status', Target::STATUS_ACTIVE)
                    ->latest()
                    ->take(3)
                    ->get()
            ),
            'attendance' => [
                'active' => $attendance?->isActive() ?? false,
                'clock_in' => $attendance?->clock_in_at?->toIso8601String(),
                'clock_out' => $attendance?->clock_out_at?->toIso8601String(),
            ],
            'tracking_active' => (bool) $user->tracking_active,
            'crm' => [
                'pending_follow_ups' => $this->crmService->pendingFollowUpsCount($user->company_id, $user->id),
                'overdue_follow_ups' => $this->crmService->overdueFollowUpsCount($user->company_id, $user->id),
            ],
            'reminders' => NotificationResource::collection(
                $this->reminders->upcomingForUser($user->id, 5)
            ),
        ]);
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon/2) * sin($dLon/2);
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

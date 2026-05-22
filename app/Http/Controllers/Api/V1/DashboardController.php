<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TargetResource;
use App\Http\Resources\VisitResource;
use App\Models\DailyRoute;
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

        return response()->json([
            'today_route' => $todayRoute ? [
                'id' => $todayRoute->id,
                'status' => $todayRoute->status,
                'route_date' => $todayRoute->route_date?->toDateString(),
            ] : null,
            'today_visits' => VisitResource::collection($todayVisits),
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
            'unread_notifications' => $this->reminders->unreadCount($user->id),
        ]);
    }
}

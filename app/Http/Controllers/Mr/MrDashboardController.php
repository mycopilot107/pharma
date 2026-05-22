<?php

namespace App\Http\Controllers\Mr;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyRoute;
use App\Models\MrAttendance;
use App\Models\Target;
use App\Models\Visit;
use App\Services\CustomerCrmService;
use App\Services\ReminderService;
use App\Services\TargetStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MrDashboardController extends Controller
{
    public function __construct(
        protected TargetStatsService $targetStats,
        protected CustomerCrmService $crmService,
        protected ReminderService $reminders,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

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

        $activeTargets = Target::where('user_id', $user->id)
            ->where('status', Target::STATUS_ACTIVE)
            ->latest()
            ->take(3)
            ->get();

        $attendance = MrAttendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        return view('mr.dashboard', [
            'user' => $user,
            'attendance' => $attendance,
            'todayRoute' => $todayRoute,
            'todayVisits' => $todayVisits,
            'activeVisit' => $user->activeVisit(),
            'activeTargets' => $activeTargets,
            'stats' => $stats,
            'targetStats' => $this->targetStats->forUser($user->id),
            'pendingFollowUps' => $this->crmService->pendingFollowUpsCount($user->company_id, $user->id),
            'overdueFollowUps' => $this->crmService->overdueFollowUpsCount($user->company_id, $user->id),
            'reminders' => $this->reminders->upcomingForUser($user->id, 5),
            'notificationsRoute' => route('mr.notifications.index'),
        ]);
    }
}

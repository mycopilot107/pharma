<?php

namespace App\Services;

use App\Enums\CustomerType;
use App\Enums\FollowUpStatus;
use App\Enums\ReminderType;
use App\Enums\VisitStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\CustomerFollowUp;
use App\Models\CustomerPrescription;
use App\Models\Expense;
use App\Models\Target;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Visit;
use App\Enums\ExpenseStatus;

class ReminderService
{
    public function syncForUser(User $user): void
    {
        if (! $user->is_active) {
            return;
        }

        $this->syncFollowUps($user);
        $this->syncMeetings($user);
        $this->syncTargetAlerts($user);
        $this->syncDoctorRevisits($user);

        if ($user->isCompanyAdmin()) {
            $this->syncAdminAlerts($user);
        }
    }

    public function syncForCompany(int $companyId): void
    {
        User::where('company_id', $companyId)
            ->where('is_active', true)
            ->each(fn (User $user) => $this->syncForUser($user));
    }

    public function unreadCount(int $userId): int
    {
        return UserNotification::where('user_id', $userId)
            ->active()
            ->unread()
            ->due()
            ->count();
    }

    public function upcomingForUser(int $userId, int $limit = 10)
    {
        return UserNotification::where('user_id', $userId)
            ->active()
            ->due()
            ->orderByDesc('priority')
            ->orderBy('remind_at')
            ->take($limit)
            ->get();
    }

    protected function syncFollowUps(User $user): void
    {
        $followUps = CustomerFollowUp::where('user_id', $user->id)
            ->where('status', FollowUpStatus::Pending)
            ->where('due_at', '<=', now()->addDays(7))
            ->with('customer:id,name')
            ->get();

        foreach ($followUps as $followUp) {
            $overdue = $followUp->isOverdue();
            $this->upsertNotification(
                $user,
                ReminderType::FollowUp,
                'customer_follow_up',
                $followUp->id,
                $overdue ? 'high' : 'normal',
                $followUp->due_at,
                ($overdue ? 'Overdue follow-up: ' : 'Follow-up due: ').$followUp->title,
                'Customer: '.$followUp->customer->name.'. '.($followUp->notes ?? ''),
                route('mr.customers.show', $followUp->customer_id),
            );
        }

        $this->pruneStale('customer_follow_up', $followUps->pluck('id')->all(), $user->id, ReminderType::FollowUp);
    }

    protected function syncMeetings(User $user): void
    {
        $visits = Visit::where('user_id', $user->id)
            ->where('status', VisitStatus::Planned)
            ->where(function ($q) {
                $q->whereDate('planned_at', today())
                    ->orWhereBetween('planned_at', [now(), now()->addHours(24)]);
            })
            ->with('customer:id,name')
            ->get();

        foreach ($visits as $visit) {
            $when = $visit->planned_at ?? $visit->created_at;
            $this->upsertNotification(
                $user,
                ReminderType::Meeting,
                'visit',
                $visit->id,
                $when->isToday() ? 'high' : 'normal',
                $when,
                'Meeting: '.$visit->place_name,
                $visit->visit_type->label().($visit->customer ? ' · '.$visit->customer->name : '').'. Check in when you arrive.',
                route('mr.visits.show', $visit),
            );
        }

        $this->pruneStale('visit', $visits->pluck('id')->all(), $user->id, ReminderType::Meeting);
    }

    protected function syncTargetAlerts(User $user): void
    {
        $targets = Target::where('user_id', $user->id)
            ->where('status', Target::STATUS_ACTIVE)
            ->get();

        $activeIds = [];

        foreach ($targets as $target) {
            $progress = $target->progressPercent();
            $daysLeft = $target->period_end ? now()->diffInDays($target->period_end, false) : null;

            $needsAlert = ($daysLeft !== null && $daysLeft <= 7 && $progress < 80)
                || ($daysLeft !== null && $daysLeft < 0)
                || $progress < 30;

            if (! $needsAlert) {
                continue;
            }

            $activeIds[] = $target->id;
            $priority = ($daysLeft !== null && $daysLeft < 0) ? 'high' : ($progress < 50 ? 'high' : 'normal');

            $body = "Progress: {$progress}%";
            if ($target->period_end) {
                $body .= $daysLeft < 0
                    ? ' · Period ended '.$target->period_end->diffForHumans()
                    : ' · '.$daysLeft.' days left';
            }

            $this->upsertNotification(
                $user,
                ReminderType::TargetAlert,
                'target',
                $target->id,
                $priority,
                now(),
                'Target alert: '.$target->title,
                $body.' · Target: '.$target->formattedTarget().' / Achieved: '.$target->formattedAchieved(),
                route('mr.targets.show', $target),
            );
        }

        $this->pruneStale('target', $activeIds, $user->id, ReminderType::TargetAlert);
    }

    protected function syncDoctorRevisits(User $user): void
    {
        $doctors = Customer::where('company_id', $user->company_id)
            ->where('type', CustomerType::Doctor)
            ->where('is_active', true)
            ->get();

        $activeIds = [];

        foreach ($doctors as $doctor) {
            $lastVisit = Visit::where('customer_id', $doctor->id)
                ->where('user_id', $user->id)
                ->where('status', VisitStatus::Completed)
                ->latest('checked_out_at')
                ->first();

            $daysSince = $lastVisit?->checked_out_at
                ? (int) $lastVisit->checked_out_at->diffInDays(now())
                : 999;

            $hasRecentRx = CustomerPrescription::where('customer_id', $doctor->id)
                ->where('prescribed_at', '>=', now()->subDays(90))
                ->exists();

            $needsRevisit = $daysSince >= 14 && ($hasRecentRx || $lastVisit);

            if (! $needsRevisit) {
                continue;
            }

            $activeIds[] = $doctor->id;
            $priority = $daysSince >= 21 ? 'high' : 'normal';

            $this->upsertNotification(
                $user,
                ReminderType::DoctorRevisit,
                'customer',
                $doctor->id,
                $priority,
                now(),
                'Doctor revisit: '.$doctor->name,
                $lastVisit
                    ? "Last visit {$daysSince} days ago. Schedule a follow-up visit."
                    : 'No completed visit on record. Plan an introductory visit.',
                route('mr.customers.show', $doctor),
            );
        }

        $this->pruneStale('customer', $activeIds, $user->id, ReminderType::DoctorRevisit);
    }

    protected function syncAdminAlerts(User $admin): void
    {
        $companyId = $admin->company_id;

        $pendingExpenses = Expense::where('company_id', $companyId)
            ->where('status', ExpenseStatus::Pending)
            ->count();

        if ($pendingExpenses > 0) {
            $this->upsertNotification(
                $admin,
                ReminderType::FollowUp,
                'admin_pending_expenses',
                0,
                'normal',
                now(),
                "{$pendingExpenses} expense(s) awaiting approval",
                'Review and approve MR fuel, hotel and food claims.',
                route('admin.expenses.index', ['status' => 'pending']),
            );
        } else {
            UserNotification::where('user_id', $admin->id)
                ->where('reference_type', 'admin_pending_expenses')
                ->delete();
        }

        $overdueFollowUps = CustomerFollowUp::where('company_id', $companyId)
            ->where('status', FollowUpStatus::Pending)
            ->where('due_at', '<', now())
            ->count();

        if ($overdueFollowUps > 0) {
            $this->upsertNotification(
                $admin,
                ReminderType::FollowUp,
                'admin_overdue_followups',
                0,
                'high',
                now(),
                "{$overdueFollowUps} overdue team follow-up(s)",
                'MRs have overdue customer follow-ups in the field.',
                route('admin.customers.index'),
            );
        } else {
            UserNotification::where('user_id', $admin->id)
                ->where('reference_type', 'admin_overdue_followups')
                ->delete();
        }
    }

    protected function upsertNotification(
        User $user,
        ReminderType $type,
        string $referenceType,
        int $referenceId,
        string $priority,
        $remindAt,
        string $title,
        string $body,
        ?string $actionUrl,
    ): void {
        UserNotification::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ],
            [
                'company_id' => $user->company_id,
                'title' => $title,
                'body' => $body,
                'action_url' => $actionUrl,
                'remind_at' => $remindAt,
                'priority' => $priority,
            ]
        );
    }

    protected function pruneStale(string $referenceType, array $activeIds, int $userId, ReminderType $type): void
    {
        $query = UserNotification::where('user_id', $userId)
            ->where('type', $type)
            ->where('reference_type', $referenceType);

        if (count($activeIds) > 0) {
            $query->whereNotIn('reference_id', $activeIds);
        }

        $query->delete();
    }
}

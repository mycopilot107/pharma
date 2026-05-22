<?php

namespace App\Services;

use App\Enums\CustomerType;
use App\Enums\FollowUpStatus;
use App\Enums\VisitStatus;
use App\Models\Customer;
use App\Models\CustomerFollowUp;
use App\Models\CustomerPrescription;
use App\Models\CustomerPurchase;
use App\Models\Target;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AiInsightsDataService
{
    public function __construct(protected TargetStatsService $targetStats) {}

    public function companyContext(int $companyId, int $days = 7, ?int $mrUserId = null): array
    {
        $from = now()->subDays($days)->startOfDay();
        $to = now()->endOfDay();

        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->when($mrUserId, fn ($q) => $q->where('id', $mrUserId))
            ->get(['id', 'name', 'email']);

        $visitQuery = Visit::where('company_id', $companyId)
            ->whereBetween('created_at', [$from, $to])
            ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId));

        $visits = (clone $visitQuery)->with(['user:id,name', 'customer:id,name,type'])->latest()->get();

        $visitStats = [
            'total' => $visits->count(),
            'completed' => $visits->where('status', VisitStatus::Completed)->count(),
            'in_progress' => $visits->where('status', VisitStatus::InProgress)->count(),
            'planned' => $visits->where('status', VisitStatus::Planned)->count(),
            'by_type' => $visits->groupBy(fn ($v) => $v->visit_type->value)->map->count(),
            'by_mr' => $visits->groupBy('user_id')->map(fn ($group) => [
                'mr' => $group->first()->user->name,
                'count' => $group->count(),
                'completed' => $group->where('status', VisitStatus::Completed)->count(),
            ])->values(),
        ];

        $targets = Target::where('company_id', $companyId)
            ->where('status', '!=', Target::STATUS_CANCELLED)
            ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId))
            ->with('user:id,name')
            ->get()
            ->map(fn ($t) => [
                'mr' => $t->user->name,
                'title' => $t->title,
                'type' => $t->type->value,
                'target' => (float) $t->target_value,
                'achieved' => (float) $t->achieved_value,
                'progress_percent' => $t->progressPercent(),
                'status' => $t->status,
            ]);

        $followUps = CustomerFollowUp::where('company_id', $companyId)
            ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId))
            ->with(['customer:id,name,type', 'user:id,name'])
            ->where('due_at', '<=', now()->addDays(7))
            ->orderBy('due_at')
            ->get()
            ->map(fn ($f) => [
                'title' => $f->title,
                'customer' => $f->customer->name,
                'customer_type' => $f->customer->type->value,
                'mr' => $f->user->name,
                'due_at' => $f->due_at->toIso8601String(),
                'status' => $f->status->value,
                'overdue' => $f->isOverdue(),
            ]);

        $prescriptions = CustomerPrescription::where('company_id', $companyId)
            ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId))
            ->where('prescribed_at', '>=', $from)
            ->count();

        $purchases = CustomerPurchase::where('company_id', $companyId)
            ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId))
            ->where('purchased_at', '>=', $from)
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(amount), 0) as revenue')
            ->first();

        $doctorEngagement = $this->doctorEngagementAnalysis($companyId, $mrUserId);

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'days' => $days,
            ],
            'company_id' => $companyId,
            'mr_filter' => $mrUserId,
            'representatives' => $representatives->pluck('name', 'id'),
            'visit_stats' => $visitStats,
            'recent_visits' => $visits->take(15)->map(fn ($v) => $this->visitPayload($v)),
            'targets' => $targets,
            'target_summary' => $mrUserId
                ? $this->targetStats->forUser($mrUserId)
                : $this->targetStats->forCompany($companyId),
            'follow_ups' => $followUps,
            'prescriptions_logged' => $prescriptions,
            'purchase_orders' => (int) ($purchases->orders ?? 0),
            'purchase_revenue' => (float) ($purchases->revenue ?? 0),
            'doctor_engagement' => $doctorEngagement,
            'customer_counts' => $this->customerCounts($companyId),
        ];
    }

    public function visitContext(Visit $visit): array
    {
        $visit->load(['user:id,name', 'customer', 'photos', 'company:id,name']);

        $customerHistory = [];
        if ($visit->customer_id) {
            $customerHistory = [
                'prior_visits' => Visit::where('customer_id', $visit->customer_id)
                    ->where('id', '!=', $visit->id)
                    ->where('status', VisitStatus::Completed)
                    ->count(),
                'recent_prescriptions' => CustomerPrescription::where('customer_id', $visit->customer_id)
                    ->latest('prescribed_at')
                    ->take(5)
                    ->get(['product_name', 'prescribed_at', 'quantity'])
                    ->toArray(),
                'pending_follow_ups' => CustomerFollowUp::where('customer_id', $visit->customer_id)
                    ->where('status', FollowUpStatus::Pending)
                    ->count(),
            ];
        }

        return [
            'visit' => $this->visitPayload($visit),
            'customer_history' => $customerHistory,
        ];
    }

    /**
     * Rule-based insights surfaced to UI and fed to OpenAI.
     */
    public function detectInsights(array $context): array
    {
        $insights = [];

        foreach ($context['doctor_engagement']['missed_priority_by_mr'] ?? [] as $row) {
            $insights[] = "{$row['mr']} missed {$row['missed_count']} high-priority doctor visit(s) this week.";
        }

        foreach ($context['doctor_engagement']['overdue_doctor_followups'] ?? [] as $row) {
            $insights[] = "Overdue follow-up: {$row['customer']} (assigned to {$row['mr']}).";
        }

        $perf = $context['target_summary']['performance_percent'] ?? 0;
        if ($perf < 50 && ($context['target_summary']['pending'] ?? 0) > 0) {
            $insights[] = 'Team target performance is below 50% with active targets still open.';
        }

        if (($context['visit_stats']['planned'] ?? 0) > ($context['visit_stats']['completed'] ?? 0)) {
            $gap = $context['visit_stats']['planned'] - $context['visit_stats']['completed'];
            $insights[] = "{$gap} planned visit(s) were not completed in this period.";
        }

        if (($context['follow_ups'] ?? collect())->filter(fn ($f) => $f['overdue'])->count() > 3) {
            $insights[] = 'Multiple overdue follow-ups require immediate attention.';
        }

        return array_values(array_unique($insights));
    }

    protected function doctorEngagementAnalysis(int $companyId, ?int $mrUserId = null): array
    {
        $weekStart = now()->startOfWeek();
        $doctors = Customer::where('company_id', $companyId)
            ->where('type', CustomerType::Doctor)
            ->where('is_active', true)
            ->get();

        $missedByMr = [];
        $staleDoctors = [];
        $overdueFollowups = [];

        foreach ($doctors as $doctor) {
            $lastVisit = Visit::where('customer_id', $doctor->id)
                ->where('status', VisitStatus::Completed)
                ->with('user:id,name')
                ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId))
                ->latest('checked_out_at')
                ->first();

            $hasRecentRx = CustomerPrescription::where('customer_id', $doctor->id)
                ->where('prescribed_at', '>=', now()->subDays(60))
                ->exists();

            $hasOverdueFollowUp = CustomerFollowUp::where('customer_id', $doctor->id)
                ->where('status', FollowUpStatus::Pending)
                ->where('due_at', '<', now())
                ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId))
                ->exists();

            $visitedThisWeek = $lastVisit && $lastVisit->checked_out_at?->gte($weekStart);

            $isHighPriority = $hasRecentRx || $hasOverdueFollowUp;

            if ($isHighPriority && ! $visitedThisWeek) {
                $mrName = $lastVisit?->user?->name
                    ?? CustomerFollowUp::where('customer_id', $doctor->id)->latest()->first()?->user?->name
                    ?? 'Unassigned';

                $missedByMr[$mrName] = ($missedByMr[$mrName] ?? 0) + 1;
            }

            if ($lastVisit === null || $lastVisit->checked_out_at?->lt(now()->subDays(14))) {
                if ($hasRecentRx) {
                    $staleDoctors[] = [
                        'doctor' => $doctor->name,
                        'specialty' => $doctor->specialty,
                        'last_visit' => $lastVisit?->checked_out_at?->toDateString(),
                    ];
                }
            }

            if ($hasOverdueFollowUp) {
                $followUp = CustomerFollowUp::where('customer_id', $doctor->id)
                    ->where('status', FollowUpStatus::Pending)
                    ->where('due_at', '<', now())
                    ->with('user:id,name')
                    ->first();

                if ($followUp) {
                    $overdueFollowups[] = [
                        'customer' => $doctor->name,
                        'mr' => $followUp->user->name,
                        'due_at' => $followUp->due_at->toDateString(),
                    ];
                }
            }
        }

        $missedPriorityByMr = collect($missedByMr)->map(fn ($count, $mr) => [
            'mr' => $mr,
            'missed_count' => $count,
        ])->values()->all();

        return [
            'total_doctors' => $doctors->count(),
            'missed_priority_by_mr' => $missedPriorityByMr,
            'stale_high_value_doctors' => array_slice($staleDoctors, 0, 10),
            'overdue_doctor_followups' => array_slice($overdueFollowups, 0, 10),
        ];
    }

    protected function visitPayload(Visit $visit): array
    {
        return [
            'id' => $visit->id,
            'mr' => $visit->user->name,
            'place' => $visit->place_name,
            'type' => $visit->visit_type->value,
            'status' => $visit->status->value,
            'customer' => $visit->customer?->name,
            'checked_in' => $visit->checked_in_at?->toIso8601String(),
            'checked_out' => $visit->checked_out_at?->toIso8601String(),
            'duration_minutes' => $visit->duration_minutes,
            'notes' => $visit->notes,
        ];
    }

    protected function customerCounts(int $companyId): array
    {
        $counts = [];
        foreach (CustomerType::cases() as $type) {
            $counts[$type->value] = Customer::where('company_id', $companyId)
                ->where('type', $type)
                ->where('is_active', true)
                ->count();
        }

        return $counts;
    }
}

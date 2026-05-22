<?php

namespace App\Services;

use App\Models\Target;
use Illuminate\Support\Collection;

class TargetStatsService
{
    public function forCompany(int $companyId): array
    {
        $targets = Target::where('company_id', $companyId)
            ->where('status', '!=', Target::STATUS_CANCELLED)
            ->get();

        return $this->calculate($targets);
    }

    public function forUser(int $userId): array
    {
        $targets = Target::where('user_id', $userId)
            ->where('status', '!=', Target::STATUS_CANCELLED)
            ->get();

        return $this->calculate($targets);
    }

    protected function calculate(Collection $targets): array
    {
        $completed = $targets->where('status', Target::STATUS_COMPLETED)->count();
        $pending = $targets->where('status', Target::STATUS_ACTIVE)->count();

        $totalTarget = (float) $targets->sum('target_value');
        $totalAchieved = (float) $targets->sum('achieved_value');

        $performancePercent = $totalTarget > 0
            ? min(100, round(($totalAchieved / $totalTarget) * 100, 1))
            : 0;

        return [
            'completed' => $completed,
            'pending' => $pending,
            'total' => $targets->count(),
            'performance_percent' => $performancePercent,
        ];
    }
}

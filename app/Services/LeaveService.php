<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function calculateDays(
        Carbon $start,
        Carbon $end,
        bool $isHalfDay = false,
    ): float {
        if ($isHalfDay) {
            return 0.5;
        }

        return (float) ($start->diffInDays($end) + 1);
    }

    public function hasOverlap(int $userId, Carbon $start, Carbon $end, ?int $excludeId = null): bool
    {
        return LeaveRequest::where('user_id', $userId)
            ->whereIn('status', [LeaveStatus::Pending, LeaveStatus::Approved])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();
    }

    public function usedDays(int $userId, LeaveType $type, ?int $year = null): float
    {
        $year = $year ?? (int) now()->year;

        return (float) LeaveRequest::where('user_id', $userId)
            ->where('leave_type', $type)
            ->where('status', LeaveStatus::Approved)
            ->whereYear('start_date', $year)
            ->sum('days_count');
    }

    public function allowance(LeaveType $type): ?float
    {
        $allowances = config('pharma.leave_allowances', []);

        if (! $type->hasBalance()) {
            return null;
        }

        return isset($allowances[$type->value])
            ? (float) $allowances[$type->value]
            : null;
    }

    public function balanceSummary(int $userId, ?int $year = null): array
    {
        $year = $year ?? (int) now()->year;
        $summary = [];

        foreach (LeaveType::cases() as $type) {
            $allowance = $this->allowance($type);
            $used = $this->usedDays($userId, $type, $year);

            $summary[$type->value] = [
                'label' => $type->label(),
                'icon' => $type->icon(),
                'allowance' => $allowance,
                'used' => $used,
                'remaining' => $allowance !== null ? max(0, $allowance - $used) : null,
            ];
        }

        return $summary;
    }

    public function validateBalance(User $user, LeaveType $type, float $requestedDays, ?int $year = null): void
    {
        $allowance = $this->allowance($type);

        if ($allowance === null) {
            return;
        }

        $used = $this->usedDays($user->id, $type, $year);
        $pending = (float) LeaveRequest::where('user_id', $user->id)
            ->where('leave_type', $type)
            ->where('status', LeaveStatus::Pending)
            ->whereYear('start_date', $year ?? (int) now()->year)
            ->sum('days_count');

        if ($used + $pending + $requestedDays > $allowance) {
            throw ValidationException::withMessages([
                'leave_type' => sprintf(
                    'Insufficient %s balance. Allowed: %s, used: %s, pending: %s, requested: %s.',
                    $type->label(),
                    $allowance,
                    $used,
                    $pending,
                    $requestedDays
                ),
            ]);
        }
    }

    public function isOnApprovedLeave(int $userId, ?Carbon $date = null): bool
    {
        $date = $date ?? today();

        return LeaveRequest::where('user_id', $userId)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    public function onLeaveToday(int $userId): ?LeaveRequest
    {
        return LeaveRequest::where('user_id', $userId)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->first();
    }
}

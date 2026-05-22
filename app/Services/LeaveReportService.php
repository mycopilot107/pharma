<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaveReportService
{
    public function __construct(protected LeaveService $leaves) {}

    public function summary(int $companyId, array $filters = []): array
    {
        $query = $this->filtered($companyId, $filters);
        $approved = (clone $query)->where('status', LeaveStatus::Approved);

        $today = today();
        $onLeaveNow = LeaveRequest::where('company_id', $companyId)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->distinct('user_id')
            ->count('user_id');

        return [
            'total_requests' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', LeaveStatus::Pending)->count(),
            'approved' => (clone $query)->where('status', LeaveStatus::Approved)->count(),
            'rejected' => (clone $query)->where('status', LeaveStatus::Rejected)->count(),
            'cancelled' => (clone $query)->where('status', LeaveStatus::Cancelled)->count(),
            'approved_days' => (float) (clone $approved)->sum('days_count'),
            'on_leave_now' => $onLeaveNow,
        ];
    }

    public function byRepresentative(int $companyId, array $filters = []): array
    {
        return $this->filtered($companyId, $filters)
            ->select(
                'user_id',
                DB::raw('COUNT(*) as request_count'),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN days_count ELSE 0 END) as approved_days"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN days_count ELSE 0 END) as pending_days"),
            )
            ->groupBy('user_id')
            ->orderByDesc('approved_days')
            ->get()
            ->map(function ($row) use ($companyId) {
                $user = User::where('company_id', $companyId)->find($row->user_id);

                return [
                    'user_id' => $row->user_id,
                    'name' => $user?->name ?? 'Unknown',
                    'request_count' => (int) $row->request_count,
                    'approved_days' => (float) $row->approved_days,
                    'pending_days' => (float) $row->pending_days,
                ];
            })
            ->all();
    }

    public function byLeaveType(int $companyId, array $filters = []): array
    {
        $rows = $this->filtered($companyId, $filters)
            ->select(
                'leave_type',
                DB::raw('COUNT(*) as request_count'),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN days_count ELSE 0 END) as approved_days"),
            )
            ->groupBy('leave_type')
            ->orderByDesc('approved_days')
            ->get();

        return $rows->map(function ($row) {
            $type = LeaveType::tryFrom($row->leave_type);

            return [
                'type' => $row->leave_type,
                'label' => $type?->label() ?? $row->leave_type,
                'icon' => $type?->icon() ?? '📋',
                'request_count' => (int) $row->request_count,
                'approved_days' => (float) $row->approved_days,
            ];
        })->all();
    }

    public function balancesForCompany(int $companyId, int $year): array
    {
        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        return $representatives->map(function ($user) use ($year) {
            $balances = $this->leaves->balanceSummary($user->id, $year);

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'balances' => $balances,
            ];
        })->all();
    }

    protected function filtered(int $companyId, array $filters)
    {
        return LeaveRequest::where('company_id', $companyId)
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('leave_type', $t))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('end_date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('start_date', '<=', $d));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function __construct(protected LeaveService $leaves) {}

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $leaves = LeaveRequest::where('company_id', $companyId)
            ->with(['user:id,name', 'reviewer:id,name'])
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->type, fn ($q, $t) => $q->where('leave_type', $t))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('end_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('start_date', '<=', $d))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        $base = LeaveRequest::where('company_id', $companyId);
        $summary = [
            'pending' => (clone $base)->where('status', LeaveStatus::Pending)->count(),
            'approved_today' => (clone $base)->where('status', LeaveStatus::Approved)
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count(),
            'on_leave_now' => (clone $base)->where('status', LeaveStatus::Approved)
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->distinct('user_id')
                ->count('user_id'),
        ];

        return view('admin.leaves.index', [
            'leaves' => $leaves,
            'representatives' => $representatives,
            'summary' => $summary,
            'leaveTypes' => LeaveType::cases(),
        ]);
    }

    public function show(LeaveRequest $leave)
    {
        $this->authorizeLeave($leave);
        $leave->load(['user', 'reviewer']);

        return view('admin.leaves.show', [
            'leave' => $leave,
            'balance' => $this->leaves->balanceSummary($leave->user_id),
        ]);
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        $this->authorizeLeave($leave);

        if (! $leave->isPending()) {
            return back()->with('error', 'This request was already reviewed.');
        }

        try {
            $this->leaves->validateBalance(
                $leave->user,
                $leave->leave_type,
                (float) $leave->days_count,
                (int) $leave->start_date->year,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $validated = $request->validate([
            'manager_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $leave->update([
            'status' => LeaveStatus::Approved,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'] ?? null,
        ]);

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $this->authorizeLeave($leave);

        if (! $leave->isPending()) {
            return back()->with('error', 'This request was already reviewed.');
        }

        $validated = $request->validate([
            'manager_notes' => ['required', 'string', 'max:1000'],
        ]);

        $leave->update([
            'status' => LeaveStatus::Rejected,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'],
        ]);

        return back()->with('success', 'Leave rejected.');
    }

    protected function authorizeLeave(LeaveRequest $leave): void
    {
        if ($leave->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}

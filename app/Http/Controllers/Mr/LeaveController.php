<?php

namespace App\Http\Controllers\Mr;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function __construct(protected LeaveService $leaves) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        $leaves = LeaveRequest::where('user_id', $user->id)
            ->when($request->type, fn ($q, $t) => $q->where('leave_type', $t))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('start_date')
            ->paginate(15)
            ->withQueryString();

        return view('mr.leaves.index', [
            'leaves' => $leaves,
            'balance' => $this->leaves->balanceSummary($user->id),
            'leaveTypes' => LeaveType::cases(),
            'onLeaveToday' => $this->leaves->onLeaveToday($user->id),
        ]);
    }

    public function create()
    {
        return view('mr.leaves.create', [
            'leaveTypes' => LeaveType::cases(),
            'balance' => $this->leaves->balanceSummary(Auth::id()),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'leave_type' => ['required', Rule::enum(LeaveType::class)],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day' => ['boolean'],
            'half_day_period' => ['nullable', 'required_if:is_half_day,1', 'in:first_half,second_half'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $isHalfDay = $request->boolean('is_half_day');

        if ($isHalfDay && ! $start->equalTo($end)) {
            return back()->withInput()->withErrors([
                'is_half_day' => 'Half-day leave must be for a single date.',
            ]);
        }

        if ($this->leaves->hasOverlap($user->id, $start, $end)) {
            return back()->withInput()->withErrors([
                'start_date' => 'You already have leave pending or approved for these dates.',
            ]);
        }

        $days = $this->leaves->calculateDays($start, $end, $isHalfDay);
        $type = LeaveType::from($validated['leave_type']);

        $this->leaves->validateBalance($user, $type, $days);

        LeaveRequest::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'leave_type' => $type,
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => $days,
            'is_half_day' => $isHalfDay,
            'half_day_period' => $isHalfDay ? $validated['half_day_period'] : null,
            'reason' => $validated['reason'],
            'status' => LeaveStatus::Pending,
        ]);

        return redirect()->route('mr.leaves.index')
            ->with('success', 'Leave request submitted for approval.');
    }

    public function show(LeaveRequest $leave)
    {
        $this->authorizeLeave($leave);

        return view('mr.leaves.show', compact('leave'));
    }

    public function cancel(LeaveRequest $leave)
    {
        $this->authorizeLeave($leave);

        if (! $leave->isPending()) {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $leave->update(['status' => LeaveStatus::Cancelled]);

        return redirect()->route('mr.leaves.index')
            ->with('success', 'Leave request cancelled.');
    }

    protected function authorizeLeave(LeaveRequest $leave): void
    {
        if ($leave->user_id !== Auth::id()) {
            abort(403);
        }
    }
}

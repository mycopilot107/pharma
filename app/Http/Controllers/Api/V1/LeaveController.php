<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function __construct(protected LeaveService $leaves) {}

    public function index(Request $request)
    {
        $leaves = LeaveRequest::where('user_id', $request->user()->id)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('start_date')
            ->paginate(15);

        return LeaveRequestResource::collection($leaves)->additional([
            'balance' => $this->leaves->balanceSummary($request->user()->id),
            'on_leave_today' => $this->leaves->onLeaveToday($request->user()->id) !== null,
        ]);
    }

    public function balance(Request $request)
    {
        return response()->json([
            'balance' => $this->leaves->balanceSummary($request->user()->id),
            'on_leave_today' => $this->leaves->onLeaveToday($request->user()->id) !== null,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'leave_type' => ['required', Rule::enum(LeaveType::class)],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day' => ['boolean'],
            'half_day_period' => ['nullable', 'required_if:is_half_day,true', 'in:first_half,second_half'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $isHalfDay = $request->boolean('is_half_day');

        if ($isHalfDay && ! $start->equalTo($end)) {
            return response()->json(['message' => 'Half-day leave must be for a single date.'], 422);
        }

        if ($this->leaves->hasOverlap($user->id, $start, $end)) {
            return response()->json(['message' => 'Overlapping leave exists for these dates.'], 422);
        }

        $days = $this->leaves->calculateDays($start, $end, $isHalfDay);
        $type = LeaveType::from($validated['leave_type']);

        $this->leaves->validateBalance($user, $type, $days);

        $leave = LeaveRequest::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'leave_type' => $type,
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => $days,
            'is_half_day' => $isHalfDay,
            'half_day_period' => $isHalfDay ? ($validated['half_day_period'] ?? null) : null,
            'reason' => $validated['reason'],
            'status' => LeaveStatus::Pending,
        ]);

        return (new LeaveRequestResource($leave))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            abort(403);
        }

        return new LeaveRequestResource($leave);
    }

    public function cancel(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $leave->isPending()) {
            return response()->json(['message' => 'Only pending requests can be cancelled.'], 422);
        }

        $leave->update(['status' => LeaveStatus::Cancelled]);

        return new LeaveRequestResource($leave->fresh());
    }
}

@extends('layouts.mr')

@section('title', 'My Leave')

@section('mr-content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold">Leave</h1>
        <p class="text-sm text-slate-500">Apply & track leave requests</p>
    </div>
    <a href="{{ route('mr.leaves.create') }}" class="rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white">+ Apply</a>
</div>

@if ($onLeaveToday)
<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
    You are on approved leave today ({{ $onLeaveToday->leave_type->label() }}).
</div>
@endif

<div class="mt-4 grid grid-cols-2 gap-2 text-xs">
    @foreach (['casual', 'sick', 'annual'] as $key)
        @if (isset($balance[$key]) && $balance[$key]['allowance'] !== null)
            <div class="rounded-xl border bg-white p-3">
                <p class="text-slate-500">{{ $balance[$key]['icon'] }} {{ $balance[$key]['label'] }}</p>
                <p class="mt-1 font-bold text-slate-800">
                    {{ $balance[$key]['remaining'] }} / {{ $balance[$key]['allowance'] }} left
                </p>
            </div>
        @endif
    @endforeach
</div>

<form method="GET" class="mt-4 flex gap-2">
    <select name="type" class="flex-1 rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach ($leaveTypes as $type)
            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->icon() }} {{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\LeaveStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-slate-100 px-3 py-2 text-sm">Filter</button>
</form>

<div class="mt-4 space-y-2">
    @forelse ($leaves as $leave)
        <a href="{{ route('mr.leaves.show', $leave) }}" class="block rounded-xl border bg-white p-3 hover:border-teal-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-medium">{{ $leave->leave_type->icon() }} {{ $leave->leave_type->label() }}</p>
                    <p class="text-sm text-slate-600">{{ $leave->dateRangeLabel() }} · {{ $leave->days_count }} day(s)</p>
                </div>
                <span class="rounded-full px-2 py-0.5 text-xs {{ $leave->status->color() }}">{{ $leave->status->label() }}</span>
            </div>
        </a>
    @empty
        <p class="py-8 text-center text-sm text-slate-500">No leave requests yet.</p>
    @endforelse
</div>

{{ $leaves->links() }}
@endsection

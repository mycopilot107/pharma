@extends('layouts.mr')

@section('title', 'Leave Request')

@section('mr-content')
<a href="{{ route('mr.leaves.index') }}" class="text-sm text-teal-700">← Back</a>

<div class="mt-4 rounded-2xl border bg-white p-4">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-bold">{{ $leave->leave_type->icon() }} {{ $leave->leave_type->label() }}</h1>
            <p class="text-sm text-slate-500">{{ $leave->dateRangeLabel() }}</p>
        </div>
        <span class="rounded-full px-2 py-1 text-xs {{ $leave->status->color() }}">{{ $leave->status->label() }}</span>
    </div>

    <dl class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between"><dt class="text-slate-500">Days</dt><dd class="font-medium">{{ $leave->days_count }}</dd></div>
        @if ($leave->is_half_day)
            <div class="flex justify-between"><dt class="text-slate-500">Half day</dt><dd>{{ ucfirst(str_replace('_', ' ', $leave->half_day_period ?? '')) }}</dd></div>
        @endif
        <div><dt class="text-slate-500">Reason</dt><dd class="mt-1 text-slate-800">{{ $leave->reason }}</dd></div>
        @if ($leave->manager_notes)
            <div class="rounded-lg bg-slate-50 p-3">
                <dt class="text-slate-500">Manager notes</dt>
                <dd class="mt-1">{{ $leave->manager_notes }}</dd>
            </div>
        @endif
        @if ($leave->reviewed_at)
            <div class="flex justify-between text-xs text-slate-500">
                <span>Reviewed</span>
                <span>{{ $leave->reviewed_at->format('d M Y h:i A') }}</span>
            </div>
        @endif
    </dl>

    @if ($leave->isPending())
        <form method="POST" action="{{ route('mr.leaves.cancel', $leave) }}" class="mt-6" onsubmit="return confirm('Cancel this leave request?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full rounded-lg border border-red-200 py-2 text-sm text-red-700 hover:bg-red-50">
                Cancel request
            </button>
        </form>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('title', 'Review Leave')

@section('content')
<a href="{{ route('admin.leaves.index') }}" class="text-sm text-teal-700 hover:underline">← All leave requests</a>

<div class="mt-4 flex flex-col gap-6 lg:flex-row lg:items-start">
    <div class="flex-1 rounded-2xl border bg-white p-6 shadow-sm">
        <span class="rounded-full px-2 py-0.5 text-xs {{ $leave->status->color() }}">{{ $leave->status->label() }}</span>
        <h1 class="mt-2 text-2xl font-bold">{{ $leave->leave_type->icon() }} {{ $leave->leave_type->label() }}</h1>
        <p class="text-lg text-slate-700">{{ $leave->dateRangeLabel() }} · {{ $leave->days_count }} day(s)</p>
        <p class="mt-2 text-sm text-slate-500">
            Requested by <strong>{{ $leave->user->name }}</strong> · {{ $leave->created_at->format('d M Y, h:i A') }}
        </p>
        @if ($leave->is_half_day)
            <p class="mt-1 text-sm text-slate-600">Half day: {{ ucfirst(str_replace('_', ' ', $leave->half_day_period ?? '')) }}</p>
        @endif
        <p class="mt-4 text-slate-700">{{ $leave->reason }}</p>

        <h2 class="mt-6 text-sm font-semibold text-slate-800">Leave balance ({{ now()->year }})</h2>
        <div class="mt-2 grid gap-2 sm:grid-cols-3">
            @foreach (['casual', 'sick', 'annual'] as $key)
                @if (isset($balance[$key]) && $balance[$key]['allowance'] !== null)
                    <div class="rounded-lg bg-slate-50 p-3 text-sm">
                        <p class="text-slate-500">{{ $balance[$key]['label'] }}</p>
                        <p class="font-semibold">{{ $balance[$key]['used'] }} used · {{ $balance[$key]['remaining'] }} left</p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @if ($leave->isPending())
    <div class="w-full lg:w-80 space-y-3 rounded-2xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800">Decision</h2>
        <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}">
            @csrf
            <textarea name="manager_notes" rows="2" placeholder="Optional note" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
            <button type="submit" class="mt-2 w-full rounded-lg bg-emerald-600 py-2.5 font-medium text-white hover:bg-emerald-700">Approve</button>
        </form>
        <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}" class="border-t pt-3">
            @csrf
            <textarea name="manager_notes" rows="2" placeholder="Reason for rejection (required)" required class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
            @error('manager_notes')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="mt-2 w-full rounded-lg border border-red-300 bg-white py-2.5 font-medium text-red-700 hover:bg-red-50">Reject</button>
        </form>
    </div>
    @elseif ($leave->manager_notes)
    <div class="w-full lg:w-80 rounded-2xl border bg-slate-50 p-5">
        <p class="text-xs font-medium text-slate-600">Manager notes</p>
        <p class="mt-1 text-sm">{{ $leave->manager_notes }}</p>
        <p class="mt-2 text-xs text-slate-500">By {{ $leave->reviewer?->name }} · {{ $leave->reviewed_at?->format('d M Y, h:i A') }}</p>
    </div>
    @endif
</div>
@endsection

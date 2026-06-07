@extends('layouts.app')

@section('title', 'Leave Management')

@section('content')

{{-- Header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <nav class="flex items-center gap-1 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-teal-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-600">Leave</span>
        </nav>
        <h1 class="mt-1 text-xl font-bold text-slate-900">Leave Management</h1>
        <p class="mt-0.5 text-sm text-slate-500">Approve or reject field team leave requests</p>
    </div>
    <a href="{{ route('admin.leaves.reports') }}"
        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Leave Reports
    </a>
</div>

{{-- Stats --}}
<div class="mt-6 grid gap-4 grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Pending approval</p>
        <p class="mt-1.5 text-3xl font-bold text-amber-500">{{ $summary['pending'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">On leave today</p>
        <p class="mt-1.5 text-3xl font-bold text-rose-500">{{ $summary['on_leave_now'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Approved today</p>
        <p class="mt-1.5 text-3xl font-bold text-emerald-600">{{ $summary['approved_today'] }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">MR</label>
            <select name="user_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All reps</option>
                @foreach ($representatives as $rep)
                    <option value="{{ $rep->id }}" @selected(request('user_id') == $rep->id)>{{ $rep->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Type</label>
            <select name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All types</option>
                @foreach ($leaveTypes as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All statuses</option>
                @foreach (\App\Enums\LeaveStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        </div>
        <button type="submit"
            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700">
            Apply
        </button>
        @if (request()->hasAny(['user_id','type','status','date_from','date_to']))
            <a href="{{ route('admin.leaves.index') }}"
                class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                Clear
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead>
            <tr class="bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Employee</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Dates</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Days</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($leaves as $leave)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-5 py-3.5 font-medium text-slate-900">{{ $leave->user->name }}</td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $leave->leave_type->icon() }} {{ $leave->leave_type->label() }}</td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $leave->dateRangeLabel() }}</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $leave->days_count }}</td>
                    <td class="px-5 py-3.5">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $leave->status->color() }}">
                            {{ $leave->status->label() }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.leaves.show', $leave) }}"
                            class="text-xs font-medium text-teal-600 hover:underline">Review</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">No leave requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $leaves->links() }}</div>

@endsection

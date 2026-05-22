@extends('layouts.app')

@section('title', 'Leave Management')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Leave management</h1>
        <p class="text-slate-600">Approve or reject field team leave requests</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.leaves.reports') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">Leave reports</a>
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Dashboard</a>
    </div>
</div>

<div class="mt-6 grid gap-4 grid-cols-3">
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">Pending approval</p>
        <p class="text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">On leave today</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $summary['on_leave_now'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">Approved today</p>
        <p class="text-2xl font-bold text-teal-600">{{ $summary['approved_today'] }}</p>
    </div>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <select name="user_id" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All reps</option>
        @foreach ($representatives as $rep)
            <option value="{{ $rep->id }}" @selected(request('user_id') == $rep->id)>{{ $rep->name }}</option>
        @endforeach
    </select>
    <select name="type" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach ($leaveTypes as $type)
            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\LeaveStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border px-3 py-2 text-sm">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border px-3 py-2 text-sm">
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-xl border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Employee</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Type</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Dates</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Days</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($leaves as $leave)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium">{{ $leave->user->name }}</td>
                    <td class="px-4 py-3">{{ $leave->leave_type->icon() }} {{ $leave->leave_type->label() }}</td>
                    <td class="px-4 py-3">{{ $leave->dateRangeLabel() }}</td>
                    <td class="px-4 py-3">{{ $leave->days_count }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $leave->status->color() }}">{{ $leave->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.leaves.show', $leave) }}" class="text-teal-700 hover:underline">Review</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No leave requests found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $leaves->links() }}</div>
@endsection

@extends('layouts.app')

@section('title', 'Tour Plans')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Tour Plans</h1>
        <p class="mt-1 text-sm text-slate-500">Review and approve MR weekly tour plans.</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
        <select name="status"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            @foreach(['submitted' => 'Pending Approval', 'approved' => 'Approved', 'rejected' => 'Rejected', 'draft' => 'Draft', '' => 'All'] as $val => $lbl)
                <option value="{{ $val }}" @selected(request('status', 'submitted') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">MR</label>
        <select name="mr_id"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            <option value="">All MRs</option>
            @foreach ($mrs as $mr)
                <option value="{{ $mr->id }}" @selected(request('mr_id') == $mr->id)>{{ $mr->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
        Filter
    </button>
</form>

@if (session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">MR</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Week</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Stops</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted</th>
                <th class="px-5 py-3.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($plans as $plan)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-4 font-medium text-slate-900">{{ $plan->user->name }}</td>
                    <td class="px-5 py-4 text-slate-600">{{ $plan->weekLabel() }}</td>
                    <td class="px-5 py-4 text-slate-600">{{ $plan->stops_count }}</td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $plan->statusBadgeClass() }}">
                            {{ $plan->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-slate-500">
                        {{ $plan->submitted_at ? $plan->submitted_at->format('d M, h:i A') : '—' }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('admin.tour-plans.show', $plan) }}"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                        No tour plans found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $plans->appends(request()->query())->links() }}</div>
@endsection

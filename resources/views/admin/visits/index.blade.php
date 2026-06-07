@extends('layouts.app')

@section('title', 'Visit Tracking')

@section('content')

{{-- Header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <nav class="flex items-center gap-1 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-teal-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-600">Visits</span>
        </nav>
        <h1 class="mt-1 text-xl font-bold text-slate-900">MR Visit Tracking</h1>
        <p class="mt-0.5 text-sm text-slate-500">Monitor field visits across your team</p>
    </div>
</div>

{{-- Stats --}}
<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Visits today</p>
        <p class="mt-1.5 text-3xl font-bold text-slate-900">{{ $summary['today'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Completed today</p>
        <p class="mt-1.5 text-3xl font-bold text-emerald-600">{{ $summary['completed_today'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">In progress now</p>
        <p class="mt-1.5 text-3xl font-bold text-amber-500">{{ $summary['in_progress'] }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">MR</label>
            <select name="user_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All MRs</option>
                @foreach ($representatives as $rep)
                    <option value="{{ $rep->id }}" @selected(request('user_id') == $rep->id)>{{ $rep->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Type</label>
            <select name="visit_type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All types</option>
                @foreach (\App\Enums\VisitType::cases() as $t)
                    <option value="{{ $t->value }}" @selected(request('visit_type') === $t->value)>{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All statuses</option>
                @foreach (\App\Enums\VisitStatus::cases() as $s)
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
        @if (request()->hasAny(['user_id','visit_type','status','date_from','date_to']))
            <a href="{{ route('admin.visits.index') }}"
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
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">MR</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Place</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Time</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">GPS</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($visits as $visit)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-5 py-3.5 font-medium text-slate-900">{{ $visit->user->name }}</td>
                    <td class="px-5 py-3.5 font-medium text-slate-800">{{ $visit->place_name }}</td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                        {{ ($visit->planned_at ?? $visit->created_at)->format('d M Y') }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $visit->visit_type->label() }}</td>
                    <td class="px-5 py-3.5">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $visit->status->color() }}">
                            {{ $visit->status->label() }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                        @if ($visit->checked_in_at)
                            {{ $visit->checked_in_at->format('h:i A') }}
                            @if ($visit->checked_out_at) – {{ $visit->checked_out_at->format('h:i A') }}@endif
                            @if ($visit->formattedDuration())
                                <span class="ml-1 text-xs text-slate-400">({{ $visit->formattedDuration() }})</span>
                            @endif
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if ($visit->check_in_latitude)
                            <span class="text-xs font-medium text-emerald-600">✓ GPS</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.visits.show', $visit) }}"
                            class="text-xs font-medium text-teal-600 hover:underline">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-400">No visits recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $visits->links() }}</div>

@endsection

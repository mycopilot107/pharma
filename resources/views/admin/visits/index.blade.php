@extends('layouts.app')

@section('title', 'Visit Tracking')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">MR visit tracking</h1>
        <p class="text-slate-600">Monitor field visits across your team</p>
    </div>
    <a href="{{ route('dashboard') }}" class="text-sm text-teal-700 hover:underline">← Team dashboard</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl bg-white border p-4 shadow-sm">
        <p class="text-sm text-slate-500">Visits today</p>
        <p class="text-2xl font-bold">{{ $summary['today'] }}</p>
    </div>
    <div class="rounded-xl bg-white border p-4 shadow-sm">
        <p class="text-sm text-slate-500">Completed today</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $summary['completed_today'] }}</p>
    </div>
    <div class="rounded-xl bg-white border p-4 shadow-sm">
        <p class="text-sm text-slate-500">In progress now</p>
        <p class="text-2xl font-bold text-amber-600">{{ $summary['in_progress'] }}</p>
    </div>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <select name="user_id" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All MRs</option>
        @foreach ($representatives as $rep)
            <option value="{{ $rep->id }}" @selected(request('user_id') == $rep->id)>{{ $rep->name }}</option>
        @endforeach
    </select>
    <select name="visit_type" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach (\App\Enums\VisitType::cases() as $t)
            <option value="{{ $t->value }}" @selected(request('visit_type') === $t->value)>{{ $t->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\VisitStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border px-3 py-2 text-sm">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border px-3 py-2 text-sm">
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-2xl border bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left">MR</th>
                <th class="px-4 py-3 text-left">Visit</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Time</th>
                <th class="px-4 py-3 text-left">GPS</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($visits as $visit)
                <tr>
                    <td class="px-4 py-3">{{ $visit->user->name }}</td>
                    <td class="px-4 py-3 font-medium">{{ $visit->place_name }}</td>
                    <td class="px-4 py-3">{{ $visit->visit_type->label() }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs {{ $visit->status->color() }}">{{ $visit->status->label() }}</span></td>
                    <td class="px-4 py-3 text-slate-500">
                        @if ($visit->checked_in_at)
                            {{ $visit->checked_in_at->format('h:i') }}
                            @if ($visit->checked_out_at) – {{ $visit->checked_out_at->format('h:i') }}@endif
                            @if ($visit->formattedDuration()) ({{ $visit->formattedDuration() }})@endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-teal-600">
                        @if ($visit->check_in_latitude) ✓ Check-in @endif
                    </td>
                    <td class="px-4 py-3"><a href="{{ route('admin.visits.show', $visit) }}" class="text-teal-700 hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No visits recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $visits->links() }}
@endsection

@extends('layouts.mr')

@section('title', 'MR Dashboard')

@section('mr-content')
<div>
    <h1 class="text-xl font-bold text-slate-900">Hello, {{ $user->name }}</h1>
    <p class="text-sm text-slate-500">{{ now()->format('l, d M Y') }}</p>
</div>

<div class="mt-6 rounded-2xl border-2 {{ $user->tracking_active ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-white' }} p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold {{ $user->tracking_active ? 'text-emerald-800' : 'text-slate-800' }}">
                @if ($user->tracking_active)
                    🟢 Live tracking active
                @else
                    Attendance
                @endif
            </p>
            @if ($attendance?->isActive())
                <p class="text-xs text-emerald-700 mt-1">Clocked in {{ $attendance->clock_in_at->format('h:i A') }}</p>
            @elseif ($attendance?->clock_out_at)
                <p class="text-xs text-slate-500 mt-1">Day ended {{ $attendance->clock_out_at->format('h:i A') }}</p>
            @else
                <p class="text-xs text-slate-500 mt-1">Clock in to start live GPS for your manager</p>
            @endif
        </div>
    </div>
    @if (! $attendance?->isActive() && ! $attendance?->clock_out_at)
        <form method="POST" action="{{ route('mr.attendance.clock-in') }}" id="clock-in-form" class="mt-3">
            @csrf
            <input type="hidden" name="latitude" id="clock-in-lat">
            <input type="hidden" name="longitude" id="clock-in-lng">
            <button type="button" onclick="submitWithGps('clock-in-form','clock-in-lat','clock-in-lng')" class="w-full rounded-xl bg-emerald-600 py-3 font-semibold text-white">
                Clock in (GPS)
            </button>
        </form>
    @elseif ($attendance?->isActive())
        <form method="POST" action="{{ route('mr.attendance.clock-out') }}" id="clock-out-form" class="mt-3">
            @csrf
            <input type="hidden" name="latitude" id="clock-out-lat">
            <input type="hidden" name="longitude" id="clock-out-lng">
            <button type="button" onclick="submitWithGps('clock-out-form','clock-out-lat','clock-out-lng')" class="w-full rounded-xl border border-red-300 bg-white py-3 font-medium text-red-700">
                Clock out
            </button>
        </form>
    @endif
</div>

@if ($activeVisit)
    <div class="mt-6 rounded-2xl border-2 border-amber-300 bg-amber-50 p-4">
        <p class="text-sm font-medium text-amber-800">Active visit in progress</p>
        <p class="mt-1 font-semibold">{{ $activeVisit->visit_type->icon() }} {{ $activeVisit->place_name }}</p>
        <p class="text-xs text-amber-700">Checked in {{ $activeVisit->checked_in_at?->diffForHumans() }}</p>
        <a href="{{ route('mr.visits.show', $activeVisit) }}" class="mt-3 inline-block rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Continue visit</a>
    </div>
@endif

<div class="mt-6">
    <a href="{{ route('mr.ai-reports.index') }}" class="flex items-center justify-between rounded-2xl border border-violet-200 bg-violet-50 p-4 hover:border-violet-400">
        <div>
            <p class="font-semibold text-violet-900">AI reporting</p>
            <p class="text-sm text-violet-700">Daily report, performance & recommendations</p>
        </div>
        <span class="text-violet-600">→</span>
    </a>
</div>

@include('partials.notifications-widget', ['reminders' => $reminders ?? collect(), 'notificationsRoute' => $notificationsRoute ?? route('mr.notifications.index')])

@if ($pendingFollowUps > 0)
<div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
    <div class="flex items-center justify-between">
        <p class="font-semibold text-amber-900">Follow-ups</p>
        <a href="{{ route('mr.customers.index') }}" class="text-sm text-amber-700">View customers →</a>
    </div>
    <p class="mt-1 text-sm text-amber-800">
        {{ $pendingFollowUps }} pending
        @if ($overdueFollowUps > 0)
            <span class="text-red-700">({{ $overdueFollowUps }} overdue)</span>
        @endif
    </p>
</div>
@endif

<section class="mt-6">
    <h2 class="text-sm font-semibold text-slate-700 mb-3">Target performance</h2>
    @include('partials.target-stats-cards', ['targetStats' => $targetStats])
</section>

<div class="mt-6 grid grid-cols-3 gap-3 text-center">
    <div class="rounded-xl bg-blue-50 p-3">
        <p class="text-2xl font-bold text-blue-700">{{ $stats['planned'] }}</p>
        <p class="text-xs text-blue-600">Planned</p>
    </div>
    <div class="rounded-xl bg-amber-50 p-3">
        <p class="text-2xl font-bold text-amber-700">{{ $stats['in_progress'] }}</p>
        <p class="text-xs text-amber-600">Active</p>
    </div>
    <div class="rounded-xl bg-emerald-50 p-3">
        <p class="text-2xl font-bold text-emerald-700">{{ $stats['completed'] }}</p>
        <p class="text-xs text-emerald-600">Done</p>
    </div>
</div>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-slate-800">Today's route</h2>
        @unless ($todayRoute)
            <form method="POST" action="{{ route('mr.routes.store') }}" id="route-form">
                @csrf
                <input type="hidden" name="latitude" id="route-lat">
                <input type="hidden" name="longitude" id="route-lng">
                <button type="button" onclick="submitWithGps('route-form','route-lat','route-lng')" class="text-sm font-medium text-teal-700">+ Start route</button>
            </form>
        @endunless
    </div>
    @if ($todayRoute)
        <p class="mt-2 text-sm text-slate-600">{{ $todayRoute->title }}</p>
        <p class="text-xs text-slate-500">{{ $todayRoute->visits()->count() }} visits · {{ $todayRoute->completedVisitsCount() }} completed</p>
    @else
        <p class="mt-2 text-sm text-slate-500">No route yet. Start your daily route to track visits.</p>
    @endif
</div>

@if ($activeTargets->isNotEmpty())
<div class="mt-6 rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-indigo-900">My targets</h2>
        <a href="{{ route('mr.targets.index') }}" class="text-sm text-indigo-700">View all</a>
    </div>
    @foreach ($activeTargets as $target)
        <a href="{{ route('mr.targets.show', $target) }}" class="mt-3 block rounded-xl bg-white p-3">
            <p class="text-sm font-medium">{{ $target->type->icon() }} {{ $target->title }}</p>
            <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-indigo-500" style="width:{{ $target->progressPercent() }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $target->progressPercent() }}% · {{ $target->formattedAchieved() }} / {{ $target->formattedTarget() }}</p>
        </a>
    @endforeach
</div>
@endif

<div class="mt-6">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-slate-800">Today's visits</h2>
        @unless ($activeVisit)
            <a href="{{ route('mr.visits.create') }}" class="rounded-lg bg-teal-600 px-3 py-1.5 text-sm font-medium text-white">+ Visit</a>
        @endunless
    </div>
    @forelse ($todayVisits as $visit)
        <a href="{{ route('mr.visits.show', $visit) }}" class="mt-3 flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 hover:border-teal-300">
            <span class="text-2xl">{{ $visit->visit_type->icon() }}</span>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-slate-900 truncate">{{ $visit->place_name }}</p>
                <p class="text-xs text-slate-500">{{ $visit->visit_type->label() }} · {{ $visit->status->label() }}</p>
            </div>
            @if ($visit->duration_minutes)
                <span class="text-xs text-slate-400">{{ $visit->formattedDuration() }}</span>
            @endif
        </a>
    @empty
        <p class="mt-4 text-center text-sm text-slate-500">No visits today yet.</p>
    @endforelse
</div>
@endsection

@push('head')
@include('partials.gps-script')
@endpush

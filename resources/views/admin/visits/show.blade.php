@extends('layouts.app')

@section('title', 'Visit Details')

@section('content')
<a href="{{ route('admin.visits.index') }}" class="text-sm text-teal-700 hover:underline">← All visits</a>

<div class="mt-4 rounded-2xl border bg-white p-6 shadow-sm">
    <div class="flex items-start gap-4">
        <span class="text-4xl">{{ $visit->visit_type->icon() }}</span>
        <div>
            <h1 class="text-2xl font-bold">{{ $visit->place_name }}</h1>
            <p class="text-slate-600">{{ $visit->visit_type->label() }} · {{ $visit->user->name }}</p>
            <span class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-medium {{ $visit->status->color() }}">{{ $visit->status->label() }}</span>
        </div>
    </div>

    @if ($visit->address)
        <p class="mt-4 text-slate-600">{{ $visit->address }}</p>
    @endif

    <dl class="mt-6 grid gap-4 sm:grid-cols-2 text-sm">
        <div>
            <dt class="text-slate-500">Check-in</dt>
            <dd class="font-medium">{{ $visit->checked_in_at?->format('d M Y, h:i A') ?? '—' }}</dd>
            @if ($visit->check_in_latitude)
                <dd class="text-teal-600 text-xs mt-1">GPS: {{ $visit->check_in_latitude }}, {{ $visit->check_in_longitude }}</dd>
                <dd class="mt-1"><a href="https://www.google.com/maps?q={{ $visit->check_in_latitude }},{{ $visit->check_in_longitude }}" target="_blank" class="text-teal-700 underline">View on map</a></dd>
            @endif
        </div>
        <div>
            <dt class="text-slate-500">Check-out</dt>
            <dd class="font-medium">{{ $visit->checked_out_at?->format('d M Y, h:i A') ?? '—' }}</dd>
            @if ($visit->check_out_latitude)
                <dd class="text-teal-600 text-xs mt-1">GPS: {{ $visit->check_out_latitude }}, {{ $visit->check_out_longitude }}</dd>
                <dd class="mt-1"><a href="https://www.google.com/maps?q={{ $visit->check_out_latitude }},{{ $visit->check_out_longitude }}" target="_blank" class="text-teal-700 underline">View on map</a></dd>
            @endif
        </div>
        <div>
            <dt class="text-slate-500">Duration</dt>
            <dd class="font-medium">{{ $visit->formattedDuration() ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Daily route</dt>
            <dd class="font-medium">{{ $visit->dailyRoute?->title ?? '—' }}</dd>
        </div>
    </dl>

    @if ($visit->notes)
        <div class="mt-6">
            <h2 class="font-semibold">Notes</h2>
            <p class="mt-2 text-sm text-slate-600 whitespace-pre-wrap">{{ $visit->notes }}</p>
        </div>
    @endif

    @if ($visit->photos->isNotEmpty())
        <div class="mt-6">
            <h2 class="font-semibold">Photos ({{ $visit->photos->count() }})</h2>
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($visit->photos as $photo)
                    <a href="{{ $photo->url() }}" target="_blank">
                        <img src="{{ $photo->url() }}" alt="" class="rounded-lg border aspect-square object-cover w-full">
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

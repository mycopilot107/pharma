@extends('layouts.mr')

@section('title', $visit->place_name)

@section('mr-content')
<div class="flex items-start gap-3">
    <span class="text-3xl">{{ $visit->visit_type->icon() }}</span>
    <div>
        <h1 class="text-xl font-bold">{{ $visit->place_name }}</h1>
        <p class="text-sm text-slate-500">{{ $visit->visit_type->label() }}</p>
        <span class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $visit->status->color() }}">{{ $visit->status->label() }}</span>
    </div>
</div>

@if ($visit->address)
    <p class="mt-3 text-sm text-slate-600">{{ $visit->address }}</p>
@endif

<div class="mt-4 grid grid-cols-2 gap-3 text-sm">
    <div class="rounded-lg bg-slate-50 p-3">
        <p class="text-xs text-slate-500">Check-in</p>
        <p class="font-medium">{{ $visit->checked_in_at?->format('h:i A') ?? '—' }}</p>
        @if ($visit->check_in_latitude)
            <p class="text-xs text-teal-600 mt-1">📍 {{ number_format($visit->check_in_latitude, 5) }}, {{ number_format($visit->check_in_longitude, 5) }}</p>
        @endif
    </div>
    <div class="rounded-lg bg-slate-50 p-3">
        <p class="text-xs text-slate-500">Check-out</p>
        <p class="font-medium">{{ $visit->checked_out_at?->format('h:i A') ?? '—' }}</p>
        @if ($visit->check_out_latitude)
            <p class="text-xs text-teal-600 mt-1">📍 {{ number_format($visit->check_out_latitude, 5) }}, {{ number_format($visit->check_out_longitude, 5) }}</p>
        @endif
    </div>
</div>

@if ($visit->duration_minutes)
    <p class="mt-2 text-center text-sm font-medium text-slate-700">Duration: {{ $visit->formattedDuration() }}</p>
@endif

@if ($visit->status === \App\Enums\VisitStatus::Planned)
    <form method="POST" action="{{ route('mr.visits.check-in', $visit) }}" id="checkin-form" class="mt-6">
        @csrf
        <input type="hidden" name="latitude" id="checkin-lat">
        <input type="hidden" name="longitude" id="checkin-lng">
        <p id="checkin-status" class="mb-2 text-xs text-slate-500"></p>
        <button type="button" onclick="submitWithGps('checkin-form','checkin-lat','checkin-lng','checkin-status')" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white">Check in with GPS</button>
    </form>
@endif

@if ($visit->status === \App\Enums\VisitStatus::InProgress)
    <form method="POST" action="{{ route('mr.visits.check-out', $visit) }}" id="checkout-form" class="mt-6 space-y-3">
        @csrf
        <input type="hidden" name="latitude" id="checkout-lat">
        <input type="hidden" name="longitude" id="checkout-lng">
        <div>
            <label class="block text-sm font-medium text-slate-700">Visit notes</label>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ $visit->notes }}</textarea>
        </div>
        <p id="checkout-status" class="text-xs text-slate-500"></p>
        <button type="button" onclick="submitWithGps('checkout-form','checkout-lat','checkout-lng','checkout-status')" class="w-full rounded-xl bg-emerald-600 py-3 font-semibold text-white">Check out with GPS</button>
    </form>
@endif

@if ($visit->status === \App\Enums\VisitStatus::Completed)
<div class="mt-8 rounded-2xl border border-violet-200 bg-violet-50/50 p-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-violet-900">✨ AI visit summary</h2>
        @if ($aiAvailable && ! $visit->ai_summary)
            <form method="POST" action="{{ route('mr.visits.ai-summary', $visit) }}">
                @csrf
                <button type="submit" class="text-xs font-medium text-violet-700">Generate</button>
            </form>
        @endif
    </div>
    @if ($visit->ai_summary)
        <article class="mt-3 prose prose-sm prose-slate max-w-none">
            {!! \Illuminate\Support\Str::markdown($visit->ai_summary) !!}
        </article>
    @elseif ($aiAvailable)
        <p class="mt-2 text-sm text-violet-700">Summary will auto-generate on check-out, or tap Generate.</p>
    @else
        <p class="mt-2 text-sm text-slate-500">AI summaries are not configured for your company.</p>
    @endif
</div>
@endif

<div class="mt-8 rounded-2xl border border-slate-200 bg-white p-4">
    <h2 class="font-semibold text-slate-800">Notes</h2>
    @if ($visit->notes && $visit->status === \App\Enums\VisitStatus::Completed)
        <p class="mt-2 text-sm text-slate-600 whitespace-pre-wrap">{{ $visit->notes }}</p>
    @endif
    <form method="POST" action="{{ route('mr.visits.notes', $visit) }}" class="mt-3">
        @csrf
        <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Add visit notes...">{{ $visit->notes }}</textarea>
        <button type="submit" class="mt-2 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium">Save notes</button>
    </form>
</div>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
    <h2 class="font-semibold text-slate-800">Photos</h2>
    <div class="mt-3 grid grid-cols-3 gap-2">
        @foreach ($visit->photos as $photo)
            <a href="{{ $photo->url() }}" target="_blank" class="block aspect-square overflow-hidden rounded-lg border">
                <img src="{{ $photo->url() }}" alt="" class="h-full w-full object-cover">
            </a>
        @endforeach
    </div>
    @if ($visit->status !== \App\Enums\VisitStatus::Cancelled)
        <form method="POST" action="{{ route('mr.visits.photos', $visit) }}" enctype="multipart/form-data" class="mt-4">
            @csrf
            <input type="file" name="photos[]" accept="image/*" capture="environment" multiple class="text-sm">
            <button type="submit" class="mt-2 rounded-lg bg-teal-50 px-4 py-2 text-sm font-medium text-teal-700">Upload photos</button>
        </form>
    @endif
</div>

<a href="{{ route('mr.dashboard') }}" class="mt-6 block text-center text-sm text-teal-700">← Back to dashboard</a>
@endsection

@push('head')
@include('partials.gps-script')
@endpush

@extends('layouts.mr')

@section('title', 'Tour Plan — ' . $tourPlan->weekLabel())

@section('mr-content')
{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('mr.tour-plans.index') }}" class="text-slate-400 hover:text-slate-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $tourPlan->weekLabel() }}</h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $tourPlan->statusBadgeClass() }}">
                    {{ $tourPlan->statusLabel() }}
                </span>
                @if ($tourPlan->submitted_at)
                    <span class="text-xs text-slate-400">Submitted {{ $tourPlan->submitted_at->diffForHumans() }}</span>
                @endif
                @if ($tourPlan->reviewer)
                    <span class="text-xs text-slate-400">Reviewed by {{ $tourPlan->reviewer->name }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 flex-shrink-0">
        @if ($tourPlan->isDraft())
            <a href="{{ route('mr.tour-plans.edit', $tourPlan) }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Edit
            </a>
            <form method="POST" action="{{ route('mr.tour-plans.submit', $tourPlan) }}">
                @csrf
                <button type="submit"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                    Submit for Approval
                </button>
            </form>
            <form method="POST" action="{{ route('mr.tour-plans.destroy', $tourPlan) }}"
                onsubmit="return confirm('Delete this draft?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                    Delete
                </button>
            </form>
        @elseif ($tourPlan->isRejected())
            <p class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                <strong>Rejected:</strong> {{ $tourPlan->rejection_reason }}
            </p>
        @endif
    </div>
</div>

@foreach (session()->only(['success','error','info']) as $type => $msg)
    @php $colors = ['success' => 'emerald', 'error' => 'red', 'info' => 'blue'][$type] ?? 'slate'; @endphp
    <div class="mb-4 rounded-xl border border-{{ $colors }}-200 bg-{{ $colors }}-50 px-4 py-3 text-sm text-{{ $colors }}-800">{{ $msg }}</div>
@endforeach

{{-- Notes --}}
@if ($tourPlan->notes)
    <div class="mb-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
        <span class="font-medium text-slate-500 uppercase tracking-wide text-[10px]">Notes:</span> {{ $tourPlan->notes }}
    </div>
@endif

{{-- Legend --}}
<div class="mb-4 flex items-center gap-4 text-xs">
    <span class="flex items-center gap-1.5">
        <span class="h-3 w-3 rounded-full bg-emerald-500"></span> Visited
    </span>
    <span class="flex items-center gap-1.5">
        <span class="h-3 w-3 rounded-full bg-amber-400"></span> Planned
    </span>
    <span class="flex items-center gap-1.5">
        <span class="h-3 w-3 rounded-full bg-red-400"></span> Missed
    </span>
    @if (!$tourPlan->isApproved())
        <span class="ml-auto text-slate-400">Plan vs Actual comparison visible after approval</span>
    @endif
</div>

{{-- Day-by-day view --}}
<div class="space-y-4">
    @foreach ($planVsActual as $day => $dayData)
        @php
            $planned = $dayData['planned'];
            $actual  = $dayData['actual'];
            $isPast  = $dayData['date']->isPast() && $tourPlan->isApproved();
            $isToday = $dayData['date']->isToday();
        @endphp

        <div class="overflow-hidden rounded-2xl border {{ $isToday ? 'border-teal-400 shadow-md' : 'border-slate-200' }} bg-white shadow-sm">
            {{-- Day header --}}
            <div class="flex items-center gap-3 border-b {{ $isToday ? 'border-teal-100 bg-teal-50/60' : 'border-slate-100 bg-slate-50/70' }} px-5 py-3">
                <p class="font-semibold text-slate-900 text-sm">{{ $dayData['label'] }}</p>
                @if ($isToday)
                    <span class="rounded-full bg-teal-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">Today</span>
                @endif
                <span class="ml-auto text-xs text-slate-500">{{ $planned->count() }} planned · {{ $actual->count() }} actual</span>
            </div>

            <div class="divide-y divide-slate-100">
                @if ($planned->isEmpty() && $actual->isEmpty())
                    <p class="px-5 py-4 text-xs text-slate-400 italic">No stops planned for this day.</p>
                @endif

                {{-- Planned stops --}}
                @foreach ($planned as $stop)
                    @php
                        $visited = $tourPlan->isApproved()
                            ? $actual->where('customer_id', $stop->customer_id)->first()
                            : null;
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-3">
                        {{-- Status dot --}}
                        @if (!$tourPlan->isApproved())
                            <span class="h-3 w-3 flex-shrink-0 rounded-full bg-amber-400"></span>
                        @elseif ($visited)
                            <span class="h-3 w-3 flex-shrink-0 rounded-full bg-emerald-500"></span>
                        @elseif ($isPast)
                            <span class="h-3 w-3 flex-shrink-0 rounded-full bg-red-400"></span>
                        @else
                            <span class="h-3 w-3 flex-shrink-0 rounded-full bg-amber-400"></span>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900">{{ $stop->customer->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ ucfirst($stop->customer->type instanceof \BackedEnum ? $stop->customer->type->value : ($stop->customer->type ?? 'customer')) }}
                                @if ($stop->area) · {{ $stop->area }} @endif
                            </p>
                        </div>

                        @if ($visited)
                            <span class="text-xs font-medium text-emerald-600">
                                Checked in {{ $visited->checked_in_at ? \Carbon\Carbon::parse($visited->checked_in_at)->format('h:i A') : '' }}
                            </span>
                        @elseif ($isPast)
                            <span class="text-xs text-red-500 font-medium">Missed</span>
                        @endif
                    </div>
                @endforeach

                {{-- Unplanned actual visits --}}
                @if ($tourPlan->isApproved())
                    @foreach ($actual as $visit)
                        @if ($planned->where('customer_id', $visit->customer_id)->isEmpty())
                            <div class="flex items-center gap-3 bg-blue-50/50 px-5 py-3">
                                <span class="h-3 w-3 flex-shrink-0 rounded-full bg-blue-400"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900">{{ $visit->customer?->name ?? $visit->place_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">Unplanned visit</p>
                                </div>
                                <span class="text-xs text-blue-600 font-medium">Extra</span>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection

@extends('layouts.app')

@section('title', "Tour Plan — {$tourPlan->user->name}")

@section('content')
{{-- Breadcrumb / Header --}}
<div class="mb-6 flex items-start justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tour-plans.index') }}" class="text-slate-400 hover:text-slate-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $tourPlan->user->name }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ $tourPlan->weekLabel() }}</p>
            <div class="mt-2 flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $tourPlan->statusBadgeClass() }}">
                    {{ $tourPlan->statusLabel() }}
                </span>
                @if ($tourPlan->submitted_at)
                    <span class="text-xs text-slate-400">Submitted {{ $tourPlan->submitted_at->format('d M, h:i A') }}</span>
                @endif
                @if ($tourPlan->reviewer)
                    <span class="text-xs text-slate-400">· Reviewed by {{ $tourPlan->reviewer->name }}</span>
                @endif
            </div>
        </div>
    </div>

    @if ($tourPlan->isSubmitted())
        <div class="flex items-center gap-2 flex-shrink-0">
            <form method="POST" action="{{ route('admin.tour-plans.approve', $tourPlan) }}">
                @csrf
                <button type="submit"
                    class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Approve
                </button>
            </form>
            <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                class="rounded-xl border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100">
                Reject
            </button>
        </div>
    @endif
</div>

@foreach (session()->only(['success','error']) as $type => $msg)
    @php $c = $type === 'success' ? 'emerald' : 'red'; @endphp
    <div class="mb-4 rounded-xl border border-{{ $c }}-200 bg-{{ $c }}-50 px-4 py-3 text-sm text-{{ $c }}-800">{{ $msg }}</div>
@endforeach

@if ($tourPlan->rejection_reason)
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <strong>Rejection reason:</strong> {{ $tourPlan->rejection_reason }}
    </div>
@endif

@if ($tourPlan->notes)
    <div class="mb-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
        <span class="font-medium text-slate-500 uppercase tracking-wide text-[10px]">Notes:</span> {{ $tourPlan->notes }}
    </div>
@endif

{{-- Summary stats --}}
@if ($tourPlan->isApproved())
    @php
        $totalPlanned = $tourPlan->stops->count();
        $totalVisited = 0;
        $totalMissed  = 0;
        $today = now()->startOfDay();
        foreach ($planVsActual as $day => $d) {
            if ($d['date']->lt($today)) {
                foreach ($d['planned'] as $stop) {
                    if ($d['actual']->where('customer_id', $stop->customer_id)->isNotEmpty()) {
                        $totalVisited++;
                    } else {
                        $totalMissed++;
                    }
                }
            }
        }
        $pct = $totalPlanned > 0 ? round(($totalVisited / $totalPlanned) * 100) : 0;
    @endphp
    <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
            <p class="text-2xl font-bold text-slate-900">{{ $totalPlanned }}</p>
            <p class="text-xs text-slate-500 mt-1">Planned</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center shadow-sm">
            <p class="text-2xl font-bold text-emerald-700">{{ $totalVisited }}</p>
            <p class="text-xs text-emerald-600 mt-1">Completed</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-center shadow-sm">
            <p class="text-2xl font-bold text-red-700">{{ $totalMissed }}</p>
            <p class="text-xs text-red-600 mt-1">Missed</p>
        </div>
    </div>
@endif

{{-- Day-by-day --}}
<div class="space-y-4">
    @foreach ($planVsActual as $day => $dayData)
        @php
            $planned = $dayData['planned'];
            $actual  = $dayData['actual'];
            $isPast  = $dayData['date']->isPast() && $tourPlan->isApproved();
            $isToday = $dayData['date']->isToday();
        @endphp

        <div class="overflow-hidden rounded-2xl border {{ $isToday ? 'border-teal-400' : 'border-slate-200' }} bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b {{ $isToday ? 'border-teal-100 bg-teal-50/60' : 'border-slate-100 bg-slate-50/70' }} px-5 py-3">
                <p class="font-semibold text-slate-900 text-sm">{{ $dayData['label'] }}</p>
                @if ($isToday)
                    <span class="rounded-full bg-teal-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase">Today</span>
                @endif
                <span class="ml-auto text-xs text-slate-500">
                    {{ $planned->count() }} planned
                    @if ($tourPlan->isApproved()) · {{ $actual->count() }} actual @endif
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @if ($planned->isEmpty() && $actual->isEmpty())
                    <p class="px-5 py-4 text-xs text-slate-400 italic">Rest day — no stops planned.</p>
                @endif

                @foreach ($planned as $stop)
                    @php
                        $visit = $tourPlan->isApproved()
                            ? $actual->where('customer_id', $stop->customer_id)->first()
                            : null;
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-3">
                        @if (!$tourPlan->isApproved())
                            <span class="h-3 w-3 rounded-full bg-amber-400 flex-shrink-0"></span>
                        @elseif ($visit)
                            <span class="h-3 w-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                        @elseif ($isPast)
                            <span class="h-3 w-3 rounded-full bg-red-400 flex-shrink-0"></span>
                        @else
                            <span class="h-3 w-3 rounded-full bg-amber-400 flex-shrink-0"></span>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900">{{ $stop->customer->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ ucfirst($stop->customer->type instanceof \BackedEnum ? $stop->customer->type->value : ($stop->customer->type ?? 'customer')) }}
                                @if ($stop->area) · {{ $stop->area }} @endif
                            </p>
                        </div>

                        @if ($visit)
                            <div class="text-right">
                                <p class="text-xs font-medium text-emerald-600">Visited</p>
                                @if ($visit->checked_in_at)
                                    <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($visit->checked_in_at)->format('h:i A') }}</p>
                                @endif
                            </div>
                        @elseif ($isPast)
                            <span class="text-xs font-semibold text-red-500">Missed</span>
                        @endif
                    </div>
                @endforeach

                @if ($tourPlan->isApproved())
                    @foreach ($actual as $visit)
                        @if ($planned->where('customer_id', $visit->customer_id)->isEmpty())
                            <div class="flex items-center gap-4 bg-blue-50/40 px-5 py-3">
                                <span class="h-3 w-3 rounded-full bg-blue-400 flex-shrink-0"></span>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">{{ $visit->customer?->name ?? $visit->place_name }}</p>
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

{{-- Reject modal --}}
@if ($tourPlan->isSubmitted())
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Reject Tour Plan</h2>
            <p class="text-sm text-slate-500 mb-4">Provide a reason so {{ $tourPlan->user->name }} can revise and resubmit.</p>
            <form method="POST" action="{{ route('admin.tour-plans.reject', $tourPlan) }}">
                @csrf
                <textarea name="rejection_reason" rows="3" required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-red-400 focus:ring-1 focus:ring-red-400"
                    placeholder="e.g. Please add more doctors in North Delhi zone…"></textarea>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Reject Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

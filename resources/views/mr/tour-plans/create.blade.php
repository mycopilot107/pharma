@extends('layouts.mr')

@section('title', isset($tourPlan) ? 'Edit Tour Plan' : 'New Tour Plan')

@section('mr-content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('mr.tour-plans.index') }}" class="text-slate-400 hover:text-slate-700">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-xl font-bold text-slate-900">
        {{ isset($tourPlan) ? 'Edit Tour Plan' : 'New Tour Plan' }}
    </h1>
</div>

@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 space-y-1">
        @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
    </div>
@endif

<form method="POST"
    action="{{ isset($tourPlan) ? route('mr.tour-plans.update', $tourPlan) : route('mr.tour-plans.store') }}">
    @csrf
    @if (isset($tourPlan)) @method('PUT') @endif

    {{-- Week --}}
    <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

    <div class="mb-5 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <svg class="h-5 w-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <div>
            <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Week</p>
            <p class="font-semibold text-slate-900">{{ $weekStart->format('d M') }} – {{ $weekStart->copy()->addDays(5)->format('d M Y') }}</p>
        </div>
    </div>

    {{-- Notes --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <label class="block text-sm font-medium text-slate-700 mb-1.5" for="notes">Notes (optional)</label>
        <textarea name="notes" id="notes" rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
            placeholder="Any special instructions for this week…">{{ old('notes', $tourPlan->notes ?? '') }}</textarea>
    </div>

    {{-- Day-by-day stops --}}
    <div class="space-y-4" id="days-container">
        @foreach ($days as $dayIndex => $dayLabel)
            @php
                $existing = isset($tourPlan)
                    ? $tourPlan->stops->where('day_of_week', $dayIndex)->values()
                    : collect();
            @endphp

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" data-day="{{ $dayIndex }}">
                <div class="flex items-center justify-between rounded-t-2xl border-b border-slate-100 bg-slate-50/70 px-5 py-3">
                    <p class="font-semibold text-slate-800 text-sm">{{ $dayLabel }}</p>
                    <button type="button"
                        onclick="addStop({{ $dayIndex }})"
                        class="flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Stop
                    </button>
                </div>

                <div class="stops-list px-5 py-3 space-y-3" id="stops-day-{{ $dayIndex }}">
                    @if ($existing->isEmpty())
                        <p class="empty-msg text-xs text-slate-400 italic py-1">No stops yet — tap Add Stop</p>
                    @else
                        @foreach ($existing as $stop)
                            <div class="stop-row flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <input type="hidden" name="stops[{{ $dayIndex * 100 + $loop->iteration }}][day_of_week]" value="{{ $dayIndex }}">
                                <select name="stops[{{ $dayIndex * 100 + $loop->iteration }}][customer_id]"
                                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                    <option value="">Select customer…</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" @selected($c->id === $stop->customer_id)>{{ $c->name }}{{ $c->type ? ' ('.ucfirst($c->type).')' : '' }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="stops[{{ $dayIndex * 100 + $loop->iteration }}][area]"
                                    value="{{ $stop->area }}"
                                    placeholder="Area"
                                    class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                <button type="button" onclick="removeStop(this)" class="text-slate-400 hover:text-red-500 flex-shrink-0">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex items-center justify-end gap-3">
        <a href="{{ route('mr.tour-plans.index') }}"
            class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Cancel
        </a>
        <button type="submit"
            class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
            {{ isset($tourPlan) ? 'Update Plan' : 'Save as Draft' }}
        </button>
    </div>
</form>

{{-- Customer list for JS --}}
<script>
const CUSTOMERS = @json($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'type' => $c->type ?? null]));

let stopCounter = 9000; // avoid collisions with Blade-rendered indices

function addStop(day) {
    const container = document.getElementById('stops-day-' + day);
    const emptyMsg  = container.querySelector('.empty-msg');
    if (emptyMsg) emptyMsg.remove();

    const idx = ++stopCounter;
    const options = CUSTOMERS.map(c =>
        `<option value="${c.id}">${c.name}${c.type ? ' (' + ucFirst(c.type) + ')' : ''}</option>`
    ).join('');

    const row = document.createElement('div');
    row.className = 'stop-row flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3';
    row.innerHTML = `
        <input type="hidden" name="stops[${idx}][day_of_week]" value="${day}">
        <select name="stops[${idx}][customer_id]"
            class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            <option value="">Select customer…</option>
            ${options}
        </select>
        <input type="text" name="stops[${idx}][area]"
            placeholder="Area"
            class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        <button type="button" onclick="removeStop(this)" class="text-slate-400 hover:text-red-500 flex-shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;
    container.appendChild(row);
}

function removeStop(btn) {
    const row = btn.closest('.stop-row');
    const container = row.parentElement;
    row.remove();
    if (!container.querySelector('.stop-row')) {
        const msg = document.createElement('p');
        msg.className = 'empty-msg text-xs text-slate-400 italic py-1';
        msg.textContent = 'No stops yet — tap Add Stop';
        container.appendChild(msg);
    }
}

function ucFirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
</script>
@endsection

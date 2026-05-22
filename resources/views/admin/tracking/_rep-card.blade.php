@php
    $badgeClass = match ($rep['status_color']) {
        'emerald' => 'bg-emerald-100 text-emerald-800',
        'teal' => 'bg-teal-100 text-teal-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'red' => 'bg-red-100 text-red-800',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp
<div class="px-4 py-3 text-sm rep-card" data-rep-id="{{ $rep['id'] }}">
    <div class="flex items-start justify-between gap-2">
        <div>
            <p class="font-medium">{{ $rep['name'] }}</p>
            <span class="inline-block mt-1 rounded-full px-2 py-0.5 text-xs {{ $badgeClass }}">{{ $rep['status'] }}</span>
        </div>
        @if ($rep['is_live'])
            <span class="h-2 w-2 rounded-full bg-emerald-500 shrink-0 mt-1.5" title="Live"></span>
        @else
            <span class="h-2 w-2 rounded-full bg-slate-300 shrink-0 mt-1.5" title="Offline"></span>
        @endif
    </div>
    @if ($rep['active_visit'])
        <p class="text-xs text-slate-500 mt-1">📍 {{ $rep['active_visit']['place'] }}</p>
    @endif
    @if ($rep['attendance'])
        <p class="text-xs text-slate-500">🕐 {{ $rep['attendance']['clock_in'] }}{{ $rep['attendance']['clock_out'] ? ' – '.$rep['attendance']['clock_out'] : ' (active)' }}</p>
    @else
        <p class="text-xs text-red-500">Not clocked in</p>
    @endif
    <p class="text-xs text-slate-500 mt-1">✓ {{ $rep['visits_today']['completed'] }}/{{ $rep['visits_today']['total'] }} visits</p>
    @if ($rep['latitude'])
        <a href="https://www.google.com/maps?q={{ $rep['latitude'] }},{{ $rep['longitude'] }}" target="_blank" class="text-xs text-teal-700 mt-1 inline-block">Open in Maps →</a>
    @endif
</div>

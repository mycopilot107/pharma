@extends('layouts.app')

@section('title', 'Real-Time Tracking')

@section('content')
@if (!empty($googleMapsKey))
    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        Google Maps API key is active (configured in Super Admin → Platform settings).
        The Flutter app receives this key via <code class="text-xs">/api/v1/tracking/config</code>.
    </div>
@endif

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Real-time tracking</h1>
        <p class="text-slate-600">Live GPS, attendance, routes & visit completion for your field team</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> Live
        </span>
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Dashboard</a>
    </div>
</div>

<div class="mt-6 grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">MRs online</p>
        <p class="text-2xl font-bold text-emerald-600" id="stat-live">{{ $summary['live_now'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">Clocked in</p>
        <p class="text-2xl font-bold text-teal-600" id="stat-clocked">{{ $summary['clocked_in'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">On visit now</p>
        <p class="text-2xl font-bold text-blue-600" id="stat-visit">{{ $summary['on_visit'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">Visits done</p>
        <p class="text-2xl font-bold text-slate-800" id="stat-completed">{{ $summary['visits_completed'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">Visits today</p>
        <p class="text-2xl font-bold text-slate-800" id="stat-total">{{ $summary['visits_total'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
        <p class="text-xs text-slate-500">Team size</p>
        <p class="text-2xl font-bold text-slate-800">{{ $summary['total_mrs'] }}</p>
    </div>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-4">
        <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b px-4 py-3">
                <h2 class="font-semibold text-slate-800">Live map</h2>
                <p class="text-xs text-slate-500" id="map-updated">Updating…</p>
            </div>
            <div id="tracking-map" class="h-[420px] w-full bg-slate-100"></div>
        </div>

        <div class="rounded-2xl border bg-white shadow-sm">
            <div class="border-b px-4 py-3">
                <h2 class="font-semibold text-slate-800">Route history</h2>
                <p class="text-xs text-slate-500">Replay GPS trail for any rep</p>
            </div>
            <div class="p-4 flex flex-wrap gap-3">
                <select id="history-rep" class="rounded-lg border px-3 py-2 text-sm">
                    @foreach ($representatives as $rep)
                        <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                    @endforeach
                </select>
                <input type="date" id="history-date" value="{{ today()->toDateString() }}" class="rounded-lg border px-3 py-2 text-sm">
                <button type="button" id="load-route-btn" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Load route</button>
            </div>
            <div id="history-map" class="h-64 w-full bg-slate-50 border-t hidden"></div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="rounded-2xl border bg-white shadow-sm">
            <div class="border-b px-4 py-3">
                <h2 class="font-semibold text-slate-800">Field team status</h2>
            </div>
            <div id="reps-list" class="max-h-[320px] overflow-y-auto divide-y">
                @foreach ($liveReps as $rep)
                    @include('admin.tracking._rep-card', ['rep' => $rep])
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border bg-white shadow-sm">
            <div class="border-b px-4 py-3">
                <h2 class="font-semibold text-slate-800">Today's attendance</h2>
            </div>
            <div class="divide-y max-h-[280px] overflow-y-auto" id="attendance-list">
                @forelse ($attendance as $record)
                    <div class="px-4 py-3 text-sm">
                        <p class="font-medium">{{ $record->user->name }}</p>
                        <p class="text-xs text-slate-500">
                            In {{ $record->clock_in_at->format('h:i A') }}
                            @if ($record->clock_out_at)
                                · Out {{ $record->clock_out_at->format('h:i A') }}
                                · {{ $record->durationMinutes() }} min
                            @else
                                · <span class="text-emerald-600 font-medium">On duty</span>
                            @endif
                        </p>
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-slate-500 text-center">No attendance records today yet.</p>
                @endforelse
            </div>
        </div>

        @if ($suspiciousVisits->isNotEmpty())
        <div class="rounded-2xl border border-red-200 bg-red-50 shadow-sm">
            <div class="border-b border-red-200 px-4 py-3">
                <h2 class="font-semibold text-red-900">Visit validation alerts</h2>
                <p class="text-xs text-red-700">GPS fraud detection</p>
            </div>
            <div class="max-h-[240px] overflow-y-auto divide-y divide-red-100">
                @foreach ($suspiciousVisits as $validation)
                    <div class="px-4 py-3 text-sm">
                        <p class="font-medium text-red-900">{{ $validation->visit->user?->name }} — {{ $validation->visit->place_name }}</p>
                        <p class="text-xs text-red-700 mt-1">Risk {{ $validation->risk_score }}% · {{ implode(', ', $validation->flags ?? []) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="rounded-2xl border bg-white shadow-sm p-4">
            <h2 class="font-semibold text-slate-800">Visit completion</h2>
            <div id="visit-bars" class="mt-3 space-y-3">
                @foreach ($liveReps as $rep)
                    @php
                        $total = max(1, $rep['visits_today']['total']);
                        $pct = round(($rep['visits_today']['completed'] / $total) * 100);
                    @endphp
                    <div data-rep-id="{{ $rep['id'] }}">
                        <div class="flex justify-between text-xs mb-1">
                            <span>{{ $rep['name'] }}</span>
                            <span class="visit-count">{{ $rep['visits_today']['completed'] }}/{{ $rep['visits_today']['total'] }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="visit-bar h-full rounded-full bg-teal-500 transition-all" style="width:{{ $rep['visits_today']['total'] ? $pct : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const LIVE_URL = @json(route('admin.tracking.live'));
const ROUTE_URL_TEMPLATE = @json(route('admin.tracking.route', ['user' => '__ID__']));
const STATUS_COLORS = { teal: '#0d9488', emerald: '#059669', amber: '#d97706', red: '#dc2626', slate: '#64748b' };
const INITIAL_REPS = @json($liveReps);

let map = L.map('tracking-map').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
let markers = {};
let historyMap = null;
let historyLayer = null;

function repCardHtml(rep) {
    const maps = rep.latitude ? `https://www.google.com/maps?q=${rep.latitude},${rep.longitude}` : '#';
    const liveDot = rep.is_live
        ? '<span class="h-2 w-2 rounded-full bg-emerald-500 shrink-0 mt-1.5"></span>'
        : '<span class="h-2 w-2 rounded-full bg-slate-300 shrink-0 mt-1.5"></span>';
    const visitLine = rep.active_visit ? `<p class="text-xs text-slate-500 mt-1">📍 ${rep.active_visit.place}</p>` : '';
    const attLine = rep.attendance
        ? `<p class="text-xs text-slate-500">🕐 ${rep.attendance.clock_in}${rep.attendance.clock_out ? ' – ' + rep.attendance.clock_out : ' (active)'}</p>`
        : '<p class="text-xs text-red-500">Not clocked in</p>';
    const mapLink = rep.latitude ? `<a href="${maps}" target="_blank" class="text-xs text-teal-700 mt-1 inline-block">Open in Maps →</a>` : '';
    return `<div class="px-4 py-3 text-sm rep-card" data-rep-id="${rep.id}">
        <div class="flex items-start justify-between gap-2">
            <div><p class="font-medium">${rep.name}</p>
            <span class="inline-block mt-1 rounded-full px-2 py-0.5 text-xs">${rep.status}</span></div>
            ${liveDot}
        </div>
        ${visitLine}${attLine}
        <p class="text-xs text-slate-500 mt-1">✓ ${rep.visits_today.completed}/${rep.visits_today.total} visits</p>
        ${mapLink}
    </div>`;
}

function updateMap(reps) {
    const bounds = [];
    reps.forEach(rep => {
        if (!rep.latitude || !rep.longitude) return;
        bounds.push([rep.latitude, rep.longitude]);
        const color = STATUS_COLORS[rep.status_color] || STATUS_COLORS.teal;
        if (markers[rep.id]) {
            markers[rep.id].setLatLng([rep.latitude, rep.longitude]);
        } else {
            const icon = L.divIcon({
                className: '',
                html: `<div style="background:${color};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>`,
                iconSize: [14, 14], iconAnchor: [7, 7]
            });
            markers[rep.id] = L.marker([rep.latitude, rep.longitude], { icon })
                .addTo(map)
                .bindPopup(`<strong>${rep.name}</strong><br>${rep.status}<br>${rep.visits_today.completed}/${rep.visits_today.total} visits`);
        }
    });
    if (bounds.length) map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
}

function updateStats(reps) {
    document.getElementById('stat-live').textContent = reps.filter(r => r.is_live).length;
    document.getElementById('stat-clocked').textContent = reps.filter(r => r.attendance && r.attendance.active).length;
    document.getElementById('stat-visit').textContent = reps.filter(r => r.active_visit).length;
    document.getElementById('stat-completed').textContent = reps.reduce((s, r) => s + r.visits_today.completed, 0);
    document.getElementById('stat-total').textContent = reps.reduce((s, r) => s + r.visits_today.total, 0);
    document.getElementById('reps-list').innerHTML = reps.map(repCardHtml).join('');
    reps.forEach(rep => {
        const el = document.querySelector(`#visit-bars [data-rep-id="${rep.id}"]`);
        if (!el) return;
        const total = Math.max(1, rep.visits_today.total);
        const countEl = el.querySelector('.visit-count');
        const barEl = el.querySelector('.visit-bar');
        if (countEl) countEl.textContent = `${rep.visits_today.completed}/${rep.visits_today.total}`;
        if (barEl) barEl.style.width = (rep.visits_today.total ? Math.round(rep.visits_today.completed / total * 100) : 0) + '%';
    });
}

function refreshLive() {
    fetch(LIVE_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            updateMap(data.reps);
            updateStats(data.reps);
            document.getElementById('map-updated').textContent = 'Updated ' + new Date().toLocaleTimeString();
        });
}

updateMap(INITIAL_REPS);
setInterval(refreshLive, 15000);
refreshLive();

document.getElementById('load-route-btn')?.addEventListener('click', () => {
    const userId = document.getElementById('history-rep').value;
    const date = document.getElementById('history-date').value;
    const url = ROUTE_URL_TEMPLATE.replace('__ID__', userId) + '?date=' + date;
    fetch(url).then(r => r.json()).then(data => {
        const el = document.getElementById('history-map');
        el.classList.remove('hidden');
        if (!historyMap) {
            historyMap = L.map('history-map');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(historyMap);
            historyLayer = L.layerGroup().addTo(historyMap);
        }
        historyLayer.clearLayers();
        const latlngs = data.pings.map(p => [p.lat, p.lng]);
        let info = '';
        if (data.analytics) {
            info = `Distance: ${data.analytics.distance_km} km · Stops: ${data.analytics.stops?.length || 0} · Moving: ${data.analytics.moving_minutes} min`;
        }
        if (latlngs.length) {
            L.polyline(latlngs, { color: '#0d9488', weight: 4 }).addTo(historyLayer);
            L.marker(latlngs[0]).addTo(historyLayer).bindPopup('Start');
            L.marker(latlngs[latlngs.length - 1]).addTo(historyLayer).bindPopup('End' + (info ? '<br>' + info : ''));
            historyMap.fitBounds(latlngs, { padding: [20, 20] });
        }
        (data.analytics?.stops || []).forEach(s => {
            L.circleMarker([s.lat, s.lng], { radius: 8, color: '#d97706', fillColor: '#fbbf24', fillOpacity: 0.8 })
                .addTo(historyLayer).bindPopup(`Stop ${s.duration_minutes} min (${s.from}-${s.to})`);
        });
        data.visits.forEach(v => {
            if (v.check_in) {
                const risk = v.validation?.risk_score >= 50 ? ' ⚠' : '';
                L.marker([v.check_in.lat, v.check_in.lng]).addTo(historyLayer).bindPopup('Visit: ' + v.place + risk);
            }
        });
    });
});
</script>
@endpush

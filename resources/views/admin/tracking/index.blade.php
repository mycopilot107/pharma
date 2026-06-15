@extends('layouts.app')

@section('title', 'Tracking')

@section('content')

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Tracking</h1>
        <p class="text-slate-500 text-sm">Live GPS, route history &amp; playback for your field team</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> Live
        </span>
        <span class="text-xs text-slate-400" id="last-updated">Connecting…</span>
    </div>
</div>

{{-- Tab navigation --}}
<div class="flex gap-1 rounded-xl bg-slate-100 p-1 mb-6 w-fit">
    <button onclick="switchTab('live-location')" data-tab="live-location"
        class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition-all">
        📍 Live Location
    </button>
    <button onclick="switchTab('live-tracking')" data-tab="live-tracking"
        class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition-all">
        🔴 Live Tracking
    </button>
    <button onclick="switchTab('playback')" data-tab="playback"
        class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition-all">
        ⏪ Play Back
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB 1 — Live Location                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-live-location" class="tab-content">

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
            <p class="text-xs text-slate-500">MRs online</p>
            <p class="text-2xl font-bold text-emerald-600" id="stat-live">{{ $summary['live_now'] }}</p>
        </div>
        <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
            <p class="text-xs text-slate-500">Clocked in</p>
            <p class="text-2xl font-bold text-teal-600" id="stat-clocked">{{ $summary['clocked_in'] }}</p>
        </div>
        <div class="rounded-xl border bg-white p-4 text-center shadow-sm">
            <p class="text-xs text-slate-500">On visit</p>
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

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <h2 class="font-semibold text-slate-800">All MRs — Current Positions</h2>
                    <p class="text-xs text-slate-400" id="loc-updated">Updating…</p>
                </div>
                <div id="map-live-location" class="h-[480px] w-full bg-slate-100"></div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-4 py-3 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-800">Field Team</h2>
                    <span class="text-xs text-slate-400">{{ count($liveReps) }} MRs</span>
                </div>
                <div id="reps-list" class="max-h-[460px] overflow-y-auto divide-y">
                    @foreach ($liveReps as $rep)
                        @include('admin.tracking._rep-card', ['rep' => $rep])
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border bg-white shadow-sm p-4">
                <h2 class="font-semibold text-slate-800 mb-3">Visit Completion</h2>
                <div id="visit-bars" class="space-y-3">
                    @foreach ($liveReps as $rep)
                        @php
                            $pct = $rep['visits_today']['total']
                                ? round($rep['visits_today']['completed'] / max(1, $rep['visits_today']['total']) * 100)
                                : 0;
                        @endphp
                        <div data-rep-id="{{ $rep['id'] }}">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="truncate max-w-[140px]">{{ $rep['name'] }}</span>
                                <span class="visit-count text-slate-500">{{ $rep['visits_today']['completed'] }}/{{ $rep['visits_today']['total'] }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="visit-bar h-full rounded-full bg-teal-500 transition-all" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB 2 — Live Tracking                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-live-tracking" class="tab-content hidden">

    <div class="rounded-2xl border bg-white shadow-sm p-4 mb-4 flex flex-wrap items-center gap-3">
        <label class="text-sm font-medium text-slate-700">Select MR:</label>
        <select id="lt-rep" class="rounded-lg border border-slate-300 px-3 py-2 text-sm flex-1 max-w-xs">
            <option value="">— Choose a representative —</option>
            @foreach ($representatives as $rep)
                <option value="{{ $rep->id }}">{{ $rep->name }}</option>
            @endforeach
        </select>
        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> Auto-refreshes every 30s
        </span>
        <span id="lt-status" class="text-xs text-slate-400 ml-auto"></span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <h2 class="font-semibold text-slate-800" id="lt-map-title">Today's Route</h2>
                    <p class="text-xs text-slate-400" id="lt-map-updated">—</p>
                </div>
                <div id="lt-empty" class="h-[480px] flex items-center justify-center bg-slate-50">
                    <p class="text-slate-400 text-sm">Select a representative above to load today's route.</p>
                </div>
                <div id="map-live-tracking" class="h-[480px] w-full hidden"></div>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Attendance / Check-In / Check-Out card --}}
            <div class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-4 py-3">
                    <p class="font-semibold text-slate-800" id="lt-name">—</p>
                    <span id="lt-badge" class="inline-block mt-1 rounded-full px-2 py-0.5 text-xs bg-slate-100 text-slate-500">Not selected</span>
                </div>
                <div class="divide-y text-sm">
                    <div class="flex justify-between px-4 py-3">
                        <span class="text-slate-500">Check-In (Clock-In)</span>
                        <span class="font-medium text-emerald-700" id="lt-clock-in">—</span>
                    </div>
                    <div class="flex justify-between px-4 py-3">
                        <span class="text-slate-500">Check-Out (Clock-Out)</span>
                        <span class="font-medium" id="lt-clock-out">—</span>
                    </div>
                    <div class="flex justify-between px-4 py-3">
                        <span class="text-slate-500">Active Visit</span>
                        <span class="font-medium text-blue-600 text-right text-xs max-w-[140px]" id="lt-active-visit">—</span>
                    </div>
                    <div class="flex justify-between px-4 py-3">
                        <span class="text-slate-500">Visits Done</span>
                        <span class="font-medium" id="lt-visits-count">—</span>
                    </div>
                    <div class="flex justify-between px-4 py-3">
                        <span class="text-slate-500">Distance Today</span>
                        <span class="font-medium" id="lt-distance">—</span>
                    </div>
                    <div class="flex justify-between px-4 py-3">
                        <span class="text-slate-500">GPS Pings</span>
                        <span class="font-medium" id="lt-pings">—</span>
                    </div>
                </div>
            </div>

            {{-- Today's visits list --}}
            <div class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-4 py-3">
                    <h2 class="font-semibold text-slate-800">Today's Visits</h2>
                </div>
                <div id="lt-visits-list" class="divide-y max-h-[280px] overflow-y-auto">
                    <p class="px-4 py-4 text-sm text-slate-400 text-center">No data yet</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- TAB 3 — Play Back                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="tab-playback" class="tab-content hidden">

    <div class="rounded-2xl border bg-white shadow-sm p-4 mb-4 flex flex-wrap items-center gap-3">
        <label class="text-sm font-medium text-slate-700">MR:</label>
        <select id="pb-rep" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">— Select MR —</option>
            @foreach ($representatives as $rep)
                <option value="{{ $rep->id }}">{{ $rep->name }}</option>
            @endforeach
        </select>
        <label class="text-sm font-medium text-slate-700">Date:</label>
        <input type="date" id="pb-date" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button id="pb-load-btn" onclick="loadPlayback()"
            class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700 transition-colors disabled:opacity-50">
            Load Route
        </button>
        <span id="pb-status" class="text-xs text-slate-400"></span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <h2 class="font-semibold text-slate-800" id="pb-map-title">Route Playback</h2>
                    <div class="flex items-center gap-3 text-xs text-slate-400">
                        <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Start</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span> End</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-400"></span> Stop</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span> Visit</span>
                    </div>
                </div>
                <div id="pb-empty" class="h-[500px] flex items-center justify-center bg-slate-50">
                    <p class="text-slate-400 text-sm">Select an MR and date, then click Load Route.</p>
                </div>
                <div id="map-playback" class="h-[500px] w-full hidden"></div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-4 py-3">
                    <h2 class="font-semibold text-slate-800">Route Analytics</h2>
                </div>
                <div class="divide-y text-sm">
                    <div class="flex justify-between px-4 py-3"><span class="text-slate-500">Distance</span><span class="font-medium" id="pb-distance">—</span></div>
                    <div class="flex justify-between px-4 py-3"><span class="text-slate-500">Moving Time</span><span class="font-medium" id="pb-moving">—</span></div>
                    <div class="flex justify-between px-4 py-3"><span class="text-slate-500">GPS Pings</span><span class="font-medium" id="pb-pings">—</span></div>
                    <div class="flex justify-between px-4 py-3"><span class="text-slate-500">Stops</span><span class="font-medium" id="pb-stops">—</span></div>
                    <div class="flex justify-between px-4 py-3"><span class="text-slate-500">Visits</span><span class="font-medium" id="pb-visits">—</span></div>
                </div>
            </div>

            <div class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-4 py-3">
                    <h2 class="font-semibold text-slate-800">Visit Timeline</h2>
                </div>
                <div id="pb-timeline" class="divide-y max-h-[360px] overflow-y-auto">
                    <p class="px-4 py-4 text-sm text-slate-400 text-center">No data</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.tab-btn { color: #64748b; }
.tab-btn.active-tab { background: #fff; color: #0f172a; font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
@keyframes livepulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const LIVE_URL      = @json(route('admin.tracking.live'));
const ROUTE_URL_TPL = @json(route('admin.tracking.route', ['user' => '__ID__']));
const INITIAL_REPS  = @json($liveReps);
const TODAY         = @json(today()->toDateString());

// ── State ──────────────────────────────────────────────────────────────────
let liveCache  = [...INITIAL_REPS];
let currentTab = 'live-location';
let liveTimer  = null;
let ltTimer    = null;
let ltRepId    = null;

// ── Maps (null = not yet initialised) ─────────────────────────────────────
let locMap = null, locMarkers = {};
let ltMap  = null, ltLayer = null, ltMrMarker = null;
let pbMap  = null, pbLayer = null;

const STATUS_HEX = { emerald:'#059669', teal:'#0d9488', amber:'#d97706', red:'#dc2626', slate:'#64748b' };

// ── Tab switching ──────────────────────────────────────────────────────────
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active-tab'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    document.querySelector('[data-tab="' + tab + '"]').classList.add('active-tab');
    currentTab = tab;
    clearInterval(liveTimer);
    clearInterval(ltTimer);

    if (tab === 'live-location') {
        setTimeout(() => locMap?.invalidateSize(), 200);
        liveTimer = setInterval(refreshLive, 15000);
    } else if (tab === 'live-tracking') {
        if (ltRepId) {
            // Re-entering tab with a rep already selected — re-init/invalidate then reload
            initLtMap(() => { loadLtRoute(); startLtRefresh(); });
        } else if (ltMap) {
            setTimeout(() => ltMap.invalidateSize(), 200);
        }
    } else if (tab === 'playback') {
        if (pbMap) setTimeout(() => pbMap.invalidateSize(), 200);
    }
}

// ── Helpers ────────────────────────────────────────────────────────────────
function initials(name) {
    return (name || '').split(' ').slice(0,2).map(w => w[0] || '').join('').toUpperCase() || '?';
}
function mrIcon(color, name, live) {
    const hex  = STATUS_HEX[color] || STATUS_HEX.slate;
    const ring = live ? `style="position:absolute;inset:-4px;border-radius:50%;border:2px solid ${hex};opacity:.4;animation:livepulse 1.5s infinite"` : '';
    const dot  = live ? `<div ${ring}></div>` : '';
    return L.divIcon({
        className: '',
        html: `<div style="position:relative;width:36px;height:36px">${dot}<div style="position:absolute;inset:0;background:${hex};color:#fff;border-radius:50%;border:2.5px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700">${initials(name)}</div></div>`,
        iconSize: [36,36], iconAnchor:[18,18], popupAnchor:[0,-20]
    });
}
function repPopup(rep) {
    const hex = STATUS_HEX[rep.status_color] || STATUS_HEX.slate;
    const att = rep.attendance
        ? `<div>⏰ Check-In: <b>${rep.attendance.clock_in}</b></div>
           <div>${rep.attendance.clock_out ? '⏰ Check-Out: <b>' + rep.attendance.clock_out + '</b>' : '<span style="color:#059669">● On duty</span>'}</div>`
        : `<div style="color:#dc2626">✗ Not clocked in</div>`;
    const visit = rep.active_visit ? `<div>🔵 On visit: <b>${rep.active_visit.place}</b></div>` : '';
    const maps  = rep.latitude ? `<a href="https://www.google.com/maps?q=${rep.latitude},${rep.longitude}" target="_blank" style="color:#0d9488;font-size:11px">Open in Maps →</a>` : '';
    return `<div style="min-width:230px;font-family:system-ui,sans-serif;font-size:12px;line-height:1.7">
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">${rep.name}</div>
        <span style="background:${hex};color:#fff;padding:1px 9px;border-radius:9999px;font-size:11px">${rep.status}</span>
        <div style="margin-top:8px;color:#475569">${att}${visit}
        <div>✓ Visits: <b>${rep.visits_today.completed}/${rep.visits_today.total}</b></div>${maps}</div>
    </div>`;
}

// ── TAB 1: Live Location ───────────────────────────────────────────────────
locMap = L.map('map-live-location').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(locMap);

function updateLocMap(reps) {
    const bounds = [];
    reps.forEach(rep => {
        if (!rep.latitude || !rep.longitude) return;
        bounds.push([rep.latitude, rep.longitude]);
        if (locMarkers[rep.id]) {
            locMarkers[rep.id].setLatLng([rep.latitude, rep.longitude]);
            locMarkers[rep.id].setIcon(mrIcon(rep.status_color, rep.name, rep.is_live));
            locMarkers[rep.id].setPopupContent(repPopup(rep));
        } else {
            locMarkers[rep.id] = L.marker([rep.latitude, rep.longitude], { icon: mrIcon(rep.status_color, rep.name, rep.is_live) })
                .addTo(locMap).bindPopup(repPopup(rep), { maxWidth: 280 });
        }
    });
    if (bounds.length) locMap.fitBounds(bounds, { padding:[40,40], maxZoom:14 });
}

function updateStats(reps) {
    const byId = id => document.getElementById(id);
    byId('stat-live').textContent      = reps.filter(r => r.is_live).length;
    byId('stat-clocked').textContent   = reps.filter(r => r.attendance?.active).length;
    byId('stat-visit').textContent     = reps.filter(r => r.active_visit).length;
    byId('stat-completed').textContent = reps.reduce((s,r) => s + r.visits_today.completed, 0);
    byId('stat-total').textContent     = reps.reduce((s,r) => s + r.visits_today.total, 0);

    const scls = { emerald:'bg-emerald-100 text-emerald-800', teal:'bg-teal-100 text-teal-800', amber:'bg-amber-100 text-amber-800', red:'bg-red-100 text-red-800' };
    byId('reps-list').innerHTML = reps.map(r => {
        const cls = scls[r.status_color] || 'bg-slate-100 text-slate-600';
        const dot = r.is_live ? '<span class="h-2 w-2 rounded-full bg-emerald-500 shrink-0"></span>' : '<span class="h-2 w-2 rounded-full bg-slate-300 shrink-0"></span>';
        const att = r.attendance
            ? `<p class="text-xs text-slate-500">⏰ ${r.attendance.clock_in}${r.attendance.clock_out ? ' – ' + r.attendance.clock_out : ' (active)'}</p>`
            : '<p class="text-xs text-red-500">Not clocked in</p>';
        const onVisit = r.active_visit ? `<p class="text-xs text-blue-600">📍 ${r.active_visit.place}</p>` : '';
        return `<div class="px-4 py-3 text-sm">
            <div class="flex items-center justify-between gap-2 mb-1">
                <div class="flex items-center gap-2">${dot}<p class="font-medium">${r.name}</p></div>
                <span class="rounded-full px-2 py-0.5 text-xs ${cls}">${r.status}</span>
            </div>${att}${onVisit}
            <p class="text-xs text-slate-500 mt-0.5">✓ ${r.visits_today.completed}/${r.visits_today.total} visits</p>
        </div>`;
    }).join('');

    reps.forEach(r => {
        const el = document.querySelector(`#visit-bars [data-rep-id="${r.id}"]`);
        if (!el) return;
        const pct = r.visits_today.total ? Math.round(r.visits_today.completed / Math.max(1,r.visits_today.total) * 100) : 0;
        const cnt = el.querySelector('.visit-count');
        const bar = el.querySelector('.visit-bar');
        if (cnt) cnt.textContent = `${r.visits_today.completed}/${r.visits_today.total}`;
        if (bar) bar.style.width = pct + '%';
    });
}

function refreshLive() {
    fetch(LIVE_URL, { headers:{ Accept:'application/json' } })
        .then(r => r.json())
        .then(d => {
            liveCache = d.reps;
            if (currentTab === 'live-location') {
                updateLocMap(d.reps);
                updateStats(d.reps);
                document.getElementById('loc-updated').textContent = 'Updated ' + new Date().toLocaleTimeString();
            }
            document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
        }).catch(() => {});
}

// ── TAB 2: Live Tracking ───────────────────────────────────────────────────
// initLtMap shows the map container, waits 150 ms for the browser to
// lay it out (fixes the Leaflet 0×0 hidden-div bug), then initialises
// Leaflet and fires cb() so the caller can load data.
function initLtMap(cb) {
    if (ltMap) {
        // Already initialised — just fix size then call back
        setTimeout(() => { ltMap.invalidateSize(); if (cb) cb(); }, 200);
        return;
    }
    document.getElementById('lt-empty').classList.add('hidden');
    document.getElementById('map-live-tracking').classList.remove('hidden');
    setTimeout(() => {
        ltMap   = L.map('map-live-tracking').setView([20.5937, 78.9629], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(ltMap);
        ltLayer = L.layerGroup().addTo(ltMap);
        if (cb) cb();
    }, 150);
}

document.getElementById('lt-rep').addEventListener('change', function () {
    ltRepId = this.value || null;
    clearInterval(ltTimer);
    if (!ltRepId) return;
    initLtMap(() => {
        loadLtRoute();
        startLtRefresh();
    });
});

function startLtRefresh() {
    ltTimer = setInterval(() => {
        if (currentTab === 'live-tracking' && ltRepId) loadLtRoute();
    }, 30000);
}

function loadLtRoute() {
    if (!ltRepId || !ltMap) return; // map not ready yet — cb will call us
    document.getElementById('lt-status').textContent = 'Loading…';
    const url = ROUTE_URL_TPL.replace('__ID__', ltRepId) + '?date=' + TODAY;

    Promise.all([
        fetch(url,      { headers:{ Accept:'application/json','X-Requested-With':'XMLHttpRequest' } }).then(r=>r.json()),
        fetch(LIVE_URL, { headers:{ Accept:'application/json' } }).then(r=>r.json())
    ]).then(([route, live]) => {
        liveCache = live.reps;
        const rep     = live.reps.find(r => r.id == ltRepId);
        const pings   = Array.isArray(route.pings) ? route.pings : [];
        const visits  = route.visits || [];
        const an      = route.analytics;

        ltLayer.clearLayers();
        if (ltMrMarker) { ltMrMarker.remove(); ltMrMarker = null; }

        // Route polyline + start marker
        if (pings.length > 1) {
            const ll = pings.map(p => [p.latitude, p.longitude]);
            L.polyline(ll, { color:'#0d9488', weight:4, opacity:0.85 }).addTo(ltLayer);
            L.circleMarker(ll[0], { radius:7, color:'#059669', fillColor:'#6ee7b7', fillOpacity:1, weight:2 })
                .addTo(ltLayer).bindPopup('Start of day');
        }

        // Visit markers (blue dots)
        visits.forEach(v => {
            if (!v.check_in) return;
            const risk = (v.validation?.risk_score ?? 0) >= 50 ? ' ⚠️' : '';
            L.circleMarker([v.check_in.lat, v.check_in.lng], {
                radius:8, color:'#3b82f6', fillColor:'#93c5fd', fillOpacity:0.9, weight:2
            }).addTo(ltLayer).bindPopup(
                `<b>${v.place}${v.customer ? ' — '+v.customer:''}</b>${risk}<br>
                 Status: ${v.status}${v.duration_minutes ? ' · '+v.duration_minutes+' min':''}`
            );
        });

        // Current live position — pulsing icon
        if (rep?.latitude && rep?.longitude) {
            ltMrMarker = L.marker([rep.latitude, rep.longitude], { icon: mrIcon(rep.status_color, rep.name, true) })
                .addTo(ltMap).bindPopup(repPopup(rep), { maxWidth:280 });
        }

        // Fit bounds
        const pts = pings.map(p=>[p.latitude,p.longitude]);
        if (rep?.latitude) pts.push([rep.latitude,rep.longitude]);
        if (pts.length) ltMap.fitBounds(pts, { padding:[30,30], maxZoom:15 });

        // Detail card
        if (rep) {
            document.getElementById('lt-name').textContent = rep.name;
            const scls = { emerald:'bg-emerald-100 text-emerald-800', teal:'bg-teal-100 text-teal-800', amber:'bg-amber-100 text-amber-800', red:'bg-red-100 text-red-800' };
            const badge = document.getElementById('lt-badge');
            badge.textContent = rep.status;
            badge.className   = 'inline-block mt-1 rounded-full px-2 py-0.5 text-xs ' + (scls[rep.status_color] || 'bg-slate-100 text-slate-600');
            document.getElementById('lt-clock-in').textContent  = rep.attendance?.clock_in  || 'Not clocked in';
            document.getElementById('lt-clock-out').textContent = rep.attendance?.clock_out || (rep.attendance ? 'On duty ✅' : '—');
            document.getElementById('lt-active-visit').textContent = rep.active_visit?.place || '—';
            document.getElementById('lt-visits-count').textContent = `${rep.visits_today.completed} / ${rep.visits_today.total}`;
            document.getElementById('lt-map-title').textContent = rep.name + ' — Today';
        }
        document.getElementById('lt-distance').textContent = an?.distance_km ? an.distance_km + ' km' : '—';
        document.getElementById('lt-pings').textContent    = pings.length || '—';

        // Visits list
        document.getElementById('lt-visits-list').innerHTML = visits.length
            ? visits.map(v => `<div class="px-4 py-3 text-sm">
                <p class="font-medium">${v.place}${v.customer ? ' <span class="text-slate-400 font-normal">— '+v.customer+'</span>':''}</p>
                <p class="text-xs text-slate-500">${v.status}${v.duration_minutes?' · '+v.duration_minutes+' min':''}${(v.validation?.risk_score??0)>=50?' · ⚠️ Risk '+v.validation.risk_score+'%':''}</p>
              </div>`).join('')
            : '<p class="px-4 py-4 text-sm text-slate-400 text-center">No visits recorded today</p>';

        document.getElementById('lt-status').textContent    = `${pings.length} GPS pings${an?` · ${an.distance_km} km`:''}`;
        document.getElementById('lt-map-updated').textContent = 'Updated ' + new Date().toLocaleTimeString();
    }).catch(() => {
        document.getElementById('lt-status').textContent = 'Error loading data';
    });
}

// ── TAB 3: Play Back ───────────────────────────────────────────────────────
function loadPlayback() {
    const repId   = document.getElementById('pb-rep').value;
    const date    = document.getElementById('pb-date').value;
    if (!repId) { alert('Please select an MR.'); return; }
    if (!date)  { alert('Please select a date.'); return; }

    const repName = document.getElementById('pb-rep').selectedOptions[0]?.text || 'MR';
    document.getElementById('pb-status').textContent    = 'Loading…';
    document.getElementById('pb-map-title').textContent = repName + ' — ' + date;
    document.getElementById('pb-load-btn').disabled     = true;

    // Show map container, then wait 150 ms for browser layout before
    // initialising Leaflet (fixes the 0×0 hidden-div tile bug).
    document.getElementById('pb-empty').classList.add('hidden');
    document.getElementById('map-playback').classList.remove('hidden');

    setTimeout(() => {
        if (!pbMap) {
            pbMap   = L.map('map-playback').setView([20.5937, 78.9629], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(pbMap);
            pbLayer = L.layerGroup().addTo(pbMap);
        } else {
            pbMap.invalidateSize();
        }
        runPlaybackFetch(repId, date);
    }, 150);
}

function runPlaybackFetch(repId, date) {
    const url = ROUTE_URL_TPL.replace('__ID__', repId) + '?date=' + date;
    fetch(url, { headers:{ Accept:'application/json','X-Requested-With':'XMLHttpRequest' } })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(data => {
            pbLayer.clearLayers();
            const pings  = Array.isArray(data.pings) ? data.pings : [];
            const visits = data.visits || [];
            const an     = data.analytics;
            const ll     = pings.map(p=>[p.latitude,p.longitude]);
            const allPts = [...ll];

            // Route
            if (ll.length > 1) {
                L.polyline(ll, { color:'#0d9488', weight:4, opacity:0.85 }).addTo(pbLayer);
                L.circleMarker(ll[0], { radius:8, color:'#059669', fillColor:'#6ee7b7', fillOpacity:1, weight:2 })
                    .addTo(pbLayer).bindPopup('<b>Start</b>');
                L.circleMarker(ll[ll.length-1], { radius:8, color:'#dc2626', fillColor:'#fca5a5', fillOpacity:1, weight:2 })
                    .addTo(pbLayer).bindPopup('<b>End</b>' + (an?`<br>${an.distance_km} km · ${an.moving_minutes} min`:''));
            } else if (visits.filter(v=>v.check_in).length) {
                const vPts = visits.filter(v=>v.check_in).map(v=>[v.check_in.lat,v.check_in.lng]);
                L.polyline(vPts, { color:'#6366f1', weight:3, dashArray:'8 6', opacity:0.7 }).addTo(pbLayer);
                vPts.forEach(p=>allPts.push(p));
            }

            // Stop markers (amber)
            (an?.stops || []).forEach(s => {
                allPts.push([s.lat,s.lng]);
                L.circleMarker([s.lat,s.lng], { radius:8, color:'#d97706', fillColor:'#fbbf24', fillOpacity:0.85, weight:2 })
                    .addTo(pbLayer).bindPopup(`⏸ Stop: ${s.duration_minutes} min<br>(${s.from}–${s.to})`);
            });

            // Visit markers (blue)
            visits.forEach(v => {
                if (!v.check_in) return;
                allPts.push([v.check_in.lat,v.check_in.lng]);
                const risk = (v.validation?.risk_score??0)>=50?' ⚠️':'';
                L.marker([v.check_in.lat,v.check_in.lng]).addTo(pbLayer)
                    .bindPopup(`<b>${v.place}${v.customer?' — '+v.customer:''}</b>${risk}<br>Status: ${v.status}${v.duration_minutes?' · '+v.duration_minutes+' min':''}`);
            });

            if (allPts.length) pbMap.fitBounds(allPts, { padding:[30,30], maxZoom:15 });

            // Analytics
            document.getElementById('pb-distance').textContent = an?.distance_km  ? an.distance_km+' km'     : '—';
            document.getElementById('pb-moving').textContent   = an?.moving_minutes ? an.moving_minutes+' min' : '—';
            document.getElementById('pb-pings').textContent    = pings.length || '—';
            document.getElementById('pb-stops').textContent    = an?.stops?.length ?? '—';
            document.getElementById('pb-visits').textContent   = visits.length || '—';

            // Timeline
            document.getElementById('pb-timeline').innerHTML = visits.length
                ? visits.map(v => `<div class="px-4 py-3 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="mt-1 h-2 w-2 rounded-full ${v.status==='completed'?'bg-teal-500':'bg-amber-400'} shrink-0"></span>
                        <div>
                            <p class="font-medium">${v.place}${v.customer?' <span class="text-slate-400 font-normal">— '+v.customer+'</span>':''}</p>
                            <p class="text-xs text-slate-500">${v.status}${v.duration_minutes?' · '+v.duration_minutes+' min':''}${(v.validation?.risk_score??0)>=50?' · ⚠️ Risk '+v.validation.risk_score+'%':''}</p>
                        </div>
                    </div>
                  </div>`).join('')
                : '<p class="px-4 py-4 text-sm text-slate-400 text-center">No visits on this date</p>';

            const msg = ll.length ? `${ll.length} GPS pings${an?' · '+an.distance_km+' km':''}` :
                        visits.length ? `No GPS pings — ${visits.length} visit locations` : 'No data for this date';
            document.getElementById('pb-status').textContent = msg;
        })
        .catch(err => { document.getElementById('pb-status').textContent = 'Error: ' + err.message; })
        .finally(() => { document.getElementById('pb-load-btn').disabled = false; });
}

// ── Bootstrap ──────────────────────────────────────────────────────────────
document.querySelector('[data-tab="live-location"]').classList.add('active-tab');
updateLocMap(liveCache);
updateStats(liveCache);
liveTimer = setInterval(refreshLive, 15000);
refreshLive();
</script>
@endpush

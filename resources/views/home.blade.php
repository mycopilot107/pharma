@extends('layouts.app')

@section('title', 'Medical Representative Tracking App — GPS, Visit Proof & DCR | MR Visits Track')
@section('meta_description', 'Track every medical representative visit in real-time. GPS tracking, doctor visit proof, daily call reports, WhatsApp summaries and fake GPS detection. Best MR tracking app for pharma companies in India.')
@section('canonical', url('/'))
@section('og_title', 'Medical Representative Tracking App | MR Visits Track')

@section('content')
{{-- ═══════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════ --}}
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-teal-900 px-8 py-20 text-center sm:py-28">
    {{-- Decorative orbs --}}
    <div class="pointer-events-none absolute -right-20 -top-20 h-80 w-80 rounded-full bg-teal-500/10"></div>
    <div class="pointer-events-none absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-teal-500/10"></div>
    <div class="pointer-events-none absolute right-1/3 top-8 h-48 w-48 rounded-full bg-white/5"></div>

    <div class="relative">
        <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-teal-300">
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-teal-400"></span>
            Pharma Field Force Management Platform
        </div>
        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Complete Visibility Over<br class="hidden sm:block">
            <span class="text-teal-400">Your Medical Field Force</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-300">
            Real-time GPS tracking, verified doctor visit proof, automated Daily Call Reports, and AI-powered fraud detection — the enterprise platform purpose-built for pharma field force management.
        </p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="{{ route('companies.register') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-8 py-4 text-base font-bold text-white shadow-lg shadow-teal-500/25 transition-colors hover:bg-teal-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Start 7-Day Free Demo
            </a>
            <a href="{{ route('login') }}"
                class="rounded-xl border border-white/20 bg-white/10 px-7 py-4 text-base font-semibold text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                Admin / MR Login
            </a>
        </div>
        <p class="mt-5 text-sm text-slate-400">No credit card required &nbsp;·&nbsp; Full system access &nbsp;·&nbsp; Setup in 2 minutes</p>
    </div>
</div>

{{-- ── Stats strip ─────────────────────────────────────────── --}}
<div class="mb-4 mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 shadow-sm sm:grid-cols-4">
    <div class="bg-white px-6 py-5 text-center">
        <p class="text-2xl font-bold text-slate-900">50+</p>
        <p class="mt-0.5 text-sm text-slate-500">Pharma companies</p>
    </div>
    <div class="bg-white px-6 py-5 text-center">
        <p class="text-2xl font-bold text-teal-600">10,000+</p>
        <p class="mt-0.5 text-sm text-slate-500">Visits tracked daily</p>
    </div>
    <div class="bg-white px-6 py-5 text-center">
        <p class="text-2xl font-bold text-slate-900">500+</p>
        <p class="mt-0.5 text-sm text-slate-500">Medical representatives</p>
    </div>
    <div class="bg-white px-6 py-5 text-center">
        <p class="text-2xl font-bold text-rose-600">Zero</p>
        <p class="mt-0.5 text-sm text-slate-500">Fake visits undetected</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     LIVE DASHBOARD SHOWCASE
═══════════════════════════════════════════════════════════ --}}
<section class="mt-20">

    <div class="mb-10 text-center">
        <span class="inline-block rounded-full bg-teal-100 px-4 py-1 text-sm font-semibold text-teal-700">Live Dashboard</span>
        <h2 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Full control. Without calling anyone.</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-500">See your entire field team in one screen — who is active, where they are, and every doctor they visited today.</p>
    </div>

    {{-- Dashboard mockup --}}
    <div class="mx-auto max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

        {{-- Window bar --}}
        <div class="flex items-center justify-between bg-slate-900 px-5 py-3">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-red-500"></span>
                <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                <span class="ml-4 text-sm font-semibold text-white">MR Visits Track — Live Dashboard</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                <span class="text-xs text-slate-400">Live · Updated just now</span>
            </div>
        </div>

        {{-- Stat strip --}}
        <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100 bg-slate-50 sm:grid-cols-4">
            <div class="px-5 py-4">
                <p class="text-xs font-medium text-slate-500">MRs Active Now</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">19</p>
                <p class="text-xs text-slate-400">of 24 on duty</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs font-medium text-slate-500">Doctor Visits Today</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">124</p>
                <p class="text-xs text-slate-400">↑ 12 from yesterday</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs font-medium text-slate-500">Missed Check-ins</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">6</p>
                <p class="text-xs text-slate-400">Needs follow-up</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs font-medium text-slate-500">Fake Visits Flagged</p>
                <p class="mt-1 text-2xl font-bold text-rose-600">2</p>
                <p class="text-xs text-slate-400">GPS mismatch detected</p>
            </div>
        </div>

        {{-- Body: MR list + map --}}
        <div class="grid divide-y divide-slate-100 lg:grid-cols-5 lg:divide-x lg:divide-y-0">

            {{-- MR status list --}}
            <div class="lg:col-span-2">
                <div class="bg-slate-50 px-5 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">MR Status</p>
                </div>
                <div class="divide-y divide-slate-50">
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">Ramesh Kumar</p>
                            <p class="text-xs text-slate-400">Visiting: Dr. Patel Clinic · 11 min ago</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Active</span>
                    </div>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">Sunita Rao</p>
                            <p class="text-xs text-slate-400">Travelling · 2.4 km from next target</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">Moving</span>
                    </div>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">Ajay Singh</p>
                            <p class="text-xs text-slate-400">No GPS ping · 48 min</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Idle</span>
                    </div>
                    <div class="flex items-center gap-3 bg-rose-50/70 px-5 py-3">
                        <span class="h-2.5 w-2.5 shrink-0 animate-pulse rounded-full bg-rose-500"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-rose-800">Vikram Nair</p>
                            <p class="text-xs text-rose-500">⚠ GPS faked · Location mismatch</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">Alert</span>
                    </div>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-slate-300"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-400">Priya Mehta</p>
                            <p class="text-xs text-slate-400">Clocked out · 17:32</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-400">Done</span>
                    </div>
                </div>
            </div>

            {{-- Map + visit feed --}}
            <div class="flex flex-col lg:col-span-3">

                {{-- CSS map simulation --}}
                <div class="relative min-h-[220px] flex-1 overflow-hidden bg-[#e8f0e4]">
                    <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
                        {{-- Road grid --}}
                        <rect width="100%" height="100%" fill="#e8f0e4"/>
                        <line x1="0" y1="80"  x2="100%" y2="80"  stroke="#d1d5db" stroke-width="8"/>
                        <line x1="0" y1="160" x2="100%" y2="160" stroke="#d1d5db" stroke-width="5"/>
                        <line x1="120" y1="0"  x2="120" y2="100%" stroke="#d1d5db" stroke-width="8"/>
                        <line x1="300" y1="0"  x2="300" y2="100%" stroke="#d1d5db" stroke-width="5"/>
                        <line x1="460" y1="0"  x2="460" y2="100%" stroke="#d1d5db" stroke-width="4"/>
                        {{-- City blocks --}}
                        <rect x="130" y="90"  width="60" height="60" rx="2" fill="#cbd5e1" opacity=".5"/>
                        <rect x="210" y="20"  width="80" height="50" rx="2" fill="#cbd5e1" opacity=".4"/>
                        <rect x="310" y="90"  width="70" height="60" rx="2" fill="#cbd5e1" opacity=".5"/>
                        <rect x="390" y="20"  width="60" height="50" rx="2" fill="#cbd5e1" opacity=".4"/>
                        <rect x="130" y="170" width="80" height="40" rx="2" fill="#cbd5e1" opacity=".4"/>
                        <rect x="310" y="170" width="80" height="40" rx="2" fill="#cbd5e1" opacity=".4"/>
                        {{-- MR route 1 (Ramesh) --}}
                        <polyline points="40,200 80,160 120,160 120,80 200,80 300,80 300,50 350,50 420,50 460,50"
                            fill="none" stroke="#0d9488" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
                        {{-- MR route 2 (Sunita) --}}
                        <polyline points="30,30 120,30 120,80 160,80 200,80"
                            fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="6,3" opacity="0.8"/>
                        {{-- Visit markers --}}
                        <circle cx="300" cy="80" r="7" fill="#0d9488" stroke="white" stroke-width="2"/>
                        <circle cx="200" cy="80" r="6" fill="#0d9488" stroke="white" stroke-width="2" opacity=".7"/>
                        <circle cx="120" cy="80" r="6" fill="#0d9488" stroke="white" stroke-width="2" opacity=".6"/>
                        {{-- Active MR dot (Ramesh) --}}
                        <circle cx="460" cy="50" r="9" fill="#0d9488" stroke="white" stroke-width="2.5"/>
                        <circle cx="460" cy="50" r="16" fill="#0d9488" opacity="0.2"/>
                        {{-- Moving MR dot (Sunita) --}}
                        <circle cx="200" cy="80" r="8" fill="#3b82f6" stroke="white" stroke-width="2"/>
                        <circle cx="200" cy="80" r="14" fill="#3b82f6" opacity="0.15"/>
                        {{-- Alert MR dot --}}
                        <circle cx="120" cy="160" r="8" fill="#ef4444" stroke="white" stroke-width="2"/>
                    </svg>
                    <div class="absolute bottom-3 left-3 rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow backdrop-blur-sm">
                        📍 19 MRs on live map
                    </div>
                    <div class="absolute right-3 top-3 flex items-center gap-1.5 rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-rose-600 shadow backdrop-blur-sm">
                        <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-rose-500"></span> Live
                    </div>
                </div>

                {{-- Recent visit feed --}}
                <div class="border-t border-slate-100 bg-white px-5 py-3">
                    <p class="mb-2.5 text-xs font-semibold uppercase tracking-wider text-slate-500">Recent Activity</p>
                    <div class="space-y-2">
                        <div class="flex items-start gap-2 text-xs">
                            <span class="mt-px font-bold text-emerald-500">✓</span>
                            <p class="flex-1 text-slate-600"><span class="font-semibold text-slate-800">Sunita Rao</span> checked in at City Medicare Clinic · visit photo uploaded</p>
                            <span class="shrink-0 text-slate-400">2 min</span>
                        </div>
                        <div class="flex items-start gap-2 text-xs">
                            <span class="mt-px font-bold text-emerald-500">✓</span>
                            <p class="flex-1 text-slate-600"><span class="font-semibold text-slate-800">Ramesh Kumar</span> completed visit — Dr. Patel · 3 photos · notes added</p>
                            <span class="shrink-0 text-slate-400">9 min</span>
                        </div>
                        <div class="flex items-start gap-2 text-xs">
                            <span class="mt-px font-bold text-rose-500">⚠</span>
                            <p class="flex-1 text-rose-600"><span class="font-semibold">Vikram Nair</span> — check-in GPS is 3.2 km away from Dr. Sharma's clinic address</p>
                            <span class="shrink-0 text-slate-400">14 min</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3 trust bullets --}}
    <div class="mt-10 grid gap-6 text-center sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-3xl font-bold text-teal-600">Real-time</p>
            <p class="mt-2 text-sm text-slate-500">GPS updates every 10 seconds. See who moved, who stopped, and who went silent.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-3xl font-bold text-rose-600">Fake-proof</p>
            <p class="mt-2 text-sm text-slate-500">AI compares check-in GPS to the actual doctor address. Fake visits are flagged automatically.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-3xl font-bold text-slate-900">Zero calls</p>
            <p class="mt-2 text-sm text-slate-500">No WhatsApp, no calls. Open the dashboard and you know exactly what every MR did today.</p>
        </div>
    </div>

</section>

{{-- ═══════════════════════════════════════════════════════════
     PROOF OF VISIT
═══════════════════════════════════════════════════════════ --}}
<section class="mt-24">

    <div class="mb-12 text-center">
        <span class="inline-block rounded-full bg-violet-100 px-4 py-1 text-sm font-semibold text-violet-700">Proof of Visit</span>
        <h2 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">GPS alone is not enough.</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-500">MR Visits Track collects hard evidence for every visit — photo, location, time, and signature. No disputes.</p>
    </div>

    <div class="mx-auto grid max-w-5xl items-center gap-12 lg:grid-cols-2">

        {{-- Left: feature list --}}
        <div class="space-y-6">

            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Clinic Selfie / Visit Photo</h3>
                    <p class="mt-1 text-sm text-slate-500">MR must upload a photo at the clinic before check-in is accepted. No photo = no visit logged. Prevents desk-sitting fraud completely.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Geo-fenced Check-in</h3>
                    <p class="mt-1 text-sm text-slate-500">Check-in only works inside a 200m radius of the registered doctor address. Outside the zone — the app blocks it and flags the attempt.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Auto Timestamp</h3>
                    <p class="mt-1 text-sm text-slate-500">Every visit is stamped with check-in time, check-out time, and total duration — recorded server-side so it cannot be altered by the MR.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Doctor Signature <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Optional</span></h3>
                    <p class="mt-1 text-sm text-slate-500">Doctor can sign digitally on the MR's phone, confirming the meeting happened. Useful for compliance audits and distributor reporting.</p>
                </div>
            </div>
        </div>

        {{-- Right: phone mockup showing a visit card --}}
        <div class="flex justify-center">
            <div class="relative w-72">
                {{-- Phone shell --}}
                <div class="overflow-hidden rounded-[2.5rem] border-4 border-slate-800 bg-slate-800 shadow-2xl">
                    {{-- Notch --}}
                    <div class="flex justify-center bg-slate-800 pt-2 pb-1">
                        <div class="h-5 w-24 rounded-full bg-slate-900"></div>
                    </div>
                    {{-- Screen --}}
                    <div class="bg-slate-50 pb-6">

                        {{-- App header --}}
                        <div class="flex items-center gap-2 bg-teal-600 px-4 py-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <p class="flex-1 text-center text-sm font-semibold text-white">Visit Proof</p>
                        </div>

                        <div class="px-4 pt-4 space-y-3">

                            {{-- Doctor info --}}
                            <div class="rounded-xl bg-white p-3 shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">Dr</div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Dr. Anita Sharma</p>
                                        <p class="text-xs text-slate-500">City Medicare Clinic, Pune</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Photo proof --}}
                            <div class="relative overflow-hidden rounded-xl bg-slate-200 shadow-sm" style="height:110px">
                                {{-- Simulated photo background --}}
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="grid grid-cols-3 gap-0 w-full h-full">
                                        <div class="bg-slate-300"></div>
                                        <div class="bg-slate-200"></div>
                                        <div class="bg-slate-300"></div>
                                    </div>
                                </div>
                                {{-- Simulated door/clinic --}}
                                <div class="absolute inset-0 flex items-end justify-center pb-2">
                                    <div class="h-16 w-10 rounded-t-lg border-2 border-slate-400 bg-slate-100"></div>
                                </div>
                                {{-- Photo verified badge --}}
                                <div class="absolute right-2 top-2 flex items-center gap-1 rounded-full bg-emerald-500 px-2 py-0.5 shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-xs font-bold text-white">Photo</span>
                                </div>
                                {{-- Camera icon bottom left --}}
                                <div class="absolute bottom-2 left-2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- GPS + Time row --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-teal-50 p-2.5 shadow-sm">
                                    <p class="text-xs font-semibold text-teal-700">📍 Geo-fenced</p>
                                    <p class="mt-0.5 text-xs text-teal-600">Inside 200m zone</p>
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span class="text-xs text-emerald-600 font-medium">Verified</span>
                                    </div>
                                </div>
                                <div class="rounded-xl bg-blue-50 p-2.5 shadow-sm">
                                    <p class="text-xs font-semibold text-blue-700">🕐 Timestamp</p>
                                    <p class="mt-0.5 text-xs text-blue-600">Check-in 2:34 PM</p>
                                    <p class="text-xs text-blue-500">Check-out 2:51 PM</p>
                                </div>
                            </div>

                            {{-- Doctor signature --}}
                            <div class="rounded-xl bg-white p-3 shadow-sm">
                                <p class="text-xs font-semibold text-slate-500 mb-1.5">Doctor Signature</p>
                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-2">
                                    <svg viewBox="0 0 180 36" class="h-8 w-full" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10,28 C20,10 30,30 45,18 C55,10 60,26 75,20 C85,15 90,28 105,22 C118,17 125,26 140,20 C152,15 158,24 170,18"
                                            fill="none" stroke="#1e293b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="mt-1.5 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-xs text-emerald-600 font-medium">Signed by Dr. Anita Sharma</span>
                                </div>
                            </div>

                            {{-- Final status badge --}}
                            <div class="flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-bold text-white">Visit Verified</span>
                            </div>

                        </div>
                    </div>
                </div>
                {{-- Phone shadow glow --}}
                <div class="pointer-events-none absolute -bottom-4 left-1/2 h-10 w-48 -translate-x-1/2 rounded-full bg-teal-400/20 blur-xl"></div>
            </div>
        </div>
    </div>

</section>

{{-- ═══════════════════════════════════════════════════════════
     DAILY CALL REPORT (DCR)
═══════════════════════════════════════════════════════════ --}}
<section class="mt-24">

    <div class="mb-12 text-center">
        <span class="inline-block rounded-full bg-blue-100 px-4 py-1 text-sm font-semibold text-blue-700">Daily Call Report</span>
        <h2 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">DCR — automatically filled. Zero extra effort.</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-500">Every pharma company needs a DCR. MR Visits Track generates it automatically from each visit — no manual typing by the MR at end of day.</p>
    </div>

    <div class="mx-auto grid max-w-5xl items-start gap-12 lg:grid-cols-2">

        {{-- Left: DCR Report Mockup (desktop admin view) --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

            {{-- Report header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-4">
                <div>
                    <p class="text-sm font-bold text-slate-900">Daily Call Report</p>
                    <p class="text-xs text-slate-500">Ramesh Kumar &nbsp;·&nbsp; Monday, 9 June 2025</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Submitted ✓</span>
            </div>

            {{-- DCR table --}}
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60">
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Doctor</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Products Promoted</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Samples</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Follow-up</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">Dr. Anita Sharma</p>
                            <p class="text-xs text-slate-400">City Medicare · 2:34 PM</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">Cardivate 10mg</span>
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">OmegaPlus</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700">6 given</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">16 Jun</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">Dr. Ravi Mehta</p>
                            <p class="text-xs text-slate-400">Apollo Pharmacy · 11:15 AM</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">GastroRelief</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">None</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">23 Jun</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">Dr. Priya Nair</p>
                            <p class="text-xs text-slate-400">Lifeline Hospital · 4:10 PM</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">Cardivate 10mg</span>
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">NeuroCal</span>
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">OmegaPlus</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700">12 given</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">13 Jun</td>
                    </tr>
                </tbody>
            </table>

            {{-- Summary footer --}}
            <div class="grid grid-cols-4 divide-x divide-slate-100 border-t border-slate-100 bg-slate-50">
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-slate-900">8</p>
                    <p class="text-xs text-slate-500">Visits</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-violet-600">5</p>
                    <p class="text-xs text-slate-500">Products</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-teal-600">34</p>
                    <p class="text-xs text-slate-500">Samples</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-blue-600">6</p>
                    <p class="text-xs text-slate-500">Follow-ups</p>
                </div>
            </div>
        </div>

        {{-- Right: benefits --}}
        <div class="space-y-7">

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Doctor Visited</h3>
                    <p class="mt-1 text-sm text-slate-500">Full doctor profile — name, clinic, specialty, and visit history. Know which doctors were reached and how often.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Products Promoted</h3>
                    <p class="mt-1 text-sm text-slate-500">MR selects which products were discussed from your catalogue. Track which products each doctor is being pitched — and which are being ignored.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Samples Given</h3>
                    <p class="mt-1 text-sm text-slate-500">Record how many samples were distributed per visit. Track total sample consumption by MR, territory, and product — stop inventory leakage.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Follow-up Date</h3>
                    <p class="mt-1 text-sm text-slate-500">MR sets the next visit date per doctor. The app auto-reminds before the date — so no doctor is forgotten and visit frequency is maintained.</p>
                </div>
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-sm font-semibold text-blue-900">No more end-of-day typing</p>
                <p class="mt-1 text-sm text-slate-600">DCR is built as the MR visits — not filled later from memory. Every detail is accurate, timestamped, and ready to export as PDF or Excel.</p>
            </div>

        </div>
    </div>

</section>

{{-- ═══════════════════════════════════════════════════════════
     TOUR PLAN / ROUTE PLAN
═══════════════════════════════════════════════════════════ --}}
<section class="mt-24">

    <div class="mb-12 text-center">
        <span class="inline-block rounded-full bg-emerald-100 px-4 py-1 text-sm font-semibold text-emerald-700">Tour Plan &amp; Route Planning</span>
        <h2 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Plan the week. Track execution. Catch every miss.</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-500">MR submits a weekly tour plan in advance. Admin approves it. The app then tracks whether every planned doctor was actually visited.</p>
    </div>

    <div class="mx-auto grid max-w-5xl items-start gap-12 lg:grid-cols-2">

        {{-- Left: feature list --}}
        <div class="space-y-7">

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Doctor List per Day</h3>
                    <p class="mt-1 text-sm text-slate-500">MR plans which doctors to visit each day of the week. The app shows planned vs. actual visits side-by-side so nothing slips through.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Area-wise Route Grouping</h3>
                    <p class="mt-1 text-sm text-slate-500">Doctors are grouped by area so the MR travels efficiently. No more criss-crossing the city. Planned route vs. GPS-actual route is compared every evening.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Missed Doctor Reminders</h3>
                    <p class="mt-1 text-sm text-slate-500">If a planned doctor was not visited by end of day, the MR gets an in-app alert and the admin sees it marked red in the dashboard. Automatically. No follow-up call needed.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Plan vs. Actual Report</h3>
                    <p class="mt-1 text-sm text-slate-500">Weekly report shows what was planned vs. what was actually done — by MR, by area, by doctor frequency. Export to Excel for review meetings.</p>
                </div>
            </div>

        </div>

        {{-- Right: Tour plan UI mockup --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-4">
                <div>
                    <p class="text-sm font-bold text-slate-900">Weekly Tour Plan</p>
                    <p class="text-xs text-slate-500">Ramesh Kumar &nbsp;·&nbsp; Week of 9–13 June 2025</p>
                </div>
                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Admin Approved ✓</span>
            </div>

            {{-- Day rows --}}
            <div class="divide-y divide-slate-50">

                {{-- Monday --}}
                <div class="px-5 py-3.5">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Monday · Pune West</p>
                        <span class="text-xs text-emerald-600 font-semibold">4 / 4 done ✓</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-sm text-slate-700">Dr. Anita Sharma <span class="text-xs text-slate-400 ml-1">City Medicare</span></p>
                            <span class="ml-auto text-xs text-emerald-600">2:34 PM</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-sm text-slate-700">Dr. Ravi Mehta <span class="text-xs text-slate-400 ml-1">Apollo Clinic</span></p>
                            <span class="ml-auto text-xs text-emerald-600">11:20 AM</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-sm text-slate-700">Sunrise Pharmacy <span class="text-xs text-slate-400 ml-1">Chemist</span></p>
                            <span class="ml-auto text-xs text-emerald-600">4:05 PM</span>
                        </div>
                    </div>
                </div>

                {{-- Tuesday --}}
                <div class="px-5 py-3.5">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Tuesday · Pune East</p>
                        <span class="text-xs text-amber-600 font-semibold">3 / 4 done</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-sm text-slate-700">Dr. Priya Nair <span class="text-xs text-slate-400 ml-1">Lifeline Hospital</span></p>
                            <span class="ml-auto text-xs text-emerald-600">10:00 AM</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <p class="text-sm text-slate-700">Dr. Suresh Rao <span class="text-xs text-slate-400 ml-1">MedPlus Clinic</span></p>
                            <span class="ml-auto text-xs text-emerald-600">2:15 PM</span>
                        </div>
                        {{-- Missed --}}
                        <div class="flex items-center gap-2 rounded-lg bg-rose-50 px-2 py-1.5 -mx-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <p class="text-sm font-medium text-rose-700">Dr. Kavita Joshi <span class="text-xs font-normal ml-1">Narayana Hospital</span></p>
                            <span class="ml-auto text-xs font-semibold text-rose-600">Missed</span>
                        </div>
                    </div>
                </div>

                {{-- Wednesday --}}
                <div class="px-5 py-3.5 bg-slate-50/60">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Wednesday · Pimpri</p>
                        <span class="text-xs text-slate-400 font-medium">Upcoming</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 opacity-50">
                            <span class="h-3.5 w-3.5 shrink-0 rounded-full border-2 border-slate-300"></span>
                            <p class="text-sm text-slate-600">Dr. Aman Verma <span class="text-xs text-slate-400 ml-1">Care Hospital</span></p>
                        </div>
                        <div class="flex items-center gap-2 opacity-50">
                            <span class="h-3.5 w-3.5 shrink-0 rounded-full border-2 border-slate-300"></span>
                            <p class="text-sm text-slate-600">Wellness Pharmacy <span class="text-xs text-slate-400 ml-1">Chemist</span></p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Week summary footer --}}
            <div class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100 bg-emerald-50">
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-emerald-700">18</p>
                    <p class="text-xs text-slate-500">Planned</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-slate-900">15</p>
                    <p class="text-xs text-slate-500">Completed</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-rose-600">3</p>
                    <p class="text-xs text-slate-500">Missed</p>
                </div>
            </div>

        </div>
    </div>

</section>

{{-- ═══════════════════════════════════════════════════════════
     WHATSAPP REPORT TO MANAGER
═══════════════════════════════════════════════════════════ --}}
<section class="mt-24">

    <div class="mb-12 text-center">
        <span class="inline-block rounded-full bg-green-100 px-4 py-1 text-sm font-semibold text-green-700">WhatsApp Reporting</span>
        <h2 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Automated daily performance reports — delivered to management.</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-500">No login required. Every evening, your managers receive a complete team performance summary on WhatsApp — without opening any app or dashboard.</p>
    </div>

    <div class="mx-auto grid max-w-5xl items-center gap-12 lg:grid-cols-2">

        {{-- Left: WhatsApp phone mockup --}}
        <div class="flex justify-center">
            <div class="relative w-72">
                {{-- Phone shell --}}
                <div class="overflow-hidden rounded-[2.5rem] border-4 border-slate-800 bg-slate-800 shadow-2xl">
                    {{-- Notch --}}
                    <div class="flex justify-center bg-slate-800 pt-2 pb-1">
                        <div class="h-5 w-24 rounded-full bg-slate-900"></div>
                    </div>
                    {{-- Screen --}}
                    <div class="bg-[#ece5dd]">

                        {{-- WhatsApp header --}}
                        <div class="flex items-center gap-3 bg-[#128c7e] px-4 py-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20 text-sm font-bold text-white">MF</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-white">MR Visits Track Bot</p>
                                <p class="text-xs text-green-100">Daily Summary · Automated</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>

                        {{-- Chat area --}}
                        <div class="space-y-3 px-3 py-4 pb-5">

                            {{-- Date divider --}}
                            <div class="flex justify-center">
                                <span class="rounded-full bg-white/60 px-3 py-0.5 text-xs text-slate-500 shadow-sm">Today, 7:00 PM</span>
                            </div>

                            {{-- Incoming message --}}
                            <div class="flex justify-start">
                                <div class="max-w-[85%] rounded-2xl rounded-tl-sm bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-bold text-[#128c7e] mb-2">📊 Daily Field Summary</p>
                                    <p class="text-xs text-slate-700 leading-relaxed">
                                        <span class="font-semibold">Ravi Kumar</span> — ✅ 18 doctor visits completed<br>
                                        <span class="text-slate-500 text-[11px]">Pune West &amp; Pimpri zone</span>
                                    </p>
                                    <div class="mt-2 border-t border-slate-100 pt-2 space-y-1">
                                        <p class="text-[11px] text-slate-600">💊 Products promoted: <span class="font-medium">Cardivate, OmegaPlus</span></p>
                                        <p class="text-[11px] text-slate-600">🎁 Samples distributed: <span class="font-medium">42 units</span></p>
                                        <p class="text-[11px] text-slate-600">📍 Distance covered: <span class="font-medium">67 km</span></p>
                                        <p class="text-[11px] text-rose-600">⚠ Missed: <span class="font-medium">Dr. Kavita Joshi</span></p>
                                    </div>
                                    <div class="mt-2 flex items-center justify-end gap-1">
                                        <span class="text-[10px] text-slate-400">7:00 PM</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Second message - full team summary --}}
                            <div class="flex justify-start">
                                <div class="max-w-[85%] rounded-2xl rounded-tl-sm bg-white px-4 py-3 shadow-sm">
                                    <p class="text-xs font-bold text-[#128c7e] mb-2">👥 Full Team Summary</p>
                                    <div class="space-y-1.5 text-[11px] text-slate-700">
                                        <div class="flex justify-between">
                                            <span>Ravi Kumar</span>
                                            <span class="font-semibold text-emerald-600">18 visits ✓</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Sunita Rao</span>
                                            <span class="font-semibold text-emerald-600">14 visits ✓</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Ajay Singh</span>
                                            <span class="font-semibold text-amber-600">9 visits ⚠</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Vikram Nair</span>
                                            <span class="font-semibold text-rose-600">6 visits 🚨</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                                        Total today: <span class="font-semibold text-slate-800">124 visits · 19 MRs active</span>
                                    </div>
                                    <div class="mt-1 flex items-center justify-end gap-1">
                                        <span class="text-[10px] text-slate-400">7:00 PM</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 -ml-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Manager reply --}}
                            <div class="flex justify-end">
                                <div class="max-w-[70%] rounded-2xl rounded-tr-sm bg-[#dcf8c6] px-3 py-2 shadow-sm">
                                    <p class="text-xs text-slate-800">👍 Good. Follow up with Vikram tomorrow.</p>
                                    <div class="mt-1 flex items-center justify-end gap-1">
                                        <span class="text-[10px] text-slate-400">7:02 PM</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 -ml-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- WhatsApp input bar --}}
                        <div class="flex items-center gap-2 bg-[#f0f0f0] px-3 py-2">
                            <div class="flex-1 rounded-full bg-white px-4 py-2 text-xs text-slate-400">Type a message</div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#128c7e]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                            </div>
                        </div>

                    </div>
                </div>
                {{-- Glow --}}
                <div class="pointer-events-none absolute -bottom-4 left-1/2 h-10 w-48 -translate-x-1/2 rounded-full bg-green-400/20 blur-xl"></div>
            </div>
        </div>

        {{-- Right: feature list --}}
        <div class="space-y-7">

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Automatic — every evening at 7 PM</h3>
                    <p class="mt-1 text-sm text-slate-500">No manual sending. System auto-generates the day's summary and pushes it via WhatsApp at a scheduled time. Manager gets it without opening any app.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Per-MR breakdown + full team total</h3>
                    <p class="mt-1 text-sm text-slate-500">Each MR's visits, samples, products promoted, and missed doctors — plus a team total so the manager sees the full picture in one message.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Alerts for underperformers</h3>
                    <p class="mt-1 text-sm text-slate-500">MRs with low visit count or fake-visit flags are highlighted in the message. Manager knows exactly who to follow up with — before the next morning.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Configurable — time, format, recipients</h3>
                    <p class="mt-1 text-sm text-slate-500">Set the send time, choose what's included, and add multiple recipients — Area Manager, RSM, or company owner. Every level of management stays informed.</p>
                </div>
            </div>

            {{-- Callout --}}
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-sm font-semibold text-green-900">No app needed for managers</p>
                <p class="mt-1 text-sm text-slate-600">Senior managers and owners don't need to log in anywhere. The report comes to them on WhatsApp — the app they already use all day.</p>
            </div>

        </div>
    </div>

</section>

{{-- ═══════════════════════════════════════════════════════════
     FAKE GPS DETECTION
═══════════════════════════════════════════════════════════ --}}
<section class="mt-24">

    <div class="mb-12 text-center">
        <span class="inline-block rounded-full bg-rose-100 px-4 py-1 text-sm font-semibold text-rose-700">Fake GPS Detection</span>
        <h2 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Your MR is "at the clinic." <span class="text-rose-600">But is he really?</span></h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-500">Fake GPS apps, mock locations, and VPNs — MR Visits Track detects all of them automatically. Every fraud attempt is logged and reported to admin instantly.</p>
    </div>

    <div class="mx-auto grid max-w-5xl items-start gap-12 lg:grid-cols-2">

        {{-- Left: Detection alert dashboard mockup --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-rose-100 bg-rose-50 px-5 py-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-rose-900">Fraud Detection Alerts</p>
                        <p class="text-xs text-rose-500">Today · 4 incidents flagged</p>
                    </div>
                </div>
                <span class="animate-pulse rounded-full bg-rose-500 px-3 py-1 text-xs font-bold text-white">Live</span>
            </div>

            {{-- Alert 1: Mock location app --}}
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-rose-800">Mock Location App Detected</p>
                            <span class="shrink-0 text-xs text-slate-400">2:41 PM</span>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-600"><span class="font-medium">Vikram Nair</span> — Device is running a GPS spoofing app. Check-in at Dr. Mehta blocked automatically.</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700">Mock GPS app running</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">Check-in blocked</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alert 2: Fast movement --}}
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-amber-800">Impossible Speed Detected</p>
                            <span class="shrink-0 text-xs text-slate-400">11:08 AM</span>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-600"><span class="font-medium">Ajay Singh</span> — Location jumped 18 km in 40 seconds. Physically impossible movement flagged.</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">1620 km/h detected</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">GPS teleport</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alert 3: Same location fraud --}}
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-orange-800">Same Location — 5 Different Doctors</p>
                            <span class="shrink-0 text-xs text-slate-400">10:15 AM</span>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-600"><span class="font-medium">Rajan Pillai</span> — All 5 check-ins today from the same GPS coordinates. Likely sitting at home or office.</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-700">Coords match 100%</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">5 visits suspicious</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary strip --}}
            <div class="grid grid-cols-3 divide-x divide-slate-100 bg-slate-50">
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-rose-600">1</p>
                    <p class="text-xs text-slate-500">Mock GPS</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-amber-600">1</p>
                    <p class="text-xs text-slate-500">Speed Fraud</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-lg font-bold text-orange-600">2</p>
                    <p class="text-xs text-slate-500">Location Clone</p>
                </div>
            </div>
        </div>

        {{-- Right: Feature explanations --}}
        <div class="space-y-7">

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Mock Location App Detection</h3>
                    <p class="mt-1 text-sm text-slate-500">The app checks if Android's "Mock Location" developer setting is active. If a GPS spoofer like Fake GPS, Hola, or Floater is running — check-in is blocked immediately and admin is notified.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Impossible Speed Detection</h3>
                    <p class="mt-1 text-sm text-slate-500">If the GPS pings show an MR teleporting — 15 km in 30 seconds — the system flags it as physically impossible movement. Common trick when MRs manually enter coordinates or switch fake locations.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Same-Location Clone Fraud</h3>
                    <p class="mt-1 text-sm text-slate-500">When multiple doctor visits are logged from identical GPS coordinates, the system raises a fraud flag. This catches MRs who log 8 visits from their home or car without moving at all.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Geo-fence Address Mismatch</h3>
                    <p class="mt-1 text-sm text-slate-500">Every doctor has a registered address. Check-in GPS is compared against it. More than 200m away — flagged. MR cannot check in to Dr. Patel while standing outside a shopping mall.</p>
                </div>
            </div>

            {{-- Callout --}}
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <p class="text-sm font-semibold text-rose-900">Fraud blocked — not just reported</p>
                <p class="mt-1 text-sm text-slate-600">When mock location is detected, the check-in is blocked before it happens — not flagged after. The MR cannot submit a fake visit even if he tries.</p>
            </div>

        </div>
    </div>

</section>

{{-- visit type strip --}}
<section class="mt-16">
    <div class="mx-auto max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="grid divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="flex items-start gap-4 p-6">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Doctor Visits</h3>
                    <p class="mt-1 text-sm text-slate-500">GPS check-in/out, visit time, notes &amp; photo proof</p>
                </div>
            </div>
            <div class="flex items-start gap-4 p-6">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Chemist Visits</h3>
                    <p class="mt-1 text-sm text-slate-500">Track retail pharmacy calls across daily routes</p>
                </div>
            </div>
            <div class="flex items-start gap-4 p-6">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900">Hospital Visits</h3>
                    <p class="mt-1 text-sm text-slate-500">Institution-level visit logging &amp; compliance records</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     FREE TRIAL BANNER
═══════════════════════════════════════════════════════════ --}}
<section class="mt-24">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 to-teal-800 px-8 py-12 text-center shadow-2xl">
        {{-- Background decoration --}}
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute -bottom-12 -left-12 h-48 w-48 rounded-full bg-white/5"></div>

        <p class="text-xs font-bold uppercase tracking-widest text-teal-300">Get started today</p>
        <h2 class="mt-4 text-3xl font-extrabold text-white sm:text-4xl">
            Full Platform Access — 7 Days, No Commitment
        </h2>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-teal-100">
            Evaluate the complete platform with your own team — Live GPS tracking, Proof of Visit, DCR, Tour Plan, WhatsApp Reports, and Fraud Detection. Every feature. No restrictions.
        </p>

        {{-- What's included --}}
        <div class="mx-auto mt-8 grid max-w-2xl gap-3 sm:grid-cols-2 text-left">
            <div class="flex items-center gap-2 text-sm text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Full team access — all MRs
            </div>
            <div class="flex items-center gap-2 text-sm text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Live GPS tracking &amp; route map
            </div>
            <div class="flex items-center gap-2 text-sm text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Proof of visit + doctor signature
            </div>
            <div class="flex items-center gap-2 text-sm text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Automatic DCR generation
            </div>
            <div class="flex items-center gap-2 text-sm text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                WhatsApp daily summary reports
            </div>
            <div class="flex items-center gap-2 text-sm text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Fake GPS &amp; fraud detection
            </div>
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('companies.register') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-bold text-teal-700 shadow-lg hover:bg-teal-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Start Free 7-Day Demo
            </a>
        </div>
        <p class="mt-3 text-sm text-teal-200">No credit card &nbsp;·&nbsp; No commitment &nbsp;·&nbsp; Cancel anytime &nbsp;·&nbsp; Setup in 2 minutes</p>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     FINAL CTA
═══════════════════════════════════════════════════════════ --}}
<section class="mb-4 mt-16">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="grid items-center gap-8 px-8 py-10 lg:grid-cols-2 lg:gap-0">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-teal-600">Ready to get started?</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Transform your pharma field force management.</h2>
                <p class="mt-3 text-slate-500">Get full platform access for 7 days at no cost. Evaluate every feature with your own team before committing to a plan.</p>
                <ul class="mt-5 space-y-2 text-sm text-slate-600">
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        No credit card — no commitment required
                    </li>
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        All features unlocked during the trial
                    </li>
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Dedicated onboarding support included
                    </li>
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Setup complete in under 2 minutes
                    </li>
                </ul>
            </div>
            <div class="flex flex-col items-center gap-4 text-center lg:border-l lg:border-slate-100 lg:pl-8">
                <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-50 text-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-extrabold text-slate-900">7 Days Free</p>
                    <p class="mt-1 text-slate-500">Full access · No card needed</p>
                </div>
                <a href="{{ route('companies.register') }}"
                    class="mt-2 inline-flex w-full max-w-xs items-center justify-center gap-2 rounded-xl bg-teal-600 px-8 py-4 text-base font-bold text-white shadow-md transition-colors hover:bg-teal-700">
                    Start Free Demo
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-teal-600">
                    Already registered? Login →
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

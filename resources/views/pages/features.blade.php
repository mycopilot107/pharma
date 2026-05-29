@extends('layouts.app')

@section('title', 'Features — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-4xl">

    {{-- Hero --}}
    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">Features</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">Everything your field team needs</h1>
        <p class="mt-3 text-lg text-slate-600 max-w-2xl mx-auto">
            From GPS check-ins to AI reports — {{ config('pharma.app_name') }} covers the entire MR management workflow for pharma companies of any size.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('companies.register') }}" class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition-colors">
                Start free &rarr;
            </a>
            <a href="{{ route('pricing') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                View pricing
            </a>
        </div>
    </div>

    {{-- Feature 1: GPS & Field Tracking --}}
    <div class="mt-20">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-xl">📍</div>
            <h2 class="text-2xl font-bold text-slate-900">GPS & Field Tracking</h2>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Real-time GPS clock-in / clock-out</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Field reps clock in via the mobile app to start GPS tracking. The exact latitude and longitude are captured at check-in, throughout the day, and at clock-out — giving you a full route timeline.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Geofence auto check-in</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    When a rep walks within 150 metres of a registered customer, the app can automatically trigger a visit check-in. Reduces manual tapping and ensures visit records are accurate and timely.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Live route map for admins</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Company admins can view each rep's live position on a map and replay the full route for any past day. Know exactly where your team was, for how long, and in what order.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Fraud & fake GPS detection</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    The platform flags suspicious patterns — same GPS coordinates for multiple visits, location jumps between consecutive pings, or visits with unusually short durations. Admins review these in the fraud-alert dashboard.
                </p>
            </div>
        </div>
    </div>

    {{-- Feature 2: MR Management --}}
    <div class="mt-16">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-xl">👥</div>
            <h2 class="text-2xl font-bold text-slate-900">MR Management</h2>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Attendance & daily schedule</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Each representative clocks in to start their day and clocks out to end it. Admins see attendance status across the whole team in real time from the admin dashboard.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Leave management</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Reps submit leave requests (casual, sick, annual, emergency) from the app. Admins approve or reject with a single tap. Leave balances and history are tracked automatically.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Sales targets</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Set monthly or quarterly targets per rep or per product. Both the admin and the rep can track target progress in real time. Targets are displayed prominently on the rep's dashboard.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Daily route planning</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Reps plan their daily visit route inside the mobile app. Admins can review planned routes versus actual routes covered to measure adherence and identify gaps.
                </p>
            </div>
        </div>
    </div>

    {{-- Feature 3: Customer CRM --}}
    <div class="mt-16">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-xl">🏥</div>
            <h2 class="text-2xl font-bold text-slate-900">Customer & CRM</h2>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Doctors, chemists & hospitals</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Maintain a centralised database of all customers — doctors, retail chemists, and hospitals — with contact details, specialisation, clinic address, and GPS coordinates for geofencing.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Visit notes & photos</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Reps capture visit notes and photos during each call. All notes are stored against the customer record and are visible to the admin, building a full visit history over time.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Follow-ups & CRM workflow</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Create follow-up tasks against any customer. Once completed, the follow-up is marked done with a timestamp. This ensures no customer interaction falls through the cracks.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Prescription & purchase records</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Log which products a doctor is prescribing and what a chemist is purchasing. This prescription and purchase data feeds directly into AI-generated performance reports.
                </p>
            </div>
        </div>
    </div>

    {{-- Feature 4: Expenses & Orders --}}
    <div class="mt-16">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-xl">💰</div>
            <h2 class="text-2xl font-bold text-slate-900">Expenses & Orders</h2>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Expense submission</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Reps submit field expenses (travel, meals, accommodation) from the app with receipt photos. Admins review, approve, or reject each claim with comments — all within the platform.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Product catalogue</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Admins maintain a product catalogue with names, categories, and pricing. Reps can browse the catalogue when logging prescriptions or booking orders from the field.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Field order booking</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Reps book orders from customers directly inside the app during a visit. Admins confirm, dispatch, or cancel orders from the web dashboard with a full order history per customer.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Expense & order reports</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Dedicated report screens let admins view all expense claims and order summaries — filtered by rep, date range, or status — so finance teams have everything in one place.
                </p>
            </div>
        </div>
    </div>

    {{-- Feature 5: AI Reports --}}
    <div class="mt-16">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-xl">🤖</div>
            <h2 class="text-2xl font-bold text-slate-900">AI-Powered Reporting</h2>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">AI visit summaries</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    After each visit, reps can generate an AI summary that consolidates notes, duration, and customer context into a clean, readable paragraph. Saves time and improves documentation quality.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">AI-generated rep reports</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Admins can generate AI reports for any rep over a custom date range. The report includes a narrative summary of performance, visit patterns, top customers, and recommendations.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Performance insights</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    The platform surfaces key metrics — visits per day, average visit duration, customer coverage rate, target achievement — in a clean admin dashboard with chart visualisations.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Rep-side AI reports</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Reps can also generate their own AI reports from the mobile app to review their own performance, see which customers they haven't visited recently, and plan better routes.
                </p>
            </div>
        </div>
    </div>

    {{-- Feature 6: Mobile App --}}
    <div class="mt-16">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-xl">📱</div>
            <h2 class="text-2xl font-bold text-slate-900">Mobile App for Field Reps</h2>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <h3 class="font-semibold text-slate-800">Android & iOS (Flutter)</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        The MedRep Fleet mobile app is built with Flutter for native performance on both Android and iOS. Download the Android APK directly or install from the app store.
                    </p>
                    <a href="{{ route('app.download') }}" class="mt-4 inline-block rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700 transition-colors">
                        Download App &rarr;
                    </a>
                </div>
                <ul class="space-y-3 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0 mt-0.5">✓</span> Background GPS with battery optimisation</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0 mt-0.5">✓</span> Camera integration for visit photos & receipts</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0 mt-0.5">✓</span> Push notifications for approvals & alerts</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0 mt-0.5">✓</span> Works reliably on intermittent mobile data</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0 mt-0.5">✓</span> Secure token-based authentication (Sanctum)</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0 mt-0.5">✓</span> Full CRM, expense & order management</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="mt-16 rounded-2xl bg-slate-900 p-10 text-center text-white">
        <h2 class="text-2xl font-bold">Try all features — free</h2>
        <p class="mt-2 text-slate-300">Register your company in minutes. 1 user, no credit card, no time limit.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-4">
            <a href="{{ route('companies.register') }}"
                class="rounded-lg bg-teal-500 px-6 py-3 font-semibold text-white hover:bg-teal-400 transition-colors">
                Start free &rarr;
            </a>
            <a href="{{ route('pricing') }}"
                class="rounded-lg border border-slate-600 px-6 py-3 font-semibold text-slate-200 hover:border-slate-400 hover:text-white transition-colors">
                View pricing
            </a>
        </div>
    </div>

</div>
@endsection

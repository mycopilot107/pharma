@extends('layouts.app')

@section('title', 'About Us — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-3xl">

    {{-- Hero --}}
    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">About Us</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">Built for India's Pharma Field Force</h1>
        <p class="mt-4 text-lg text-slate-600 leading-relaxed">
            {{ config('pharma.app_name') }} is a SaaS platform that helps pharmaceutical companies manage, track, and empower their medical representative teams — from first visit to final report.
        </p>
    </div>

    {{-- Mission --}}
    <div class="mt-12 rounded-2xl border border-teal-100 bg-teal-50 p-8">
        <h2 class="text-xl font-semibold text-teal-800">Our Mission</h2>
        <p class="mt-3 text-slate-700 leading-relaxed">
            To give every pharma company — whether a 1-person agency or a 100-rep national team — the tools to run a transparent, data-driven field force without complex enterprise software or high costs.
        </p>
    </div>

    {{-- What we do --}}
    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-slate-800">What We Do</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="text-2xl">📍</div>
                <h3 class="mt-3 font-semibold text-slate-800">GPS Visit Tracking</h3>
                <p class="mt-1 text-sm text-slate-600">Real-time GPS check-in and check-out for every doctor, chemist, and hospital visit. Geofence detection ensures accuracy.</p>
            </div>
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="text-2xl">📊</div>
                <h3 class="mt-3 font-semibold text-slate-800">AI-Powered Reports</h3>
                <p class="mt-1 text-sm text-slate-600">Automated visit summaries, performance insights, and AI-generated reports to help managers make faster decisions.</p>
            </div>
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="text-2xl">💼</div>
                <h3 class="mt-3 font-semibold text-slate-800">Complete MR Management</h3>
                <p class="mt-1 text-sm text-slate-600">Manage leaves, expenses, orders, targets, and daily routes all in one place — for both admins and field reps.</p>
            </div>
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="text-2xl">📱</div>
                <h3 class="mt-3 font-semibold text-slate-800">Mobile-First App</h3>
                <p class="mt-1 text-sm text-slate-600">Flutter-based Android and iOS app built for field representatives. Works smoothly even with intermittent connectivity.</p>
            </div>
        </div>
    </div>

    {{-- Who we serve --}}
    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-slate-800">Who We Serve</h2>
        <ul class="mt-4 space-y-3 text-slate-600">
            <li class="flex items-start gap-3">
                <span class="mt-0.5 text-teal-500">✓</span>
                <span><strong class="text-slate-800">Pharmaceutical Companies</strong> — Track your entire field force with live GPS and attendance.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="mt-0.5 text-teal-500">✓</span>
                <span><strong class="text-slate-800">Medical Representatives</strong> — Log visits, manage customers, submit expenses and orders from your phone.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="mt-0.5 text-teal-500">✓</span>
                <span><strong class="text-slate-800">Distributors & Agencies</strong> — Manage multi-rep teams under a single company account.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="mt-0.5 text-teal-500">✓</span>
                <span><strong class="text-slate-800">Sales Managers</strong> — Monitor targets, route plans, and daily performance with live dashboards.</span>
            </li>
        </ul>
    </div>

    {{-- CTA --}}
    <div class="mt-12 rounded-2xl bg-slate-900 p-8 text-center text-white">
        <h2 class="text-xl font-semibold">Ready to get started?</h2>
        <p class="mt-2 text-sm text-slate-300">Register your company in minutes. Free plan available — no credit card required.</p>
        <a href="{{ route('companies.register') }}" class="mt-5 inline-block rounded-lg bg-teal-500 px-6 py-2.5 font-semibold text-white hover:bg-teal-400 transition-colors">
            Register Your Company
        </a>
    </div>

</div>
@endsection

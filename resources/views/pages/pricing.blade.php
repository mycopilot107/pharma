@extends('layouts.app')

@section('title', 'Pricing — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-5xl">

    {{-- Hero --}}
    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">Pricing</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">Simple, transparent pricing</h1>
        <p class="mt-3 text-slate-600">One price per user. All features included. Start free — no credit card required.</p>
    </div>

    {{-- Plan Cards --}}
    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($plans as $plan)
        <div class="relative flex flex-col rounded-2xl border bg-white p-7 shadow-sm transition hover:shadow-md
            {{ $plan->isFree() ? 'border-teal-400 ring-2 ring-teal-400' : 'border-slate-200' }}">

            @if ($plan->isFree())
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-teal-500 px-3 py-0.5 text-xs font-semibold text-white">
                    Free forever
                </span>
            @endif

            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-600">
                    {{ $plan->user_limit }} {{ $plan->user_limit === 1 ? 'User' : 'Users' }}
                </p>
                <p class="mt-3 text-4xl font-bold text-slate-900">{{ $plan->formattedPrice() }}</p>
                @if ($plan->isFree())
                    <p class="mt-1 text-sm text-slate-500">forever &middot; no credit card</p>
                @else
                    <p class="mt-1 text-sm text-slate-500">per month &middot; ${{ config('pharma.price_per_user_usd', 3) }}/user</p>
                @endif
                <p class="mt-4 text-sm text-slate-600">{{ $plan->description }}</p>
            </div>

            <a href="{{ route('companies.register', ['plan' => $plan->id]) }}"
                class="mt-7 inline-block w-full rounded-lg py-2.5 text-center text-sm font-semibold transition-colors
                    {{ $plan->isFree()
                        ? 'bg-teal-600 text-white hover:bg-teal-700'
                        : 'bg-teal-50 text-teal-700 hover:bg-teal-100' }}">
                {{ $plan->isFree() ? 'Start for free' : 'Get started' }} &rarr;
            </a>
        </div>
        @endforeach
    </div>

    <p class="mt-5 text-center text-sm text-slate-500">
        Need more than 50 users?
        <a href="{{ route('contact') }}" class="text-teal-600 hover:underline">Contact us</a> for a custom plan.
    </p>

    {{-- What's included --}}
    <div class="mt-16">
        <h2 class="text-center text-2xl font-semibold text-slate-800">Everything included in every plan</h2>
        <p class="mt-2 text-center text-sm text-slate-500">No feature tiers. All plans get the full platform.</p>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-lg">📍</span>
                    <p class="font-semibold text-slate-800">GPS Visit Tracking</p>
                </div>
                <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Real-time GPS clock-in &amp; clock-out</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Geofence auto check-in (150 m radius)</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Live route map &amp; route history</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Fraud / fake GPS detection</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-lg">👥</span>
                    <p class="font-semibold text-slate-800">MR Management</p>
                </div>
                <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Attendance &amp; daily clock-in/out</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Leave requests &amp; approval workflow</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Sales targets &amp; progress tracking</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Daily route planning</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-lg">🏥</span>
                    <p class="font-semibold text-slate-800">Customer CRM</p>
                </div>
                <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Doctors, chemists &amp; hospitals</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Visit notes, photos &amp; follow-ups</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Prescription &amp; purchase history</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Customer GPS coordinates</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-lg">🤖</span>
                    <p class="font-semibold text-slate-800">AI-Powered Reports</p>
                </div>
                <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> AI visit summaries per rep</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Performance insights &amp; trends</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> AI-generated team reports</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Admin &amp; MR report views</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-lg">💰</span>
                    <p class="font-semibold text-slate-800">Expenses &amp; Orders</p>
                </div>
                <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Expense submission with receipts</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Admin approve / reject workflow</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Product catalogue management</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Field order booking &amp; tracking</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-lg">📱</span>
                    <p class="font-semibold text-slate-800">Mobile App</p>
                </div>
                <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Android &amp; iOS (Flutter)</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Background GPS tracking</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Works with intermittent connectivity</li>
                    <li class="flex gap-2"><span class="text-teal-500 shrink-0">✓</span> Push notifications</li>
                </ul>
            </div>

        </div>
    </div>

    {{-- Feature comparison table --}}
    <div class="mt-16 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="py-4 pl-6 pr-3 text-left font-semibold text-slate-700 w-1/2">Feature</th>
                    <th class="px-4 py-4 text-center font-semibold text-slate-700">Free</th>
                    <th class="px-4 py-4 text-center font-semibold text-teal-700 bg-teal-50">Paid Plans</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                $rows = [
                    ['GPS visit tracking',              true, true],
                    ['Geofence auto check-in',          true, true],
                    ['Live route map',                  true, true],
                    ['Fake GPS fraud detection',        true, true],
                    ['Attendance clock-in / clock-out', true, true],
                    ['Leave management',                true, true],
                    ['Expense management',              true, true],
                    ['Product catalogue & orders',      true, true],
                    ['Customer CRM',                    true, true],
                    ['AI visit summaries',              true, true],
                    ['AI-generated reports',            true, true],
                    ['Daily route planner',             true, true],
                    ['Target management',               true, true],
                    ['Android & iOS mobile app',        true, true],
                    ['Push notifications',              true, true],
                    ['Admin web dashboard',             true, true],
                    ['Number of MR users',              '1 user', 'Up to plan limit'],
                ];
                @endphp
                @foreach ($rows as $row)
                <tr>
                    <td class="py-3 pl-6 pr-3 text-slate-700">{{ $row[0] }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($row[1] === true)
                            <span class="text-teal-500 text-base">✓</span>
                        @else
                            <span class="text-slate-500 text-xs">{{ $row[1] }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center bg-teal-50/40">
                        @if ($row[2] === true)
                            <span class="text-teal-500 text-base">✓</span>
                        @else
                            <span class="text-teal-700 text-xs font-medium">{{ $row[2] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- FAQ --}}
    <div class="mt-16">
        <h2 class="text-center text-2xl font-semibold text-slate-800">Billing questions</h2>
        <div class="mt-8 space-y-3 max-w-2xl mx-auto">

            <details class="group rounded-xl border border-slate-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                    Can I upgrade or change my plan?
                    <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
                </summary>
                <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                    Yes. Register with the plan that matches your current team size. When your team grows, contact us to switch to a larger plan. Your existing data is always preserved.
                </div>
            </details>

            <details class="group rounded-xl border border-slate-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                    What happens when my subscription expires?
                    <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
                </summary>
                <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                    Your account is suspended until the subscription is renewed. All your data is retained for 30 days after expiry. After that, the account and data are permanently deleted unless renewed.
                </div>
            </details>

            <details class="group rounded-xl border border-slate-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                    What payment methods are accepted?
                    <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
                </summary>
                <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                    We accept all major credit/debit cards, UPI, net banking, and wallets via Razorpay — India's leading payment gateway. Your card details are never stored on our servers.
                </div>
            </details>

            <details class="group rounded-xl border border-slate-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                    Is there a refund policy?
                    <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
                </summary>
                <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                    We do not offer refunds for partial months or unused periods. We recommend starting with the Free plan to evaluate the platform before purchasing a paid plan.
                </div>
            </details>

        </div>
    </div>

    {{-- CTA --}}
    <div class="mt-16 rounded-2xl bg-slate-900 p-10 text-center text-white">
        <h2 class="text-2xl font-bold">Ready to track your field force?</h2>
        <p class="mt-2 text-slate-300">Start with the free plan — no credit card, no commitment.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-4">
            <a href="{{ route('companies.register') }}"
                class="rounded-lg bg-teal-500 px-6 py-3 font-semibold text-white hover:bg-teal-400 transition-colors">
                Register Your Company &rarr;
            </a>
            <a href="{{ route('contact') }}"
                class="rounded-lg border border-slate-600 px-6 py-3 font-semibold text-slate-200 hover:border-slate-400 hover:text-white transition-colors">
                Talk to Us
            </a>
        </div>
    </div>

</div>
@endsection

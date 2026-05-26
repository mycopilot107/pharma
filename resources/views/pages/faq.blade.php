@extends('layouts.app')

@section('title', 'FAQ — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">FAQ</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">Frequently Asked Questions</h1>
        <p class="mt-3 text-slate-600">Everything you need to know about {{ config('pharma.app_name') }}.</p>
    </div>

    <div class="mt-12 space-y-4">

        {{-- Section: Getting Started --}}
        <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Getting Started</h2>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                How do I register my company?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                Go to <a href="{{ route('companies.register') }}" class="text-teal-600 hover:underline">Register Company</a>, fill in your company details, choose a plan, and complete the setup. If you pick the Free plan, your account is activated instantly — no payment required.
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                Is there a free plan?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                Yes! We offer a free plan that supports 1 medical representative. It includes GPS tracking, visit logging, customer management, and all core features — forever, with no credit card required.
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                How do I add medical representatives to my account?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                After logging in as Company Admin, go to <strong>Team → Add User</strong>. Enter the MR's name, email, and password. They can then log in via the web portal or the mobile app using those credentials.
            </div>
        </details>

        {{-- Section: Mobile App --}}
        <h2 class="mt-6 text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Mobile App</h2>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                Is there an Android and iOS app for field reps?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                Yes. The MedRep Fleet mobile app is available for Android (APK download) and iOS. It supports GPS tracking, visit check-in/out, customer management, expense submission, order booking, and notifications — all from the phone.
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                Does GPS tracking work in the background?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                Yes. The app sends GPS pings every 30 seconds when active and every 60 seconds when running in the background. Location tracking requires the representative to clock in for the day. Tracking stops automatically after clock-out.
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                What permissions does the app need?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                The app requires: Location (foreground & background for GPS tracking), Camera (for visit photos and expense receipts), and Internet. All permissions are requested at the appropriate time with a clear explanation of why they are needed.
            </div>
        </details>

        {{-- Section: Visits & Tracking --}}
        <h2 class="mt-6 text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Visits & Tracking</h2>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                How does geofence auto check-in work?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                When a representative enters within 150 metres of a customer's registered location, the app can automatically trigger a check-in. This reduces manual effort and ensures accurate visit records. Admins can configure the geofence radius per customer.
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                Can admins detect fake GPS or location spoofing?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                Yes. The platform includes a fraud detection system that flags suspicious patterns — such as repeated check-ins from the same GPS coordinates, visits with unusually short durations, or large location jumps between pings. Admins can review fraud alerts in the tracking dashboard.
            </div>
        </details>

        {{-- Section: Billing --}}
        <h2 class="mt-6 text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Billing & Plans</h2>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                How is pricing calculated?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                Pricing is <strong>${{ config('pharma.price_per_user_usd', 3) }} per user per month</strong>. Choose a plan based on how many medical representatives you have. All plans include the same features — only the user limit differs.
            </div>
        </details>

        <details class="group rounded-xl border border-slate-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium text-slate-800 hover:text-teal-700">
                What payment methods are accepted?
                <span class="ml-4 shrink-0 text-slate-400 group-open:rotate-180 transition-transform">&#9660;</span>
            </summary>
            <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-600 leading-relaxed">
                We accept all major credit/debit cards, UPI, net banking, and wallets via Razorpay — a secure Indian payment gateway. Your payment details are never stored on our servers.
            </div>
        </details>

    </div>

    {{-- Still have questions --}}
    <div class="mt-10 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
        <p class="font-medium text-slate-800">Still have questions?</p>
        <p class="mt-1 text-sm text-slate-600">Reach out to us and we'll get back to you within 24 hours.</p>
        <a href="{{ route('contact') }}" class="mt-4 inline-block rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700 transition-colors">
            Contact Us
        </a>
    </div>

</div>
@endsection

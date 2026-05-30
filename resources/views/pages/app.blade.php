@extends('layouts.app')

@section('title', 'Download App — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-3xl">

    {{-- Hero --}}
    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">Mobile App</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">MedRep Fleet — Field Rep App</h1>
        <p class="mt-3 text-slate-600 max-w-xl mx-auto">
            The mobile app for medical representatives. GPS tracking, visit management, expenses, orders, and AI reports — all from your phone.
        </p>
    </div>

    {{-- Download buttons --}}
    <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">

        {{-- Android --}}
        <a href="{{ asset('downloads/medrep-fleet.apk') }}" download="medrep-fleet.apk"
            class="flex w-full max-w-xs items-center gap-4 rounded-2xl border-2 border-teal-600 bg-teal-600 px-6 py-4 text-white shadow hover:bg-teal-700 hover:border-teal-700 transition-colors">
            <div class="text-3xl leading-none">🤖</div>
            <div>
                <p class="text-xs font-medium opacity-80 uppercase tracking-wide">Download for</p>
                <p class="text-lg font-bold leading-tight">Android APK</p>
            </div>
        </a>

        {{-- iOS --}}
        <div class="flex w-full max-w-xs items-center gap-4 rounded-2xl border-2 border-slate-300 bg-white px-6 py-4 text-slate-700 shadow opacity-70 cursor-not-allowed select-none">
            <div class="text-3xl leading-none">🍎</div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Coming soon</p>
                <p class="text-lg font-bold leading-tight">iOS App Store</p>
            </div>
        </div>

    </div>

    {{-- Android APK instructions --}}
    <div id="android-download" class="mt-12 rounded-2xl border border-teal-200 bg-teal-50 p-7">
        <h2 class="font-semibold text-teal-800 text-lg">Android APK — Install Guide</h2>
        <p class="mt-2 text-sm text-teal-700">The app is distributed as a direct APK download. Follow these steps to install it on your Android device:</p>
        <ol class="mt-4 space-y-3 text-sm text-teal-900">
            <li class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">1</span>
                <span>Tap the <strong>Download Android APK</strong> button above — the APK file will download directly to your phone.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">2</span>
                <span>Open your phone's <strong>Settings → Apps → Special app access → Install unknown apps</strong> and allow installation from your browser or file manager.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">3</span>
                <span>Open the downloaded APK file from your notifications or file manager and tap <strong>Install</strong>.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">4</span>
                <span>Open the MedRep Fleet app, enter your <strong>email and password</strong> provided by your Company Admin, and start tracking.</span>
            </li>
        </ol>
        <p class="mt-4 text-xs text-teal-700">
            Minimum Android version: <strong>Android 6.0 (API 23)</strong> or higher. GPS and Camera permissions are required.
        </p>
    </div>

    {{-- App features --}}
    <div class="mt-12">
        <h2 class="text-xl font-semibold text-slate-800 text-center">What you can do in the app</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">

            <div class="flex gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-2xl shrink-0">📍</div>
                <div>
                    <p class="font-semibold text-slate-800">Clock in & GPS tracking</p>
                    <p class="mt-1 text-sm text-slate-600">Start your day with a GPS clock-in. The app tracks your location in the background and sends pings to the server throughout your work day.</p>
                </div>
            </div>

            <div class="flex gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-2xl shrink-0">👨‍⚕️</div>
                <div>
                    <p class="font-semibold text-slate-800">Visit check-in / check-out</p>
                    <p class="mt-1 text-sm text-slate-600">Log every visit to doctors, chemists, and hospitals. Add notes and photos during the visit. Geofence auto-triggers check-in when you arrive nearby.</p>
                </div>
            </div>

            <div class="flex gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-2xl shrink-0">🧾</div>
                <div>
                    <p class="font-semibold text-slate-800">Expenses & receipts</p>
                    <p class="mt-1 text-sm text-slate-600">Submit travel, meal, and other field expenses with a photo of your receipt. Track the status of each claim — pending, approved, or rejected.</p>
                </div>
            </div>

            <div class="flex gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-2xl shrink-0">📦</div>
                <div>
                    <p class="font-semibold text-slate-800">Order booking</p>
                    <p class="mt-1 text-sm text-slate-600">Book orders from customers during your visit. Select products from the catalogue, set quantities, and submit for admin confirmation — all without a paper form.</p>
                </div>
            </div>

            <div class="flex gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-2xl shrink-0">🏖️</div>
                <div>
                    <p class="font-semibold text-slate-800">Leave requests</p>
                    <p class="mt-1 text-sm text-slate-600">Apply for casual, sick, annual, or emergency leave from the app. View your leave balance, pending requests, and approval status in real time.</p>
                </div>
            </div>

            <div class="flex gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-2xl shrink-0">🤖</div>
                <div>
                    <p class="font-semibold text-slate-800">AI performance reports</p>
                    <p class="mt-1 text-sm text-slate-600">Generate an AI summary of your own performance over any date range. Get insight into your visit patterns, top customers, and areas to improve.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Permissions info --}}
    <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="font-semibold text-slate-800">App permissions explained</h3>
        <div class="mt-4 grid gap-3 sm:grid-cols-3 text-sm">
            <div class="rounded-lg bg-slate-50 px-4 py-3">
                <p class="font-medium text-slate-700">📍 Location</p>
                <p class="mt-1 text-slate-500 text-xs">Required for GPS tracking during work sessions. Background location is used to send pings while the app is minimised.</p>
            </div>
            <div class="rounded-lg bg-slate-50 px-4 py-3">
                <p class="font-medium text-slate-700">📷 Camera</p>
                <p class="mt-1 text-slate-500 text-xs">Used to take photos during customer visits and to photograph expense receipts for submission.</p>
            </div>
            <div class="rounded-lg bg-slate-50 px-4 py-3">
                <p class="font-medium text-slate-700">🔔 Notifications</p>
                <p class="mt-1 text-slate-500 text-xs">Push notifications for leave approvals, expense status updates, and new orders or tasks from your admin.</p>
            </div>
        </div>
    </div>

    {{-- Not a rep? --}}
    <div class="mt-10 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
        <p class="font-medium text-slate-800">Are you a Company Admin?</p>
        <p class="mt-1 text-sm text-slate-600">Admins manage the team and view reports via the web portal — no app download needed.</p>
        <div class="mt-4 flex flex-wrap justify-center gap-3">
            <a href="{{ route('login') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700 transition-colors">
                Admin Login &rarr;
            </a>
            <a href="{{ route('companies.register') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-white transition-colors">
                Register Company
            </a>
        </div>
    </div>

</div>
@endsection

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('pharma.app_name'))</title>
    <meta name="description" content="@yield('meta_description', 'MR Visits Track — Medical Representative tracking app with GPS, visit proof, daily call reports and fake GPS detection for pharma companies in India.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    {{-- Open Graph --}}
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="@yield('canonical', url()->current())">
    <meta property="og:title"       content="@yield('og_title', config('pharma.app_name'))">
    <meta property="og:description" content="@yield('meta_description', 'Track every MR visit with GPS, photo proof, DCR and WhatsApp reports.')">
    <meta property="og:site_name"   content="{{ config('pharma.app_name') }}">
    <meta property="og:image"       content="{{ url('assets/images/icon-512.png') }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    {{-- Favicons --}}
    <link rel="icon"             type="image/x-icon"  href="{{ url('favicon.ico') }}">
    <link rel="icon"             type="image/png"     sizes="32x32" href="{{ url('favicon-32x32.png') }}">
    <link rel="icon"             type="image/png"     sizes="16x16" href="{{ url('favicon-16x16.png') }}">
    <link rel="apple-touch-icon"                      sizes="180x180" href="{{ url('apple-touch-icon.png') }}">
    <link rel="icon"             type="image/png"     sizes="192x192" href="{{ url('assets/images/icon-192.png') }}">
    @stack('schema')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased flex flex-col">
    <header class="relative z-50 border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ auth()->check() && auth()->user()->role === \App\Enums\UserRole::SuperAdmin ? route('super-admin.dashboard') : (auth()->check() && auth()->user()->role === \App\Enums\UserRole::CompanyAdmin ? route('dashboard') : route('home')) }}"
                class="shrink-0 flex items-center">
                <img src="{{ url('assets/images/logo-nav.png') }}"
                     alt="{{ config('pharma.app_name') }}"
                     height="36"
                     class="h-9 w-auto">
            </a>

            @guest
            <nav class="hidden md:flex items-center gap-1 text-sm">
                <a href="{{ route('features') }}" class="px-3 py-1.5 text-slate-600 hover:text-teal-700 hover:bg-slate-50 rounded-lg transition-colors">Features</a>
                <a href="{{ route('pricing') }}"  class="px-3 py-1.5 text-slate-600 hover:text-teal-700 hover:bg-slate-50 rounded-lg transition-colors">Pricing</a>
                <a href="{{ route('app.download') }}" class="px-3 py-1.5 text-slate-600 hover:text-teal-700 hover:bg-slate-50 rounded-lg transition-colors">App</a>
                <a href="{{ route('about') }}"    class="px-3 py-1.5 text-slate-600 hover:text-teal-700 hover:bg-slate-50 rounded-lg transition-colors">About</a>
            </nav>
            @endguest

            <div class="flex items-center gap-2 text-sm shrink-0">
                @auth
                    @include('partials.notification-bell')
                    @if (auth()->user()->role === \App\Enums\UserRole::Representative)
                        <a href="{{ route('mr.dashboard') }}" class="hidden rounded-lg bg-teal-600 px-3 py-2 font-medium text-white hover:bg-teal-700 sm:inline-flex">
                            MR App
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-slate-100 px-3 py-2 text-slate-700 hover:bg-slate-200">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100">Login</a>
                    <a href="{{ route('companies.register') }}" class="rounded-lg bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700">Register</a>
                @endauth
            </div>
        </div>

        @auth
            @if (auth()->user()->role === \App\Enums\UserRole::SuperAdmin)
                @include('partials.super-admin-nav')
            @elseif (auth()->user()->role === \App\Enums\UserRole::CompanyAdmin)
                @include('partials.admin-nav')
            @endif
        @endauth
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 flex-1">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
    @include('partials.footer')
    @stack('scripts')
</body>
</html>

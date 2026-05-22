@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold text-slate-900">Sign in</h1>
    <p class="mt-2 text-sm text-slate-600">Company admins manage teams &amp; visits. Medical representatives log field visits here.</p>

    <form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
            <input type="password" name="password" id="password" required
                class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-600">
            Remember me
        </label>
        <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700">Sign in</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-600">
        New company? <a href="{{ route('companies.register') }}" class="font-medium text-teal-700 hover:underline">Register here</a>
    </p>
</div>
@endsection

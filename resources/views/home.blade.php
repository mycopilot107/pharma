@extends('layouts.app')

@section('title', 'Medical Representative Management')

@section('content')
<div class="text-center">
    <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
        Pharma MR Management with AI &amp; Tracking
    </h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
        For pharma companies, medical representatives, sales teams, distributors &amp; agencies — register, manage your team, and track every field visit with GPS.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('companies.register') }}" class="rounded-xl bg-teal-600 px-6 py-3 font-semibold text-white shadow hover:bg-teal-700">
            Start Company Registration
        </a>
        <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 hover:bg-slate-50">
            Login (Admin / MR)
        </a>
    </div>
</div>

<section class="mt-16">
    <h2 class="mb-6 text-center text-2xl font-semibold text-slate-800">MR visit tracking</h2>
    <div class="mx-auto grid max-w-4xl gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-2xl">👨‍⚕️</p>
            <h3 class="mt-2 font-semibold">Doctor visits</h3>
            <p class="mt-1 text-sm text-slate-600">GPS check-in/out, visit time, notes &amp; photos</p>
        </div>
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-2xl">💊</p>
            <h3 class="mt-2 font-semibold">Chemist visits</h3>
            <p class="mt-1 text-sm text-slate-600">Track retail pharmacy calls on daily routes</p>
        </div>
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-2xl">🏥</p>
            <h3 class="mt-2 font-semibold">Hospital visits</h3>
            <p class="mt-1 text-sm text-slate-600">Institution-level visit logging &amp; compliance</p>
        </div>
    </div>
</section>

<section class="mt-16">
    <h2 class="mb-6 text-center text-2xl font-semibold text-slate-800">Choose your team size</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($plans as $plan)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-teal-300 hover:shadow-md {{ $plan->isFree() ? 'ring-2 ring-teal-500' : '' }}">
                @if ($plan->isFree())
                    <span class="rounded-full bg-teal-100 px-2 py-0.5 text-xs font-semibold text-teal-800">Popular starter</span>
                @endif
                <p class="mt-2 text-sm font-medium uppercase tracking-wide text-teal-600">{{ $plan->user_limit }} {{ $plan->user_limit === 1 ? 'User' : 'Users' }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $plan->formattedPrice() }}</p>
                @if ($plan->isFree())
                    <p class="mt-1 text-sm text-slate-500">forever · no credit card</p>
                @else
                    <p class="mt-1 text-sm text-slate-500">per month · {{ format_money(config('pharma.price_per_user_usd', 3)) }}/user</p>
                @endif
                <p class="mt-4 text-sm text-slate-600">{{ $plan->description }}</p>
                <a href="{{ route('companies.register', ['plan' => $plan->id]) }}" class="mt-6 inline-block w-full rounded-lg py-2 text-center text-sm font-semibold {{ $plan->isFree() ? 'bg-teal-600 text-white hover:bg-teal-700' : 'bg-teal-50 text-teal-700 hover:bg-teal-100' }}">
                    {{ $plan->isFree() ? 'Start free' : 'Select plan' }}
                </a>
            </div>
        @endforeach
    </div>
</section>
@endsection

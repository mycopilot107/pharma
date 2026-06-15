@extends('layouts.mr')

@section('title', 'Tour Plans')

@section('mr-content')
<div class="flex items-start justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Tour Plans</h1>
        <p class="mt-0.5 text-sm text-slate-500">Plan your weekly field visits, get approved, track execution.</p>
    </div>
    <a href="{{ route('mr.tour-plans.create') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Plan
    </a>
</div>

@if (session('success'))
    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<div class="mt-6 space-y-3">
    @forelse ($plans as $plan)
        <a href="{{ route('mr.tour-plans.show', $plan) }}"
            class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-teal-300 transition-colors">
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-slate-900">{{ $plan->weekLabel() }}</p>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $plan->statusBadgeClass() }}">
                            {{ $plan->statusLabel() }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $plan->stops_count }} stop{{ $plan->stops_count !== 1 ? 's' : '' }} planned</p>
                    @if ($plan->rejection_reason)
                        <p class="mt-1 text-xs text-red-600">Rejection: {{ Str::limit($plan->rejection_reason, 80) }}</p>
                    @endif
                </div>
                <svg class="h-5 w-5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 py-16 text-center">
            <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-500">No tour plans yet</p>
            <p class="mt-1 text-xs text-slate-400">Create your first weekly plan to get started.</p>
            <a href="{{ route('mr.tour-plans.create') }}"
                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                Create Plan
            </a>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $plans->links() }}</div>
@endsection

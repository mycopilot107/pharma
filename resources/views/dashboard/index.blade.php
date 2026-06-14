@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- Page header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Admin Dashboard</p>
        <h1 class="mt-0.5 text-2xl font-bold text-slate-900">{{ $company->name }}</h1>
    </div>
    @if ($company->canAddRepresentative())
        <a href="{{ route('users.create') }}"
            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add MR
        </a>
    @else
        <span class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-500">
            Plan limit reached &mdash; {{ $company->user_limit }} users
        </span>
    @endif
</div>

{{-- Quick stats --}}
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Total MRs</p>
        <p class="mt-1.5 text-3xl font-bold text-slate-900">{{ $usedSlots }}</p>
        <p class="mt-1 text-xs text-slate-400">of {{ $company->user_limit }} plan seats</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Plan</p>
        <p class="mt-1.5 text-xl font-bold text-slate-900">{{ $plan->user_limit }} users</p>
        <p class="mt-1 text-xs text-teal-600 font-medium">{{ $plan->formattedPrice() }}/month</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Subscription expires</p>
        <p class="mt-1.5 text-lg font-bold text-slate-900">
            {{ $company->subscription_ends_at?->format('d M Y') ?? 'No expiry' }}
        </p>
        @if ($company->subscription_ends_at)
            <p class="mt-1 text-xs {{ $company->subscription_ends_at->diffInDays(now()) < 30 ? 'text-rose-500 font-medium' : 'text-slate-400' }}">
                {{ $company->subscription_ends_at->diffForHumans() }}
            </p>
        @endif
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Currency</p>
        <p class="mt-1.5 text-xl font-bold text-slate-900">{{ currency_symbol() }} {{ currency_code() }}</p>
        <p class="mt-1 text-xs text-slate-400">Used across all reports</p>
    </div>
</div>

@include('partials.notifications-widget', ['reminders' => $reminders ?? collect(), 'notificationsRoute' => $notificationsRoute ?? route('admin.notifications.index')])

{{-- Module grid --}}
<div class="mt-8">
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Field operations</h2>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

        <a href="{{ route('admin.tracking.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-emerald-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-emerald-700">Live Tracking</p>
                <p class="mt-0.5 text-xs text-slate-500">GPS · Routes · Attendance</p>
            </div>
        </a>

        <a href="{{ route('admin.visits.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-teal-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600 transition group-hover:bg-teal-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-teal-700">MR Visits</p>
                <p class="mt-0.5 text-xs text-slate-500">Doctor, chemist &amp; hospital</p>
            </div>
        </a>

        <a href="{{ route('admin.customers.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-violet-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600 transition group-hover:bg-violet-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-violet-700">Customers (CRM)</p>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ ($totalCustomers ?? 0) > 0 ? $totalCustomers . ' active' : 'Doctors, hospitals, chemists' }}
                </p>
            </div>
        </a>

        <a href="{{ route('admin.targets.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-indigo-700">Targets</p>
                <p class="mt-0.5 text-xs text-slate-500">Sales &amp; performance goals</p>
            </div>
        </a>
    </div>
</div>

<div class="mt-4">
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Finance &amp; HR</h2>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

        <a href="{{ route('admin.expenses.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-orange-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-50 text-orange-600 transition group-hover:bg-orange-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-orange-700">Expenses</p>
                <p class="mt-0.5 text-xs text-slate-500">Fuel, hotel &amp; food claims</p>
            </div>
        </a>

        <a href="{{ route('admin.orders.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-green-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50 text-green-600 transition group-hover:bg-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-green-700">Orders</p>
                <p class="mt-0.5 text-xs text-slate-500">Field orders from MRs</p>
            </div>
        </a>

        <a href="{{ route('admin.leaves.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-rose-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 transition group-hover:bg-rose-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-rose-700">Leave</p>
                <p class="mt-0.5 text-xs text-slate-500">Approve leave requests</p>
            </div>
        </a>

        <a href="{{ route('admin.ai-reports.index') }}"
            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-purple-300 hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-50 text-purple-600 transition group-hover:bg-purple-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 group-hover:text-purple-700">AI Reports</p>
                <p class="mt-0.5 text-xs text-slate-500">Daily insights &amp; predictions</p>
            </div>
        </a>
    </div>
</div>

{{-- Target performance --}}
<section class="mt-8">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">Target performance</h2>
        <a href="{{ route('admin.targets.index') }}" class="text-xs font-medium text-teal-600 hover:underline">Manage →</a>
    </div>
    @include('partials.target-stats-cards', ['targetStats' => $targetStats])
</section>

{{-- MR table --}}
<section class="mt-8">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">Medical representatives</h2>
        <a href="{{ route('users.index') }}" class="text-xs font-medium text-teal-600 hover:underline">Manage all →</a>
    </div>

    @if ($representatives->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-3 text-sm text-slate-500">No representatives yet.</p>
            @if ($company->canAddRepresentative())
                <a href="{{ route('users.create') }}" class="mt-2 inline-block text-sm font-medium text-teal-600 hover:underline">Add your first MR →</a>
            @endif
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($representatives->take(5) as $user)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $user->name }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $user->email }}</td>
                            <td class="px-5 py-3.5">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('users.show', $user) }}" class="text-xs font-medium text-teal-600 hover:underline">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($representatives->count() > 5)
            <p class="mt-2 text-center text-xs text-slate-400">
                Showing 5 of {{ $usedSlots }} —
                <a href="{{ route('users.index') }}" class="text-teal-600 hover:underline">view full list</a>
            </p>
        @endif
    @endif
</section>

@endsection

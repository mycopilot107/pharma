@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $company->name }}</h1>
        <p class="text-slate-600">Medical Representative Management</p>
    </div>
    @if ($company->canAddRepresentative())
        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">
            + Add MR
        </a>
    @else
        <span class="rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600">Plan limit reached ({{ $company->user_limit }} users)</span>
    @endif
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('admin.tracking.index') }}" class="flex items-center justify-between rounded-2xl border-2 border-emerald-400 bg-emerald-50 p-5 hover:border-emerald-500 shadow-sm">
        <div>
            <p class="font-semibold text-emerald-900">⭐ Real-time tracking</p>
            <p class="text-sm text-emerald-700">Live GPS · Routes · Attendance · Visits</p>
        </div>
        <span class="text-emerald-600">→</span>
    </a>
    <a href="{{ route('admin.customers.index') }}" class="flex items-center justify-between rounded-2xl border border-violet-200 bg-violet-50 p-5 hover:border-violet-400">
        <div>
            <p class="font-semibold text-violet-900">Customer Management (CRM)</p>
            <p class="text-sm text-violet-700">Doctors, hospitals, clinics, chemists & distributors</p>
        </div>
        <span class="text-violet-600">→</span>
    </a>
    <a href="{{ route('admin.visits.index') }}" class="flex items-center justify-between rounded-2xl border border-teal-200 bg-teal-50 p-5 hover:border-teal-400">
        <div>
            <p class="font-semibold text-teal-900">MR Visit Tracking</p>
            <p class="text-sm text-teal-700">Doctor, chemist & hospital visits with GPS</p>
        </div>
        <span class="text-teal-600">→</span>
    </a>
    <a href="{{ route('admin.ai-reports.index') }}" class="flex items-center justify-between rounded-2xl border border-violet-200 bg-violet-50 p-5 hover:border-violet-400">
        <div>
            <p class="font-semibold text-violet-900">AI reporting</p>
            <p class="text-sm text-violet-700">Daily reports, doctor engagement & sales predictions</p>
        </div>
        <span class="text-violet-600">→</span>
    </a>
    <a href="{{ route('admin.notifications.index') }}" class="flex items-center justify-between rounded-2xl border border-rose-200 bg-rose-50 p-5 hover:border-rose-300">
        <div>
            <p class="font-semibold text-rose-900">Reminders & alerts</p>
            <p class="text-sm text-rose-700">Follow-ups, targets, meetings & doctor revisits</p>
        </div>
        <span class="text-rose-600">→</span>
    </a>
    <a href="{{ route('admin.expenses.index') }}" class="flex items-center justify-between rounded-2xl border border-orange-200 bg-orange-50 p-5 hover:border-orange-400">
        <div>
            <p class="font-semibold text-orange-900">Expense management</p>
            <p class="text-sm text-orange-700">Fuel, hotel & food bills — approve or reject</p>
        </div>
        <span class="text-orange-600">→</span>
    </a>
    <a href="{{ route('admin.targets.index') }}" class="flex items-center justify-between rounded-2xl border border-indigo-200 bg-indigo-50 p-5 hover:border-indigo-400">
        <div>
            <p class="font-semibold text-indigo-900">Target Management</p>
            <p class="text-sm text-indigo-700">Monthly, product, sales & area targets</p>
        </div>
        <span class="text-indigo-600">→</span>
    </a>
</div>

@include('partials.notifications-widget', ['reminders' => $reminders ?? collect(), 'notificationsRoute' => $notificationsRoute ?? route('admin.notifications.index')])

@if (($totalCustomers ?? 0) > 0)
<section class="mt-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-800">CRM overview</h2>
        <a href="{{ route('admin.customers.index') }}" class="text-sm text-violet-700 hover:underline">Manage customers →</a>
    </div>
    <p class="text-sm text-slate-600">{{ $totalCustomers }} active customers across your territory</p>
</section>
@endif

<section class="mt-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-800">Target performance</h2>
        <a href="{{ route('admin.targets.index') }}" class="text-sm text-indigo-700 hover:underline">Manage targets →</a>
    </div>
    @include('partials.target-stats-cards', ['targetStats' => $targetStats])
</section>

<div class="mt-8 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Plan</p>
        <p class="mt-1 text-xl font-bold">{{ $plan->user_limit }} users</p>
        <p class="text-sm text-teal-600">{{ $plan->formattedPrice() }}/month</p>
        <p class="mt-1 text-xs text-slate-500">Currency: {{ currency_symbol() }} ({{ currency_code() }})</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Representatives used</p>
        <p class="mt-1 text-xl font-bold">{{ $usedSlots }} / {{ $company->user_limit }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Subscription valid until</p>
        <p class="mt-1 text-lg font-bold">{{ $company->subscription_ends_at?->format('d M Y') ?? '—' }}</p>
    </div>
</div>

<section class="mt-10">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-800">Medical representatives</h2>
        <a href="{{ route('users.index') }}" class="text-sm text-teal-700 hover:underline">Manage all →</a>
    </div>
    @if ($representatives->isEmpty())
        <p class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
            No representatives yet.
            @if ($company->canAddRepresentative())
                <a href="{{ route('users.create') }}" class="text-teal-700 hover:underline">Add your first MR</a>
            @endif
        </p>
    @else
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($representatives->take(5) as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @if ($user->is_active)
                                    <span class="text-emerald-600">Active</span>
                                @else
                                    <span class="text-slate-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('users.show', $user) }}" class="text-teal-700 hover:underline">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($representatives->count() > 5)
            <p class="mt-2 text-center text-sm text-slate-500">
                Showing 5 of {{ $usedSlots }} —
                <a href="{{ route('users.index') }}" class="text-teal-700 hover:underline">view full list</a>
            </p>
        @endif
    @endif
</section>
@endsection

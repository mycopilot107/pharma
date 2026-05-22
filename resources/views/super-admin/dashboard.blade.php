@extends('layouts.app')

@section('title', 'Super Admin')

@section('content')
<div>
    <h1 class="text-2xl font-bold text-slate-900">Platform overview</h1>
    <p class="text-slate-600">All registered companies and subscription plans</p>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Total companies</p>
        <p class="text-2xl font-bold">{{ $stats['companies_total'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Active</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $stats['companies_active'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Pending</p>
        <p class="text-2xl font-bold text-amber-600">{{ $stats['companies_pending'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Inactive</p>
        <p class="text-2xl font-bold text-slate-600">{{ $stats['companies_inactive'] }}</p>
    </div>
</div>
<p class="mt-2 text-sm text-slate-500">{{ $stats['representatives'] }} medical representatives across all companies</p>

<div class="mt-8 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Recent companies</h2>
    <a href="{{ route('super-admin.companies.index') }}" class="text-sm text-violet-700 hover:underline">View all →</a>
</div>

<div class="mt-4 overflow-hidden rounded-xl border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Company</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Plan</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">MRs</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Subscription</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($companies as $company)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $company->name }}</p>
                        <p class="text-xs text-slate-500">{{ $company->email }}</p>
                    </td>
                    <td class="px-4 py-3">
                        {{ $company->plan?->name ?? '—' }}
                        <span class="text-xs text-slate-500">({{ $company->user_limit }} users)</span>
                    </td>
                    <td class="px-4 py-3">{{ $company->reps_count }} / {{ $company->user_limit }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $company->accessBadgeClass() }}">{{ $company->accessLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $company->subscription_ends_at?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('super-admin.companies.show', $company) }}" class="text-violet-700 hover:underline">Manage</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No companies registered yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $companies->links() }}</div>
@endsection

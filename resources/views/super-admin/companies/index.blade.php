@extends('layouts.app')

@section('title', 'Companies')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Companies</h1>
        <p class="text-slate-600">All tenants, plans, and subscription status</p>
    </div>
    <a href="{{ route('super-admin.dashboard') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Overview</a>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Name or email"
        class="min-w-[12rem] flex-1 rounded-lg border px-3 py-2 text-sm">
    <select name="access" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All companies</option>
        <option value="active" @selected(request('access') === 'active')>Active only</option>
        <option value="inactive" @selected(request('access') === 'inactive')>Inactive only</option>
        <option value="pending" @selected(request('access') === 'pending')>Pending only</option>
    </select>
    <select name="plan_id" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All plans</option>
        @foreach ($plans as $plan)
            <option value="{{ $plan->id }}" @selected(request('plan_id') == $plan->id)>{{ $plan->name }} ({{ $plan->user_limit }} users)</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-violet-700 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-xl border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Company</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Plan</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Price</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Team</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Currency</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Access</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Ends</th>
                <th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($companies as $company)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $company->name }}</p>
                        <p class="text-xs text-slate-500">{{ $company->email }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $company->plan?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $company->plan?->formattedPrice($company->currency) ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $company->reps_count }} MR · {{ $company->admins_count }} admin</td>
                    <td class="px-4 py-3">{{ strtoupper($company->currency ?? 'USD') }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $company->accessBadgeClass() }}">
                            {{ $company->accessLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $company->subscription_ends_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            @if ($company->isEnabled())
                                <form method="POST" action="{{ route('super-admin.companies.deactivate', $company) }}" class="inline"
                                    onsubmit="return confirm('Deactivate {{ $company->name }}? All users will be blocked from login.');">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50">Deactivate</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('super-admin.companies.activate', $company) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50">Activate</button>
                                </form>
                            @endif
                            <a href="{{ route('super-admin.companies.show', $company) }}" class="text-violet-700 hover:underline text-xs">Details</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No companies match your filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $companies->links() }}</div>
@endsection

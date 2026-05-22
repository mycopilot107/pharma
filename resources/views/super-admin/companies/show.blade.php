@extends('layouts.app')

@section('title', $company->name)

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">{{ $company->name }}</h1>
        <p class="text-slate-600">{{ $company->email }} · Registered {{ $company->created_at->format('d M Y') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($company->isEnabled())
            <form method="POST" action="{{ route('super-admin.companies.deactivate', $company) }}"
                onsubmit="return confirm('Deactivate this company? All users will be blocked from login.');">
                @csrf
                <button type="submit" class="rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-medium text-red-800 hover:bg-red-100">Set inactive</button>
            </form>
        @else
            <form method="POST" action="{{ route('super-admin.companies.activate', $company) }}">
                @csrf
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Set active</button>
            </form>
        @endif
        <a href="{{ route('super-admin.companies.index') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← All companies</a>
    </div>
</div>

<div class="mt-4">
    <span class="rounded-full px-3 py-1 text-sm font-medium {{ $company->accessBadgeClass() }}">{{ $company->accessLabel() }}</span>
    @if ($company->isEnabled() && ! $company->isActive())
        <span class="ml-2 text-sm text-orange-700">Subscription expired — extend end date below or users cannot sign in.</span>
    @endif
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="rounded-xl border bg-white p-5 shadow-sm lg:col-span-2">
        <h2 class="font-semibold">Company details</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Phone</dt><dd class="font-medium">{{ $company->phone ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Currency</dt><dd class="font-medium">{{ strtoupper($company->currency ?? 'USD') }}</dd></div>
            <div><dt class="text-slate-500">Current plan</dt><dd class="font-medium">{{ $company->plan?->name }} — {{ $company->plan?->formattedPrice($company->currency) }}</dd></div>
            <div><dt class="text-slate-500">Paid (USD)</dt><dd class="font-medium">${{ number_format((float) $company->amount_paid_usd, 2) }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-slate-500">Address</dt><dd class="font-medium">{{ $company->address ?? '—' }}</dd></div>
        </dl>
    </div>

    <form method="POST" action="{{ route('super-admin.companies.update', $company) }}" class="rounded-xl border bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')
        <h2 class="font-semibold">Manage subscription</h2>
        <div class="mt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700" for="plan_id">Plan</label>
                <select name="plan_id" id="plan_id" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected($company->plan_id === $plan->id)>
                            {{ $plan->name }} — {{ $plan->formattedPrice() }} ({{ $plan->user_limit }} users)
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700" for="user_limit">User limit</label>
                <input type="number" name="user_limit" id="user_limit" value="{{ old('user_limit', $company->user_limit) }}" min="1" max="500" required
                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700" for="status">Account access</label>
                <select name="status" id="status" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
                    @foreach (\App\Models\Company::statusOptions() as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $company->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Active = users can log in. Inactive = blocked. Pending = awaiting payment.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700" for="subscription_ends_at">Subscription ends</label>
                <input type="date" name="subscription_ends_at" id="subscription_ends_at"
                    value="{{ old('subscription_ends_at', $company->subscription_ends_at?->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full rounded-lg bg-violet-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-violet-800">Save changes</button>
        </div>
    </form>
</div>

<div class="mt-8 overflow-hidden rounded-xl border bg-white shadow-sm">
    <div class="border-b px-4 py-3">
        <h2 class="font-semibold">Users ({{ $company->users->count() }})</h2>
    </div>
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Name</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Email</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Role</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Active</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($company->users as $user)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">{{ $user->role->label() }}</td>
                    <td class="px-4 py-3">{{ $user->is_active ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

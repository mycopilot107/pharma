@extends('layouts.app')

@section('title', 'Company Settings')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Company Settings</h1>
    <p class="mt-1 text-sm text-slate-500">Manage your company preferences and configuration.</p>
</div>

<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Operating currency --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">Operating Currency</h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    This currency is used across all reports, expenses, orders, and invoices.
                </p>
            </div>
            <div class="p-6">
                <label class="block text-sm font-medium text-slate-700" for="currency">
                    Currency <span class="text-red-500">*</span>
                </label>
                <select name="currency" id="currency" required
                    class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                    @foreach ($currencies as $code => $meta)
                        <option value="{{ $code }}"
                            @selected(old('currency', $company->currency ?? 'USD') === $code)>
                            {{ $meta['symbol'] }} — {{ $code }} ({{ $meta['name'] }})
                        </option>
                    @endforeach
                </select>
                @error('currency')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-slate-400">
                    Changing the currency affects how amounts are displayed. Existing data values are not converted.
                </p>
            </div>
        </div>

        {{-- Company info (read-only) --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">Company Information</h2>
                <p class="mt-0.5 text-xs text-slate-500">Contact support to update your company name or email.</p>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-6 py-4">
                    <span class="text-sm text-slate-500">Company name</span>
                    <span class="text-sm font-medium text-slate-900">{{ $company->name }}</span>
                </div>
                <div class="flex items-center justify-between px-6 py-4">
                    <span class="text-sm text-slate-500">Email</span>
                    <span class="text-sm font-medium text-slate-900">{{ $company->email }}</span>
                </div>
                <div class="flex items-center justify-between px-6 py-4">
                    <span class="text-sm text-slate-500">Plan</span>
                    <span class="text-sm font-medium text-slate-900">{{ $company->plan->user_limit }} MRs</span>
                </div>
                <div class="flex items-center justify-between px-6 py-4">
                    <span class="text-sm text-slate-500">Status</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $company->accessBadgeClass() }}">
                        {{ $company->accessLabel() }}
                    </span>
                </div>
                @if ($company->subscription_ends_at)
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-sm text-slate-500">Subscription expires</span>
                        <span class="text-sm font-medium text-slate-900">
                            {{ $company->subscription_ends_at->format('d M Y') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit"
                class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')

{{-- Header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <nav class="flex items-center gap-1 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-teal-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-600">Customers</span>
        </nav>
        <h1 class="mt-1 text-xl font-bold text-slate-900">Customers (CRM)</h1>
        <p class="mt-0.5 text-sm text-slate-500">Doctors, hospitals, clinics, chemists &amp; distributors</p>
    </div>
    <a href="{{ route('admin.customers.create') }}"
        class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add customer
    </a>
</div>

{{-- Customer type stats --}}
<div class="mt-6 grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
    @foreach ($customerTypes as $type)
        <a href="{{ route('admin.customers.index', ['type' => $type->value]) }}"
            class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-teal-300 hover:shadow-md
                {{ request('type') === $type->value ? 'border-teal-400 ring-2 ring-teal-400' : '' }}">
            <p class="text-xs font-medium text-slate-500">{{ $type->icon() }} {{ $type->label() }}</p>
            <p class="mt-1.5 text-2xl font-bold text-slate-900">{{ $counts[$type->value] ?? 0 }}</p>
        </a>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-1 min-w-[200px] flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Search</label>
            <input type="search" name="search" value="{{ request('search') }}"
                placeholder="Name, phone, city…"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Type</label>
            <select name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All types</option>
                @foreach ($customerTypes as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All statuses</option>
                <option value="active"   @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit"
            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700">
            Apply
        </button>
        @if (request()->hasAny(['search','type','status']))
            <a href="{{ route('admin.customers.index') }}"
                class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                Clear
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead>
            <tr class="bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Contact</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Location</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($customers as $customer)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('admin.customers.show', $customer) }}"
                            class="font-semibold text-teal-700 hover:underline">{{ $customer->name }}</a>
                        @unless ($customer->is_active)
                            <span class="ml-1.5 rounded-md bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">Inactive</span>
                        @endunless
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $customer->type->color() }}">
                            {{ $customer->type->label() }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">
                        {{ $customer->phone ?? '—' }}
                        @if ($customer->email)
                            <p class="text-xs text-slate-400">{{ $customer->email }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $customer->city ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.customers.edit', $customer) }}"
                            class="text-xs font-medium text-teal-600 hover:underline">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">
                        No customers yet.
                        <a href="{{ route('admin.customers.create') }}" class="text-teal-600 hover:underline">Add your first CRM record →</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $customers->links() }}</div>

@endsection

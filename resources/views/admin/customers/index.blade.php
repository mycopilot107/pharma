@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Customer management (CRM)</h1>
        <p class="text-slate-600">Doctors, hospitals, clinics, chemists & distributors</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">← Dashboard</a>
        <a href="{{ route('admin.customers.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Add customer</a>
    </div>
</div>

<div class="mt-6 grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">
    @foreach ($customerTypes as $type)
        <a href="{{ route('admin.customers.index', ['type' => $type->value]) }}" class="rounded-xl border bg-white p-4 shadow-sm hover:border-teal-300">
            <p class="text-xs text-slate-500">{{ $type->icon() }} {{ $type->label() }}</p>
            <p class="text-2xl font-bold">{{ $counts[$type->value] ?? 0 }}</p>
        </a>
    @endforeach
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone, city..." class="min-w-[200px] flex-1 rounded-lg border px-3 py-2 text-sm">
    <select name="type" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach ($customerTypes as $type)
            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        <option value="active" @selected(request('status') === 'active')>Active</option>
        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
    </select>
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-2xl border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Customer</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Type</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Contact</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Location</th>
                <th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($customers as $customer)
                <tr>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-teal-700 hover:underline">{{ $customer->name }}</a>
                        @unless ($customer->is_active)
                            <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">Inactive</span>
                        @endunless
                    </td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $customer->type->color() }}">{{ $customer->type->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $customer->phone ?? '—' }}
                        @if ($customer->email)<br><span class="text-xs">{{ $customer->email }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $customer->city ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="text-teal-700 hover:underline">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No customers yet. Add your first CRM record.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $customers->links() }}
@endsection

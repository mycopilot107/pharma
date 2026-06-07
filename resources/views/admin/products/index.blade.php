@extends('layouts.app')

@section('title', 'Product Catalog')

@section('content')

{{-- Header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <nav class="flex items-center gap-1 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-teal-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-600">Products</span>
        </nav>
        <h1 class="mt-1 text-xl font-bold text-slate-900">Product Catalog</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage company products for MR order taking</p>
    </div>
    <a href="{{ route('admin.products.create') }}"
        class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add product
    </a>
</div>

{{-- Stats --}}
<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Total products</p>
        <p class="mt-1.5 text-3xl font-bold text-slate-900">{{ $summary['total'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Active</p>
        <p class="mt-1.5 text-3xl font-bold text-emerald-600">{{ $summary['active'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Inactive</p>
        <p class="mt-1.5 text-3xl font-bold text-slate-400">{{ $summary['inactive'] }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-1 min-w-[200px] flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Search</label>
            <input type="search" name="search" value="{{ request('search') }}"
                placeholder="Name, SKU, brand…"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All</option>
                <option value="active"   @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit"
            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700">
            Apply
        </button>
        @if (request()->hasAny(['search','status']))
            <a href="{{ route('admin.products.index') }}"
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
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Product</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">SKU</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Category</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Price</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($products as $product)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                        @if ($product->brand)
                            <p class="text-xs text-slate-400">
                                {{ $product->brand }}
                                @if ($product->strength) · {{ $product->strength }}@endif
                            </p>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $product->sku ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $product->category ?? '—' }}</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $product->formattedPrice() }}</td>
                    <td class="px-5 py-3.5">
                        @if ($product->is_active)
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
                        <a href="{{ route('admin.products.edit', $product) }}"
                            class="text-xs font-medium text-teal-600 hover:underline">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                        No products yet.
                        <a href="{{ route('admin.products.create') }}" class="text-teal-600 hover:underline">Add your first product →</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $products->links() }}</div>

@endsection

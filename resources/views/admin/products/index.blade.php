@extends('layouts.app')

@section('title', 'Product Catalog')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Product catalog</h1>
        <p class="text-slate-600">Manage company products for MR order taking</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Dashboard</a>
        <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Add product</a>
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Total products</p>
        <p class="text-2xl font-bold">{{ $summary['total'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Active</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $summary['active'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Inactive</p>
        <p class="text-2xl font-bold text-slate-500">{{ $summary['inactive'] }}</p>
    </div>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, SKU, brand…" class="rounded-lg border px-3 py-2 text-sm">
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All</option>
        <option value="active" @selected(request('status') === 'active')>Active</option>
        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
    </select>
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-xl border bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="border-b bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Product</th>
                <th class="px-4 py-3">SKU</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Price</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($products as $product)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $product->name }}</p>
                        @if ($product->brand)<p class="text-xs text-slate-500">{{ $product->brand }} @if($product->strength)· {{ $product->strength }}@endif</p>@endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $product->sku ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $product->category ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $product->formattedPrice() }}</td>
                    <td class="px-4 py-3">
                        @if ($product->is_active)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Active</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-teal-700 hover:underline">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No products yet. <a href="{{ route('admin.products.create') }}" class="text-teal-700">Add your first product</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection

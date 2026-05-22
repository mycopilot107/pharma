@extends('layouts.app')

@section('title', 'Order Reports')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Order reports</h1>
        <p class="text-slate-600">Sales by product, MR, and customer type</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Orders</a>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <select name="user_id" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All MRs</option>
        @foreach ($representatives as $rep)
            <option value="{{ $rep->id }}" @selected(($filters['user_id'] ?? '') == $rep->id)>{{ $rep->name }}</option>
        @endforeach
    </select>
    <select name="customer_type" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All customer types</option>
        @foreach ($customerTypes as $type)
            <option value="{{ $type->value }}" @selected(($filters['customer_type'] ?? '') === $type->value)>{{ $type->icon() }} {{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\OrderStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-lg border px-3 py-2 text-sm">
    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-lg border px-3 py-2 text-sm">
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Apply</button>
</form>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Total orders</p>
        <p class="text-2xl font-bold">{{ $summary['total_orders'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Pending</p>
        <p class="text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Confirmed + delivered</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $summary['confirmed'] + $summary['delivered'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Revenue</p>
        <p class="text-xl font-bold">{{ format_money($summary['revenue']) }}</p>
    </div>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Top products</h2>
        <table class="mt-4 w-full text-sm">
            <thead><tr class="text-left text-xs text-slate-500"><th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Value</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($topProducts as $row)
                    <tr>
                        <td class="py-2">{{ $row['product_name'] }}</td>
                        <td class="py-2">{{ $row['total_qty'] }}</td>
                        <td class="py-2 text-right">{{ format_money($row['total_value']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-slate-500">No data for selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold">By medical representative</h2>
        <table class="mt-4 w-full text-sm">
            <thead><tr class="text-left text-xs text-slate-500"><th class="pb-2">MR</th><th class="pb-2">Orders</th><th class="pb-2 text-right">Value</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($byRepresentative as $row)
                    <tr>
                        <td class="py-2">{{ $row['name'] }}</td>
                        <td class="py-2">{{ $row['order_count'] }}</td>
                        <td class="py-2 text-right">{{ format_money($row['total_value']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-slate-500">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-xl border bg-white p-5 shadow-sm lg:col-span-2">
        <h2 class="font-semibold">By customer type</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @forelse ($byCustomerType as $row)
                <div class="rounded-lg border p-3">
                    <p class="text-lg">{{ $row['icon'] }} {{ $row['label'] }}</p>
                    <p class="text-sm text-slate-500">{{ $row['order_count'] }} orders</p>
                    <p class="font-semibold text-teal-700">{{ format_money($row['total_value']) }}</p>
                </div>
            @empty
                <p class="text-slate-500">No data for selected filters.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Orders</h1>
        <p class="text-slate-600">MR orders from doctors, hospitals, clinics, chemists & distributors</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.reports') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">📊 Reports</a>
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Dashboard</a>
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Pending approval</p>
        <p class="text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Confirmed</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $summary['confirmed'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Revenue (confirmed + delivered)</p>
        <p class="text-xl font-bold">{{ format_money($summary['revenue']) }}</p>
    </div>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Order # or customer…" class="rounded-lg border px-3 py-2 text-sm">
    <select name="user_id" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All MRs</option>
        @foreach ($representatives as $rep)
            <option value="{{ $rep->id }}" @selected(request('user_id') == $rep->id)>{{ $rep->name }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\OrderStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border px-3 py-2 text-sm">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border px-3 py-2 text-sm">
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-xl border bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="border-b bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Order</th>
                <th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">MR</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Amount</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($orders as $order)
                <tr>
                    <td class="px-4 py-3 font-mono text-xs">{{ $order->order_number }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $order->customer->name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->customer->type?->icon() }} {{ $order->customer->type?->label() }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $order->user->name }}</td>
                    <td class="px-4 py-3">{{ $order->order_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-medium">{{ $order->formattedTotal() }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $order->status->color() }}">{{ $order->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-teal-700 hover:underline">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No orders yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection

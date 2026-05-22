@extends('layouts.mr')

@section('title', 'My Orders')

@section('mr-content')
<div class="flex items-center justify-between">
    <h1 class="text-xl font-bold">My orders</h1>
    <a href="{{ route('mr.orders.create') }}" class="rounded-lg bg-teal-600 px-3 py-1.5 text-sm font-medium text-white">+ New order</a>
</div>

<div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
    <div class="rounded-lg border bg-white p-2">
        <p class="text-slate-500">Total</p>
        <p class="font-bold">{{ $summary['total'] }}</p>
    </div>
    <div class="rounded-lg border bg-white p-2">
        <p class="text-slate-500">Pending</p>
        <p class="font-bold text-amber-600">{{ $summary['pending'] }}</p>
    </div>
    <div class="rounded-lg border bg-white p-2">
        <p class="text-slate-500">Confirmed value</p>
        <p class="font-bold text-emerald-600">{{ format_money($summary['value']) }}</p>
    </div>
</div>

<form method="GET" class="mt-4">
    <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\OrderStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
</form>

<div class="mt-4 space-y-3">
    @forelse ($orders as $order)
        <a href="{{ route('mr.orders.show', $order) }}" class="block rounded-xl border bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-mono text-xs text-slate-500">{{ $order->order_number }}</p>
                    <p class="font-medium">{{ $order->customer->name }}</p>
                    <p class="text-xs text-slate-500">{{ $order->customer->type?->icon() }} {{ $order->order_date->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold">{{ $order->formattedTotal() }}</p>
                    <span class="rounded-full px-2 py-0.5 text-xs {{ $order->status->color() }}">{{ $order->status->label() }}</span>
                </div>
            </div>
        </a>
    @empty
        <p class="rounded-xl border bg-white p-6 text-center text-slate-500">No orders yet.</p>
    @endforelse
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection

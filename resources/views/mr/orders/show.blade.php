@extends('layouts.mr')

@section('title', 'Order')

@section('mr-content')
<a href="{{ route('mr.orders.index') }}" class="text-sm text-teal-700">← My orders</a>

<div class="mt-4 rounded-xl border bg-white p-4">
    <p class="font-mono text-xs text-slate-500">{{ $order->order_number }}</p>
    <h1 class="text-xl font-bold">{{ $order->customer->name }}</h1>
    <p class="text-sm text-slate-500">{{ $order->customer->type?->icon() }} {{ $order->customer->type?->label() }} · {{ $order->order_date->format('d M Y') }}</p>
    <span class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs {{ $order->status->color() }}">{{ $order->status->label() }}</span>
</div>

<ul class="mt-4 space-y-2 rounded-xl border bg-white p-4 text-sm">
    @foreach ($order->items as $item)
        <li class="flex justify-between border-b border-slate-100 pb-2 last:border-0">
            <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
            <span class="font-medium">{{ format_money($item->line_total, $order->currency) }}</span>
        </li>
    @endforeach
    <li class="flex justify-between pt-2 font-bold">
        <span>Total</span>
        <span class="text-teal-700">{{ $order->formattedTotal() }}</span>
    </li>
</ul>

@if ($order->notes)
    <p class="mt-4 text-sm text-slate-600"><strong>Notes:</strong> {{ $order->notes }}</p>
@endif
@if ($order->manager_notes)
    <p class="mt-2 text-sm text-slate-600"><strong>Manager:</strong> {{ $order->manager_notes }}</p>
@endif

@if ($order->isPending())
<form method="POST" action="{{ route('mr.orders.cancel', $order) }}" class="mt-6" onsubmit="return confirm('Cancel this order?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="w-full rounded-lg border border-red-200 py-2 text-sm text-red-700">Cancel order</button>
</form>
@endif
@endsection

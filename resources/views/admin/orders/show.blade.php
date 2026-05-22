@extends('layouts.app')

@section('title', 'Order '.$order->order_number)

@section('content')
<a href="{{ route('admin.orders.index') }}" class="text-sm text-teal-700">← Orders</a>

<div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">{{ $order->order_number }}</h1>
        <p class="text-slate-600">{{ $order->order_date->format('d M Y') }} · {{ $order->user->name }}</p>
    </div>
    <span class="rounded-full px-3 py-1 text-sm {{ $order->status->color() }}">{{ $order->status->label() }}</span>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800">Customer</h2>
        <p class="mt-2 text-lg font-medium">{{ $order->customer->name }}</p>
        <p class="text-sm text-slate-500">{{ $order->customer->type?->icon() }} {{ $order->customer->type?->label() }}</p>
        @if ($order->customer->city)<p class="mt-1 text-sm text-slate-600">{{ $order->customer->city }}</p>@endif
    </div>
    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800">Summary</h2>
        <p class="mt-2 text-2xl font-bold text-teal-700">{{ $order->formattedTotal() }}</p>
        @if ($order->notes)<p class="mt-2 text-sm text-slate-600"><strong>MR notes:</strong> {{ $order->notes }}</p>@endif
        @if ($order->manager_notes)<p class="mt-2 text-sm text-slate-600"><strong>Manager:</strong> {{ $order->manager_notes }}</p>@endif
    </div>
</div>

<div class="mt-6 overflow-hidden rounded-xl border bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="border-b bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Product</th>
                <th class="px-4 py-3">Qty</th>
                <th class="px-4 py-3">Unit price</th>
                <th class="px-4 py-3 text-right">Line total</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach ($order->items as $item)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $item->product_name }}</p>
                        @if ($item->sku)<p class="text-xs text-slate-500">{{ $item->sku }}</p>@endif
                    </td>
                    <td class="px-4 py-3">{{ $item->quantity }}</td>
                    <td class="px-4 py-3">{{ format_money($item->unit_price, $order->currency) }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ format_money($item->line_total, $order->currency) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t bg-slate-50">
            <tr>
                <td colspan="3" class="px-4 py-3 text-right font-semibold">Total</td>
                <td class="px-4 py-3 text-right text-lg font-bold">{{ $order->formattedTotal() }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@if ($order->isPending())
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <form method="POST" action="{{ route('admin.orders.confirm', $order) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
        @csrf
        <h3 class="font-semibold text-emerald-900">Confirm order</h3>
        <textarea name="manager_notes" rows="2" placeholder="Optional notes" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm"></textarea>
        <button type="submit" class="mt-3 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white">Confirm</button>
    </form>
    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="rounded-xl border border-red-200 bg-red-50 p-5">
        @csrf
        <h3 class="font-semibold text-red-900">Cancel order</h3>
        <textarea name="manager_notes" rows="2" required placeholder="Reason for cancellation" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm"></textarea>
        <button type="submit" class="mt-3 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white">Cancel</button>
    </form>
</div>
@elseif ($order->status === \App\Enums\OrderStatus::Confirmed)
<form method="POST" action="{{ route('admin.orders.deliver', $order) }}" class="mt-6">
    @csrf
    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Mark as delivered</button>
</form>
@endif
@endsection

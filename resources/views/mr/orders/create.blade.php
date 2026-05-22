@extends('layouts.mr')

@section('title', 'New Order')

@section('mr-content')
<a href="{{ route('mr.orders.index') }}" class="text-sm text-teal-700">← My orders</a>
<h1 class="mt-4 text-xl font-bold">New order</h1>
<p class="text-sm text-slate-500">Order for doctor, hospital, clinic, chemist or distributor</p>

@if ($products->isEmpty())
    <p class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">No products in catalog yet. Ask your admin to add products first.</p>
@elseif ($customers->isEmpty())
    <p class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">No customers yet. <a href="{{ route('mr.customers.index') }}" class="underline">Add a customer</a> first.</p>
@else
<form method="POST" action="{{ route('mr.orders.store') }}" id="order-form" class="mt-6 space-y-4 rounded-xl border bg-white p-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-slate-700">Customer *</label>
        <select name="customer_id" required class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm">
            <option value="">Select customer</option>
            @foreach ($customers as $c)
                <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                    {{ $c->type?->icon() }} {{ $c->name }}@if($c->city) ({{ $c->city }})@endif
                </option>
            @endforeach
        </select>
        @error('customer_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Order date *</label>
        <input type="date" name="order_date" value="{{ old('order_date', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="mt-1 w-full rounded-lg border px-3 py-2.5">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Products *</label>
        <div id="line-items" class="mt-2 space-y-2">
            <div class="line-item flex gap-2">
                <select name="items[0][product_id]" required class="flex-1 rounded-lg border px-2 py-2 text-sm product-select">
                    <option value="">Product</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}">{{ $p->displayLabel() }} — {{ $p->formattedPrice() }}</option>
                    @endforeach
                </select>
                <input type="number" name="items[0][quantity]" min="1" value="1" required placeholder="Qty" class="w-20 rounded-lg border px-2 py-2 text-sm">
            </div>
        </div>
        <button type="button" id="add-line" class="mt-2 text-sm text-teal-700">+ Add another product</button>
        @error('items')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
    </div>

    <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white">Submit order</button>
</form>

@push('head')
<script>
(function() {
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'label' => $p->displayLabel().' — '.$p->formattedPrice(), 'price' => $p->unit_price]));
    let lineIndex = 1;
    document.getElementById('add-line').addEventListener('click', function() {
        const wrap = document.getElementById('line-items');
        const row = document.createElement('div');
        row.className = 'line-item flex gap-2';
        let opts = '<option value="">Product</option>';
        products.forEach(p => { opts += `<option value="${p.id}" data-price="${p.price}">${p.label}</option>`; });
        row.innerHTML = `<select name="items[${lineIndex}][product_id]" required class="flex-1 rounded-lg border px-2 py-2 text-sm">${opts}</select>
            <input type="number" name="items[${lineIndex}][quantity]" min="1" value="1" required class="w-20 rounded-lg border px-2 py-2 text-sm">
            <button type="button" class="remove-line text-red-600 text-sm px-1">✕</button>`;
        wrap.appendChild(row);
        lineIndex++;
        row.querySelector('.remove-line').addEventListener('click', () => row.remove());
    });
})();
</script>
@endpush
@endif
@endsection

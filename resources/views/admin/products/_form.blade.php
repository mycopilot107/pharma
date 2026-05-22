@php $product = $product ?? null; @endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Product name *</label>
        <input type="text" name="name" value="{{ old('name', $product?->name) }}" required
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $product?->sku) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        @error('sku')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Brand</label>
        <input type="text" name="brand" value="{{ old('brand', $product?->brand) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Strength</label>
        <input type="text" name="strength" value="{{ old('strength', $product?->strength) }}" placeholder="e.g. 500mg"
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Pack size</label>
        <input type="text" name="pack_size" value="{{ old('pack_size', $product?->pack_size) }}" placeholder="e.g. 10 tabs"
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Category</label>
        <input type="text" name="category" value="{{ old('category', $product?->category) }}" placeholder="e.g. Cardiology"
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">MRP ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" name="mrp" value="{{ old('mrp', $product?->mrp) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Unit price *</label>
        <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price', $product?->unit_price ?? 0) }}" required
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        @error('unit_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('description', $product?->description) }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))
                class="rounded border-slate-300">
            Active (visible to MRs for ordering)
        </label>
    </div>
</div>

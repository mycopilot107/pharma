@php
    $target = $target ?? null;
@endphp

<div>
    <label class="block text-sm font-medium text-slate-700">Medical representative *</label>
    <select name="user_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        <option value="">Select MR</option>
        @foreach ($representatives as $rep)
            <option value="{{ $rep->id }}" @selected(old('user_id', $target?->user_id) == $rep->id)>{{ $rep->name }}</option>
        @endforeach
    </select>
    @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Target type *</label>
    <select name="type" id="target_type" required class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        @foreach ($targetTypes as $t)
            <option value="{{ $t->value }}" @selected(old('type', $target?->type?->value) === $t->value)>{{ $t->icon() }} {{ $t->label() }}</option>
        @endforeach
    </select>
    <p id="type-hint" class="mt-1 text-xs text-slate-500"></p>
    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Title *</label>
    <input type="text" name="title" value="{{ old('title', $target?->title) }}" required placeholder="e.g. March 2026 sales push"
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div id="field-product" class="hidden">
    <label class="block text-sm font-medium text-slate-700">Product name *</label>
    <input type="text" name="product_name" value="{{ old('product_name', $target?->product_name) }}" placeholder="e.g. CardioMax 500mg"
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    @error('product_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div id="field-area" class="hidden">
    <label class="block text-sm font-medium text-slate-700">Area / territory *</label>
    <input type="text" name="area_name" value="{{ old('area_name', $target?->area_name) }}" placeholder="e.g. North Delhi Zone"
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    @error('area_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div id="field-period" class="hidden space-y-3">
    <div>
        <label class="block text-sm font-medium text-slate-700">Target month</label>
        <input type="month" name="period_month" value="{{ old('period_month', $target?->period_start?->format('Y-m')) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <p class="text-xs text-slate-500">Or use custom date range:</p>
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="block text-xs text-slate-500">Start date</label>
            <input type="date" name="period_start" value="{{ old('period_start', $target?->period_start?->format('Y-m-d')) }}"
                class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        </div>
        <div>
            <label class="block text-xs text-slate-500">End date</label>
            <input type="date" name="period_end" value="{{ old('period_end', $target?->period_end?->format('Y-m-d')) }}"
                class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        </div>
    </div>
    @error('period_month')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">Target value *</label>
        <input type="number" step="0.01" name="target_value" value="{{ old('target_value', $target?->target_value) }}" required
            class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        @error('target_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Unit *</label>
        <select name="unit" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
            @foreach ($targetUnits as $u)
                <option value="{{ $u->value }}" @selected(old('unit', $target?->unit?->value) === $u->value)>{{ $u->label() }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Current achieved (optional)</label>
    <input type="number" step="0.01" name="achieved_value" value="{{ old('achieved_value', $target?->achieved_value ?? 0) }}"
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Description</label>
    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('description', $target?->description) }}</textarea>
</div>

@push('head')
<script>
const typeHints = @json(collect($targetTypes)->mapWithKeys(fn($t) => [$t->value => $t->description()]));
function toggleTargetFields() {
    const type = document.getElementById('target_type').value;
    document.getElementById('type-hint').textContent = typeHints[type] || '';
    document.getElementById('field-product').classList.toggle('hidden', type !== 'product');
    document.getElementById('field-area').classList.toggle('hidden', type !== 'area');
    document.getElementById('field-period').classList.toggle('hidden', !['monthly', 'sales'].includes(type));
}
document.getElementById('target_type').addEventListener('change', toggleTargetFields);
toggleTargetFields();
</script>
@endpush

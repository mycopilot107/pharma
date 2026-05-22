@extends('layouts.mr')

@section('title', 'Submit Expense')

@section('mr-content')
<a href="{{ route('mr.expenses.index') }}" class="text-sm text-teal-700">← My expenses</a>
<h1 class="mt-4 text-xl font-bold">Submit expense</h1>
<p class="text-sm text-slate-500">Upload fuel, hotel or food bill for approval</p>

<form method="POST" action="{{ route('mr.expenses.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-xl border bg-white p-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-slate-700">Expense type *</label>
        <select name="type" required class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm">
            @foreach ($expenseTypes as $type)
                <option value="{{ $type->value }}">{{ $type->icon() }} {{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Amount ({{ currency_symbol() }}) *</label>
        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="mt-1 w-full rounded-lg border px-3 py-2.5">
        @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Expense date *</label>
        <input type="date" name="expense_date" value="{{ old('expense_date', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="mt-1 w-full rounded-lg border px-3 py-2.5">
        @error('expense_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <input type="text" name="description" value="{{ old('description') }}" placeholder="e.g. Mumbai–Pune fuel" class="mt-1 w-full rounded-lg border px-3 py-2.5">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Bill / receipt *</label>
        <p class="text-xs text-slate-500 mb-1">Photo or PDF, max 5 MB</p>
        <input type="file" name="receipt" accept="image/*,application/pdf" capture="environment" required class="w-full text-sm">
        @error('receipt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white">Submit for approval</button>
</form>
@endsection

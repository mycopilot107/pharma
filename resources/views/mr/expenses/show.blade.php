@extends('layouts.mr')

@section('title', 'Expense')

@section('mr-content')
<a href="{{ route('mr.expenses.index') }}" class="text-sm text-teal-700">← My expenses</a>

<div class="mt-4 flex items-start justify-between">
    <div>
        <p class="text-2xl">{{ $expense->type->icon() }}</p>
        <h1 class="text-xl font-bold">{{ $expense->type->label() }}</h1>
        <p class="text-lg font-semibold text-slate-800">{{ $expense->formattedAmount() }}</p>
        <p class="text-sm text-slate-500">{{ $expense->expense_date->format('d M Y') }}</p>
    </div>
    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $expense->status->color() }}">{{ $expense->status->label() }}</span>
</div>

@if ($expense->description)
    <p class="mt-3 text-sm text-slate-600">{{ $expense->description }}</p>
@endif

@if ($expense->receipt_path)
<div class="mt-6 rounded-xl border bg-white p-4">
    <h2 class="text-sm font-semibold text-slate-800">Receipt</h2>
    @if ($expense->isImageReceipt())
        <a href="{{ $expense->receiptUrl() }}" target="_blank" class="mt-2 block">
            <img src="{{ $expense->receiptUrl() }}" alt="Receipt" class="max-h-64 rounded-lg border object-contain">
        </a>
    @else
        <a href="{{ $expense->receiptUrl() }}" target="_blank" class="mt-2 inline-flex items-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-sm text-teal-700">
            📄 View PDF — {{ $expense->receipt_original_name }}
        </a>
    @endif
</div>
@endif

@if ($expense->manager_notes)
<div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
    <p class="text-xs font-medium text-slate-600">Manager notes</p>
    <p class="mt-1 text-sm text-slate-700">{{ $expense->manager_notes }}</p>
    @if ($expense->reviewed_at)
        <p class="text-xs text-slate-500 mt-2">{{ $expense->status->label() }} {{ $expense->reviewed_at->format('d M Y, h:i A') }}</p>
    @endif
</div>
@endif

@if ($expense->isPending())
<form method="POST" action="{{ route('mr.expenses.destroy', $expense) }}" class="mt-6" onsubmit="return confirm('Delete this expense?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-sm text-red-600 hover:underline">Delete pending expense</button>
</form>
@endif
@endsection

@extends('layouts.app')

@section('title', 'Review Expense')

@section('content')
<a href="{{ route('admin.expenses.index') }}" class="text-sm text-teal-700 hover:underline">← All expenses</a>

<div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start">
    <div class="flex-1">
        <span class="rounded-full px-2 py-0.5 text-xs {{ $expense->status->color() }}">{{ $expense->status->label() }}</span>
        <h1 class="mt-2 text-2xl font-bold">{{ $expense->type->icon() }} {{ $expense->type->label() }}</h1>
        <p class="text-xl font-semibold text-slate-800">{{ $expense->formattedAmount() }}</p>
        <p class="text-slate-600">{{ $expense->expense_date->format('l, d M Y') }}</p>
        <p class="mt-2 text-sm text-slate-500">Submitted by <strong>{{ $expense->user->name }}</strong> · {{ $expense->created_at->format('d M Y, h:i A') }}</p>
        @if ($expense->description)
            <p class="mt-3 text-slate-700">{{ $expense->description }}</p>
        @endif
    </div>

    @if ($expense->isPending())
    <div class="w-full lg:w-80 space-y-3 rounded-2xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800">Manager decision</h2>
        <form method="POST" action="{{ route('admin.expenses.approve', $expense) }}">
            @csrf
            <textarea name="manager_notes" rows="2" placeholder="Optional note" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
            <button type="submit" class="mt-2 w-full rounded-lg bg-emerald-600 py-2.5 font-medium text-white hover:bg-emerald-700">Approve</button>
        </form>
        <form method="POST" action="{{ route('admin.expenses.reject', $expense) }}" class="border-t pt-3">
            @csrf
            <textarea name="manager_notes" rows="2" placeholder="Reason for rejection (required)" required class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
            @error('manager_notes')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="mt-2 w-full rounded-lg border border-red-300 bg-white py-2.5 font-medium text-red-700 hover:bg-red-50">Reject</button>
        </form>
    </div>
    @elseif ($expense->manager_notes)
    <div class="w-full lg:w-80 rounded-2xl border bg-slate-50 p-5">
        <p class="text-xs font-medium text-slate-600">Manager notes</p>
        <p class="mt-1 text-sm">{{ $expense->manager_notes }}</p>
        <p class="mt-2 text-xs text-slate-500">By {{ $expense->reviewer?->name }} · {{ $expense->reviewed_at?->format('d M Y, h:i A') }}</p>
    </div>
    @endif
</div>

@if ($expense->receipt_path)
<div class="mt-8 rounded-2xl border bg-white p-6 shadow-sm">
    <h2 class="font-semibold text-slate-800">Uploaded bill</h2>
    @if ($expense->isImageReceipt())
        <a href="{{ $expense->receiptUrl() }}" target="_blank" class="mt-4 inline-block">
            <img src="{{ $expense->receiptUrl() }}" alt="Receipt" class="max-h-[480px] rounded-lg border shadow-sm">
        </a>
    @else
        <iframe src="{{ $expense->receiptUrl() }}" class="mt-4 h-[500px] w-full rounded-lg border"></iframe>
        <a href="{{ $expense->receiptUrl() }}" target="_blank" class="mt-2 inline-block text-sm text-teal-700 hover:underline">Open PDF in new tab</a>
    @endif
</div>
@endif
@endsection

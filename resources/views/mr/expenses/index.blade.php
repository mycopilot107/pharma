@extends('layouts.mr')

@section('title', 'My Expenses')

@section('mr-content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold">My expenses</h1>
        <p class="text-sm text-slate-500">Fuel, hotel & food bills</p>
    </div>
    <a href="{{ route('mr.expenses.create') }}" class="rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white">+ Submit</a>
</div>

<div class="mt-4 grid grid-cols-2 gap-3 text-center">
    <div class="rounded-xl bg-amber-50 p-3">
        <p class="text-xs text-amber-700">Pending</p>
        <p class="text-lg font-bold text-amber-800">{{ format_money($summary['pending']) }}</p>
    </div>
    <div class="rounded-xl bg-emerald-50 p-3">
        <p class="text-xs text-emerald-700">Approved</p>
        <p class="text-lg font-bold text-emerald-800">{{ format_money($summary['approved']) }}</p>
    </div>
</div>

<form method="GET" class="mt-4 flex gap-2">
    <select name="type" class="flex-1 rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach ($expenseTypes as $type)
            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->icon() }} {{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\ExpenseStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-slate-100 px-3 py-2 text-sm">Filter</button>
</form>

<div class="mt-4 space-y-2">
    @forelse ($expenses as $expense)
        <a href="{{ route('mr.expenses.show', $expense) }}" class="block rounded-xl border bg-white p-3 hover:border-teal-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-medium">{{ $expense->type->icon() }} {{ $expense->type->label() }}</p>
                    <p class="text-sm text-slate-600">{{ $expense->formattedAmount() }}</p>
                    <p class="text-xs text-slate-500">{{ $expense->expense_date->format('d M Y') }}</p>
                </div>
                <span class="rounded-full px-2 py-0.5 text-xs {{ $expense->status->color() }}">{{ $expense->status->label() }}</span>
            </div>
        </a>
    @empty
        <p class="py-8 text-center text-sm text-slate-500">No expenses yet. Submit your first bill.</p>
    @endforelse
</div>

{{ $expenses->links() }}
@endsection

@extends('layouts.app')

@section('title', 'Expense Management')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Expense management</h1>
        <p class="text-slate-600">Review and approve MR fuel, hotel & food expenses</p>
    </div>
    <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Dashboard</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Pending approval</p>
        <p class="text-2xl font-bold text-amber-600">{{ $summary['pending_count'] }}</p>
        <p class="text-sm text-amber-700">{{ format_money($summary['pending_amount']) }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Approved (all time)</p>
        <p class="text-xl font-bold text-emerald-600">{{ format_money($summary['approved_amount']) }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">⛽ Fuel pending</p>
        <p class="text-xl font-bold">{{ $summary['fuel'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">🏨 Hotel pending</p>
        <p class="text-xl font-bold">{{ $summary['hotel'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">🍽️ Food pending</p>
        <p class="text-xl font-bold">{{ $summary['food'] }}</p>
    </div>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <select name="user_id" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All MRs</option>
        @foreach ($representatives as $rep)
            <option value="{{ $rep->id }}" @selected(request('user_id') == $rep->id)>{{ $rep->name }}</option>
        @endforeach
    </select>
    <select name="type" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach ($expenseTypes as $type)
            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border px-3 py-2 text-sm">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border px-3 py-2 text-sm">
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-2xl border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-600">MR</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Type</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Amount</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Date</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                <th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($expenses as $expense)
                <tr class="{{ $expense->isPending() ? 'bg-amber-50/50' : '' }}">
                    <td class="px-4 py-3 font-medium">{{ $expense->user->name }}</td>
                    <td class="px-4 py-3">{{ $expense->type->icon() }} {{ $expense->type->label() }}</td>
                    <td class="px-4 py-3">{{ $expense->formattedAmount() }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $expense->status->color() }}">{{ $expense->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.expenses.show', $expense) }}" class="text-teal-700 hover:underline">Review</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No expenses found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $expenses->links() }}
@endsection

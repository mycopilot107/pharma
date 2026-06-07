@extends('layouts.app')

@section('title', 'Expense Management')

@section('content')

{{-- Header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <nav class="flex items-center gap-1 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-teal-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-600">Expenses</span>
        </nav>
        <h1 class="mt-1 text-xl font-bold text-slate-900">Expense Management</h1>
        <p class="mt-0.5 text-sm text-slate-500">Review and approve MR fuel, hotel &amp; food expenses</p>
    </div>
</div>

{{-- Stats --}}
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
        <p class="text-xs font-medium text-slate-500">Pending approval</p>
        <p class="mt-1.5 text-3xl font-bold text-amber-500">{{ $summary['pending_count'] }}</p>
        <p class="mt-1 text-xs font-medium text-amber-600">{{ format_money($summary['pending_amount']) }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
        <p class="text-xs font-medium text-slate-500">Approved total</p>
        <p class="mt-1.5 text-2xl font-bold text-emerald-600">{{ format_money($summary['approved_amount']) }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Fuel pending</p>
        <p class="mt-1.5 text-2xl font-bold text-slate-700">{{ $summary['fuel'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Hotel pending</p>
        <p class="mt-1.5 text-2xl font-bold text-slate-700">{{ $summary['hotel'] }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Food pending</p>
        <p class="mt-1.5 text-2xl font-bold text-slate-700">{{ $summary['food'] }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">MR</label>
            <select name="user_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All MRs</option>
                @foreach ($representatives as $rep)
                    <option value="{{ $rep->id }}" @selected(request('user_id') == $rep->id)>{{ $rep->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Type</label>
            <select name="type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All types</option>
                @foreach ($expenseTypes as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All statuses</option>
                <option value="pending"  @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        </div>
        <button type="submit"
            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700">
            Apply
        </button>
        @if (request()->hasAny(['user_id','type','status','date_from','date_to']))
            <a href="{{ route('admin.expenses.index') }}"
                class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                Clear
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-100 text-sm">
        <thead>
            <tr class="bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">MR</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Amount</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($expenses as $expense)
                <tr class="hover:bg-slate-50/50 {{ $expense->isPending() ? 'bg-amber-50/30' : '' }}">
                    <td class="px-5 py-3.5 font-medium text-slate-900">{{ $expense->user->name }}</td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $expense->type->icon() }} {{ $expense->type->label() }}</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $expense->formattedAmount() }}</td>
                    <td class="px-5 py-3.5 text-slate-500">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-5 py-3.5">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $expense->status->color() }}">
                            {{ $expense->status->label() }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.expenses.show', $expense) }}"
                            class="text-xs font-medium text-teal-600 hover:underline">Review</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">No expenses found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $expenses->links() }}</div>

@endsection

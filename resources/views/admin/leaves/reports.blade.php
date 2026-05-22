@extends('layouts.app')

@section('title', 'Leave Reports')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Leave reports</h1>
        <p class="text-slate-600">Usage by MR, leave type, and annual balances</p>
    </div>
    <a href="{{ route('admin.leaves.index') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Leave management</a>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <select name="user_id" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All MRs</option>
        @foreach ($representatives as $rep)
            <option value="{{ $rep->id }}" @selected(($filters['user_id'] ?? '') == $rep->id)>{{ $rep->name }}</option>
        @endforeach
    </select>
    <select name="type" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach ($leaveTypes as $type)
            <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->icon() }} {{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\LeaveStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-lg border px-3 py-2 text-sm" placeholder="From">
    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-lg border px-3 py-2 text-sm" placeholder="To">
    <select name="year" class="rounded-lg border px-3 py-2 text-sm" title="Year for balance table">
        @for ($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" @selected($year === $y)>Balance year {{ $y }}</option>
        @endfor
    </select>
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Apply</button>
</form>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Total requests</p>
        <p class="text-2xl font-bold">{{ $summary['total_requests'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Pending</p>
        <p class="text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Approved days</p>
        <p class="text-2xl font-bold text-emerald-600">{{ number_format($summary['approved_days'], 1) }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">On leave today</p>
        <p class="text-2xl font-bold text-teal-600">{{ $summary['on_leave_now'] }}</p>
    </div>
</div>

<div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-600">
    <span>Approved: <strong>{{ $summary['approved'] }}</strong></span>
    <span>Rejected: <strong>{{ $summary['rejected'] }}</strong></span>
    <span>Cancelled: <strong>{{ $summary['cancelled'] }}</strong></span>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold">By medical representative</h2>
        <table class="mt-4 w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500">
                    <th class="pb-2">MR</th>
                    <th class="pb-2">Requests</th>
                    <th class="pb-2">Approved days</th>
                    <th class="pb-2 text-right">Pending days</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($byRepresentative as $row)
                    <tr>
                        <td class="py-2 font-medium">{{ $row['name'] }}</td>
                        <td class="py-2">{{ $row['request_count'] }}</td>
                        <td class="py-2">{{ number_format($row['approved_days'], 1) }}</td>
                        <td class="py-2 text-right text-amber-700">{{ number_format($row['pending_days'], 1) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-slate-500">No data for selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="font-semibold">By leave type</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @forelse ($byLeaveType as $row)
                <div class="rounded-lg border p-3">
                    <p class="text-lg">{{ $row['icon'] }} {{ $row['label'] }}</p>
                    <p class="text-sm text-slate-500">{{ $row['request_count'] }} requests</p>
                    <p class="font-semibold text-teal-700">{{ number_format($row['approved_days'], 1) }} approved days</p>
                </div>
            @empty
                <p class="text-slate-500">No data for selected filters.</p>
            @endforelse
        </div>
    </div>
</div>

<section class="mt-8 overflow-hidden rounded-xl border bg-white shadow-sm">
    <div class="border-b bg-slate-50 px-5 py-4">
        <h2 class="font-semibold">Annual leave balances ({{ $year }})</h2>
        <p class="text-sm text-slate-500">Used vs allowance per MR — unpaid leave has no balance cap</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">MR</th>
                    @foreach ($leaveTypes as $type)
                        @if ($type->hasBalance())
                            <th class="px-4 py-3 text-center font-medium text-slate-600">{{ $type->icon() }} {{ $type->label() }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($balances as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row['name'] }}</td>
                        @foreach ($leaveTypes as $type)
                            @if ($type->hasBalance())
                                @php $b = $row['balances'][$type->value] ?? null; @endphp
                                <td class="px-4 py-3 text-center text-xs">
                                    @if ($b && $b['allowance'] !== null)
                                        <span class="text-slate-600">{{ number_format($b['used'], 1) }}</span>
                                        <span class="text-slate-400">/</span>
                                        <span>{{ $b['allowance'] }}</span>
                                        <br>
                                        <span class="{{ ($b['remaining'] ?? 0) <= 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                            {{ number_format($b['remaining'] ?? 0, 1) }} left
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">No representatives in your company.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

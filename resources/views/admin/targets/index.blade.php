@extends('layouts.app')

@section('title', 'Target Management')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Target management</h1>
        <p class="text-slate-600">Assign monthly, product, sales &amp; area targets to your MRs</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">← Dashboard</a>
        <a href="{{ route('admin.targets.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Assign target</a>
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Active</p>
        <p class="text-2xl font-bold">{{ $summary['active'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">📅 Monthly</p>
        <p class="text-xl font-bold">{{ $summary['monthly'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">📦 Product</p>
        <p class="text-xl font-bold">{{ $summary['product'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">💰 Sales</p>
        <p class="text-xl font-bold">{{ $summary['sales'] }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">🗺️ Area</p>
        <p class="text-xl font-bold">{{ $summary['area'] }}</p>
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
        @foreach (\App\Enums\TargetType::cases() as $t)
            <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        <option value="active" @selected(request('status') === 'active')>Active</option>
        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
    </select>
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 space-y-4">
    @forelse ($targets as $target)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex gap-3">
                    <span class="text-2xl">{{ $target->type->icon() }}</span>
                    <div>
                        <p class="font-semibold text-slate-900">{{ $target->title }}</p>
                        <p class="text-sm text-slate-500">{{ $target->type->label() }} · {{ $target->user->name }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $target->subtitle() }}</p>
                    </div>
                </div>
                <div class="flex gap-2 text-sm">
                    <a href="{{ route('admin.targets.edit', $target) }}" class="text-teal-700 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('admin.targets.destroy', $target) }}" onsubmit="return confirm('Remove this target?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-1">
                    <span>{{ $target->formattedAchieved() }} / {{ $target->formattedTarget() }}</span>
                    <span class="font-medium">{{ $target->progressPercent() }}%</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-teal-500" style="width: {{ $target->progressPercent() }}%"></div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.targets.progress', $target) }}" class="mt-4 flex flex-wrap gap-2 items-end">
                @csrf
                @method('PATCH')
                <div>
                    <label class="text-xs text-slate-500">Update achieved</label>
                    <input type="number" step="0.01" name="achieved_value" value="{{ $target->achieved_value }}" class="mt-1 rounded-lg border px-3 py-1.5 text-sm w-32">
                </div>
                <button type="submit" class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium hover:bg-slate-200">Save progress</button>
            </form>
        </div>
    @empty
        <p class="rounded-xl border border-dashed p-12 text-center text-slate-500">No targets assigned yet. Click "Assign target" to get started.</p>
    @endforelse
</div>

{{ $targets->links() }}
@endsection

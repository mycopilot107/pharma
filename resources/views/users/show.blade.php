@extends('layouts.app')

@section('title', $user->name)

@section('content')
<a href="{{ route('users.index') }}" class="text-sm text-teal-700 hover:underline">← All representatives</a>

<div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
        <p class="text-slate-600">{{ $user->email }}</p>
        @if ($user->phone)<p class="text-sm text-slate-500">{{ $user->phone }}</p>@endif
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($user->is_active)
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm text-emerald-800">Active</span>
        @else
            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600">Inactive</span>
        @endif
        @if ($user->tracking_active)
            <span class="rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800">On duty now</span>
        @endif
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Total visits</p>
        <p class="text-2xl font-bold">{{ $user->visits_count }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Completed visits</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $user->completed_visits_count }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Orders</p>
        <p class="text-2xl font-bold">{{ $ordersCount }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Expenses submitted</p>
        <p class="text-2xl font-bold">{{ $expensesCount }}</p>
    </div>
</div>

<div class="mt-6 rounded-xl border bg-white p-5 shadow-sm">
    <h2 class="font-semibold text-slate-800">Account details</h2>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-slate-500">Member since</dt>
            <dd class="font-medium">{{ $user->created_at->format('d M Y, H:i') }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Last updated</dt>
            <dd class="font-medium">{{ $user->updated_at->format('d M Y, H:i') }}</dd>
        </div>
        @if ($user->last_location_at)
            <div class="sm:col-span-2">
                <dt class="text-slate-500">Last GPS update</dt>
                <dd class="font-medium">{{ $user->last_location_at->diffForHumans() }}</dd>
            </div>
        @endif
    </dl>
</div>

<div class="mt-6 flex gap-3">
    <a href="{{ route('users.edit', $user) }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Edit</a>
    <a href="{{ route('admin.tracking.index') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">Live tracking</a>
    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this representative permanently?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-lg border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete</button>
    </form>
</div>
@endsection

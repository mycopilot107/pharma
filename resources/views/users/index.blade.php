@extends('layouts.app')

@section('title', 'Manage Medical Representatives')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Manage medical representatives</h1>
        <p class="text-slate-600">{{ $usedSlots }} / {{ $company->user_limit }} slots used · {{ $remainingSlots }} remaining</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">← Dashboard</a>
        @if ($canAdd)
            <a href="{{ route('users.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Add MR</a>
        @else
            <span class="rounded-lg bg-slate-200 px-4 py-2 text-sm text-slate-600">Plan limit reached</span>
        @endif
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Total MRs</p>
        <p class="text-2xl font-bold">{{ $usedSlots }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Active</p>
        <p class="text-2xl font-bold text-emerald-600">{{ $activeCount }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">Available slots</p>
        <p class="text-2xl font-bold text-teal-600">{{ $remainingSlots }}</p>
    </div>
</div>

<form method="GET" class="mt-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone…"
        class="min-w-[200px] flex-1 rounded-lg border px-3 py-2 text-sm">
    <select name="status" class="rounded-lg border px-3 py-2 text-sm">
        <option value="">All statuses</option>
        <option value="active" @selected(request('status') === 'active')>Active</option>
        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
    </select>
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Filter</button>
</form>

<div class="mt-6 overflow-hidden rounded-2xl border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Name</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Contact</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Visits</th>
                <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                <th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($representatives as $user)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">Joined {{ $user->created_at->format('d M Y') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p>{{ $user->email }}</p>
                        <p class="text-xs text-slate-500">{{ $user->phone ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $user->visits_count }}</td>
                    <td class="px-4 py-3">
                        @if ($user->is_active)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Active</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Inactive</span>
                        @endif
                        @if ($user->tracking_active)
                            <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">On duty</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('users.show', $user) }}" class="text-teal-700 hover:underline">View</a>
                        <span class="text-slate-300">·</span>
                        <a href="{{ route('users.edit', $user) }}" class="text-teal-700 hover:underline">Edit</a>
                        <span class="text-slate-300">·</span>
                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline"
                            onsubmit="return confirm('Remove {{ $user->name }}? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                        No representatives yet.
                        @if ($canAdd)
                            <a href="{{ route('users.create') }}" class="text-teal-700 hover:underline">Add your first MR</a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $representatives->links() }}</div>
@endsection

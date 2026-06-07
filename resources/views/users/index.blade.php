@extends('layouts.app')

@section('title', 'Manage Medical Representatives')

@section('content')

{{-- Header --}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <nav class="flex items-center gap-1 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-teal-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-600">Team</span>
        </nav>
        <h1 class="mt-1 text-xl font-bold text-slate-900">Medical Representatives</h1>
        <p class="mt-0.5 text-sm text-slate-500">{{ $usedSlots }} / {{ $company->user_limit }} seats used · {{ $remainingSlots }} available</p>
    </div>
    @if ($canAdd)
        <a href="{{ route('users.create') }}"
            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add MR
        </a>
    @else
        <span class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-500">Plan limit reached</span>
    @endif
</div>

{{-- Stats --}}
<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Total MRs</p>
        <p class="mt-1.5 text-3xl font-bold text-slate-900">{{ $usedSlots }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Active</p>
        <p class="mt-1.5 text-3xl font-bold text-emerald-600">{{ $activeCount }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-medium text-slate-500">Available slots</p>
        <p class="mt-1.5 text-3xl font-bold text-teal-600">{{ $remainingSlots }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-1 min-w-[200px] flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Search</label>
            <input type="search" name="search" value="{{ request('search') }}"
                placeholder="Name, email, phone…"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                <option value="">All statuses</option>
                <option value="active"   @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit"
            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700">
            Apply
        </button>
        @if (request()->hasAny(['search','status']))
            <a href="{{ route('users.index') }}"
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
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Contact</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Visits</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($representatives as $user)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs text-slate-400">Joined {{ $user->created_at->format('d M Y') }}</p>
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="text-slate-700">{{ $user->email }}</p>
                        <p class="text-xs text-slate-400">{{ $user->phone ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3.5 font-semibold text-slate-700">{{ $user->visits_count }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex flex-wrap gap-1">
                            @if ($user->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Inactive
                                </span>
                            @endif
                            @if ($user->tracking_active)
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">On duty</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('users.show', $user) }}" class="text-xs font-medium text-teal-600 hover:underline">View</a>
                            <a href="{{ route('users.edit', $user) }}" class="text-xs font-medium text-slate-500 hover:text-teal-600">Edit</a>
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline"
                                onsubmit="return confirm('Remove {{ addslashes($user->name) }}? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center">
                        <p class="text-sm text-slate-400">No representatives yet.</p>
                        @if ($canAdd)
                            <a href="{{ route('users.create') }}" class="mt-1 inline-block text-sm font-medium text-teal-600 hover:underline">Add your first MR →</a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $representatives->links() }}</div>

@endsection

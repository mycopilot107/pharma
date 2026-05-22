@extends('layouts.app')

@section('title', 'Reminders & Notifications')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Reminders & notifications</h1>
        <p class="text-slate-600">Team follow-ups, target alerts, meetings & doctor revisits</p>
    </div>
    <div class="flex gap-2">
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <button type="submit" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">Mark all read</button>
            </form>
        @endif
        <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm">← Dashboard</a>
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-4">
    @foreach (\App\Enums\ReminderType::cases() as $type)
        <a href="{{ route('admin.notifications.index', ['type' => $type->value]) }}" class="rounded-xl border bg-white p-4 text-center hover:border-teal-300 {{ request('type') === $type->value ? 'ring-2 ring-teal-400' : '' }}">
            <p class="text-2xl">{{ $type->icon() }}</p>
            <p class="text-sm font-medium mt-1">{{ $type->label() }}</p>
        </a>
    @endforeach
</div>

<div class="mt-6 space-y-3">
    @forelse ($notifications as $notification)
        <div class="rounded-xl border bg-white p-5 shadow-sm {{ $notification->isUnread() ? 'border-l-4 border-l-teal-500' : '' }}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500">{{ $notification->type->icon() }} {{ $notification->type->label() }}
                        @if ($notification->priority === 'high')<span class="ml-1 text-red-600">Urgent</span>@endif
                    </span>
                    <h2 class="mt-1 font-semibold text-slate-900">{{ $notification->title }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $notification->body }}</p>
                    <p class="mt-2 text-xs text-slate-400">{{ $notification->remind_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    @if ($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white">Open</a>
                    @endif
                    @if ($notification->isUnread())
                        <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg border px-4 py-2 text-sm">Mark read</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.notifications.dismiss', $notification) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-slate-500">Dismiss</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-dashed py-12 text-center text-slate-500">No active reminders. They appear when follow-ups are due, visits are planned, or targets need attention.</p>
    @endforelse
</div>

{{ $notifications->links() }}
@endsection

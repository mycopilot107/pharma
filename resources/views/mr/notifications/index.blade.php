@extends('layouts.mr')

@section('title', 'Reminders')

@section('mr-content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold">Reminders & alerts</h1>
        <p class="text-sm text-slate-500">Follow-ups, meetings, targets & doctor revisits</p>
    </div>
    @if ($unreadCount > 0)
        <form method="POST" action="{{ route('mr.notifications.read-all') }}">
            @csrf
            <button type="submit" class="text-xs text-teal-700 font-medium">Mark all read</button>
        </form>
    @endif
</div>

<div class="mt-4 flex flex-wrap gap-2 text-xs">
    <a href="{{ route('mr.notifications.index') }}" class="rounded-full px-3 py-1 {{ !request('type') ? 'bg-teal-600 text-white' : 'bg-slate-100' }}">All</a>
    @foreach (\App\Enums\ReminderType::cases() as $type)
        <a href="{{ route('mr.notifications.index', ['type' => $type->value]) }}" class="rounded-full px-3 py-1 {{ request('type') === $type->value ? 'bg-teal-600 text-white' : 'bg-slate-100' }}">
            {{ $type->icon() }} {{ $type->label() }}
        </a>
    @endforeach
</div>

<div class="mt-4 space-y-2">
    @forelse ($notifications as $notification)
        <div class="rounded-xl border p-4 {{ $notification->type->color() }} {{ $notification->isUnread() ? 'ring-1 ring-teal-300' : 'opacity-75' }}">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-slate-500">{{ $notification->type->icon() }} {{ $notification->type->label() }}
                        @if ($notification->priority === 'high')<span class="text-red-600">· Urgent</span>@endif
                    </p>
                    <p class="font-medium text-slate-900 mt-0.5">{{ $notification->title }}</p>
                    <p class="text-sm text-slate-600 mt-1">{{ $notification->body }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $notification->remind_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($notification->action_url)
                    <a href="{{ $notification->action_url }}" class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-medium text-white">Open</a>
                @endif
                @if ($notification->isUnread())
                    <form method="POST" action="{{ route('mr.notifications.read', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg border bg-white px-3 py-1.5 text-xs">Mark read</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('mr.notifications.dismiss', $notification) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-slate-500 hover:text-slate-700">Dismiss</button>
                </form>
            </div>
        </div>
    @empty
        <p class="py-12 text-center text-sm text-slate-500">No reminders right now. You're all caught up!</p>
    @endforelse
</div>

{{ $notifications->links() }}
@endsection

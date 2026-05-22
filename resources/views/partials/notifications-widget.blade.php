@if (isset($reminders) && $reminders->isNotEmpty())
<section class="mt-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-800">🔔 Reminders</h2>
        <a href="{{ $notificationsRoute ?? route('mr.notifications.index') }}" class="text-xs text-teal-700">View all</a>
    </div>
    <div class="space-y-2">
        @foreach ($reminders as $notification)
            <a href="{{ $notification->action_url ?? ($notificationsRoute ?? '#') }}" class="block rounded-xl border p-3 {{ $notification->type->color() }} hover:ring-1 hover:ring-teal-200 {{ $notification->isUnread() ? 'ring-1 ring-teal-300' : '' }}">
                <p class="text-xs text-slate-500">{{ $notification->type->icon() }} {{ $notification->type->label() }}</p>
                <p class="text-sm font-medium text-slate-900">{{ $notification->title }}</p>
                <p class="text-xs text-slate-600 line-clamp-2">{{ $notification->body }}</p>
            </a>
        @endforeach
    </div>
</section>
@endif

@auth
@php
    $bellRoute = auth()->user()->isRepresentative()
        ? route('mr.notifications.index')
        : route('admin.notifications.index');
    $bellCountRoute = auth()->user()->isRepresentative()
        ? route('mr.notifications.count')
        : route('admin.notifications.count');
@endphp
<a href="{{ $bellRoute }}" class="relative rounded-lg p-2 text-slate-600 hover:bg-slate-100" title="Reminders">
    <span class="text-lg">🔔</span>
    @if (($notificationUnreadCount ?? 0) > 0)
        <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
            {{ $notificationUnreadCount > 9 ? '9+' : $notificationUnreadCount }}
        </span>
    @endif
</a>
@endauth

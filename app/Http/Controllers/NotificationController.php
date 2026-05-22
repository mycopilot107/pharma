<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

abstract class NotificationController extends Controller
{
    public function __construct(protected ReminderService $reminders) {}

    protected function syncIfNeeded(): void
    {
        $user = Auth::user();
        Cache::remember(
            'reminders_sync:'.$user->id,
            now()->addMinutes(5),
            function () use ($user) {
                $this->reminders->syncForUser($user);

                return true;
            }
        );
    }

    public function index(Request $request)
    {
        $this->syncIfNeeded();
        $user = Auth::user();

        $notifications = UserNotification::where('user_id', $user->id)
            ->active()
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->filter === 'unread', fn ($q) => $q->unread())
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderBy('remind_at')
            ->paginate(25)
            ->withQueryString();

        return view($this->viewPrefix().'.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $this->reminders->unreadCount($user->id),
        ]);
    }

    public function unreadCount()
    {
        $this->syncIfNeeded();

        return response()->json([
            'count' => $this->reminders->unreadCount(Auth::id()),
        ]);
    }

    public function markRead(UserNotification $notification)
    {
        $this->authorizeNotification($notification);
        $notification->markRead();

        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead()
    {
        UserNotification::where('user_id', Auth::id())
            ->active()
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function dismiss(UserNotification $notification)
    {
        $this->authorizeNotification($notification);
        $notification->update(['dismissed_at' => now(), 'read_at' => now()]);

        return back()->with('success', 'Notification dismissed.');
    }

    protected function authorizeNotification(UserNotification $notification): void
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
    }

    abstract protected function viewPrefix(): string;
}

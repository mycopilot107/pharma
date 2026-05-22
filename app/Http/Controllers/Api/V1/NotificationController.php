<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function __construct(protected ReminderService $reminders) {}

    public function index(Request $request)
    {
        $this->syncIfNeeded($request);

        $notifications = UserNotification::where('user_id', $request->user()->id)
            ->active()
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->filter === 'unread', fn ($q) => $q->unread())
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderBy('remind_at')
            ->paginate(25);

        return NotificationResource::collection($notifications)->additional([
            'unread_count' => $this->reminders->unreadCount($request->user()->id),
        ]);
    }

    public function unreadCount(Request $request)
    {
        $this->syncIfNeeded($request);

        return response()->json([
            'count' => $this->reminders->unreadCount($request->user()->id),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification)
    {
        $this->authorizeNotification($notification, $request);
        $notification->markRead();

        return new NotificationResource($notification->fresh());
    }

    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', $request->user()->id)
            ->active()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function dismiss(Request $request, UserNotification $notification)
    {
        $this->authorizeNotification($notification, $request);
        $notification->update(['dismissed_at' => now(), 'read_at' => now()]);

        return response()->json(['message' => 'Notification dismissed.']);
    }

    protected function syncIfNeeded(Request $request): void
    {
        $user = $request->user();
        Cache::remember(
            'reminders_sync:'.$user->id,
            now()->addMinutes(5),
            function () use ($user) {
                $this->reminders->syncForUser($user);

                return true;
            }
        );
    }

    protected function authorizeNotification(UserNotification $notification, Request $request): void
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}

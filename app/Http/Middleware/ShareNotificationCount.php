<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\ReminderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareNotificationCount
{
    public function __construct(protected ReminderService $reminders) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === UserRole::SuperAdmin) {
                View::share('notificationUnreadCount', 0);

                return $next($request);
            }

            Cache::remember(
                'reminders_sync:'.$user->id,
                now()->addMinutes(5),
                function () use ($user) {
                    $this->reminders->syncForUser($user);

                    return true;
                }
            );

            View::share('notificationUnreadCount', $this->reminders->unreadCount($user->id));
        }

        return $next($request);
    }
}

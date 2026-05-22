<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiRepresentative
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== UserRole::Representative || ! $user->is_active) {
            return response()->json(['message' => 'Unauthorized. Field representative access required.'], 403);
        }

        if (! $user->company?->isActive()) {
            return response()->json(['message' => 'Your company subscription is inactive.'], 403);
        }

        return $next($request);
    }
}

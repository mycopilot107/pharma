<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRepresentative
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== UserRole::Representative || ! $user->is_active) {
            return redirect()->route('login')
                ->with('error', 'Please sign in as a medical representative.');
        }

        if (! $user->company?->isActive()) {
            return redirect()->route('login')
                ->with('error', 'Your company subscription is inactive.');
        }

        return $next($request);
    }
}

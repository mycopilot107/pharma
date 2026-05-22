<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== UserRole::SuperAdmin || ! $user->is_active) {
            return redirect()->route('login')
                ->with('error', 'Please sign in as a platform super administrator.');
        }

        return $next($request);
    }
}

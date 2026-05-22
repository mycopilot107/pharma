<?php

namespace App\Http\Middleware;

use App\Support\Currency;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->company) {
            Currency::fromCompany(Auth::user()->company);
        } elseif ($request->old('currency') && Currency::isSupported($request->old('currency'))) {
            Currency::set($request->old('currency'));
        }

        View::share('currencyCode', Currency::code());
        View::share('currencySymbol', Currency::symbol());

        return $next($request);
    }
}

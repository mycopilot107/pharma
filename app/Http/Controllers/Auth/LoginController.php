<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account is not active yet.',
            ]);
        }

        if ($user->role === UserRole::SuperAdmin) {
            $request->session()->regenerate();

            return redirect()->intended(route('super-admin.dashboard'));
        }

        if (! in_array($user->role, [UserRole::CompanyAdmin, UserRole::Representative], true)) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Invalid account type.',
            ]);
        }

        if (! $user->company?->isActive()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your company subscription is not active.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(
            $user->role === UserRole::Representative
                ? route('mr.dashboard')
                : route('dashboard')
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

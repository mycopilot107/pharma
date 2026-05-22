<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your account is not active yet.'],
            ]);
        }

        if ($user->role !== UserRole::Representative) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['This app is for field representatives only.'],
            ]);
        }

        if (! $user->company?->isActive()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your company subscription is not active.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('company.plan')),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load('company.plan'));
    }
}

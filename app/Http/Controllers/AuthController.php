<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            ...$request->validated(),
            'role' => User::ROLE_USER,
        ]);

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => 'Registratie gelukt. Controleer je e-mail om je account te bevestigen.',
        ], 201);
    }

    public function login(LoginRequest $request): UserResource|JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'De ingevoerde inloggegevens zijn onjuist.',
            ], 422);
        }

        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            if ($request->hasSession()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'message' => 'Bevestig je e-mailadres voordat je inlogt. Vraag eventueel een nieuwe bevestigingsmail aan.',
                'code' => 'email_unverified',
            ], 403);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Je bent uitgelogd.',
        ]);
    }
}

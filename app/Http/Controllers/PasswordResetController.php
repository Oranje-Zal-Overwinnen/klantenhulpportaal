<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetController extends Controller
{
    public function sendLink(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => 'Als dit e-mailadres bij ons bekend is, ontvang je een e-mail met een resetlink.',
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Je wachtwoord is aangepast. Je kunt nu inloggen.',
            ]);
        }

        return response()->json([
            'message' => 'De resetlink is ongeldig of verlopen.',
        ], 422);
    }
}

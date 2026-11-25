<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResendVerificationEmailRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Throwable;

class EmailVerificationController extends Controller
{
    public function __invoke(int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect('/login?verified=1');
    }

    public function resend(ResendVerificationEmailRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return response()->json([
            'message' => 'Als dit e-mailadres bekend is en nog niet bevestigd, ontvang je een nieuwe bevestigingsmail.',
        ]);
    }
}

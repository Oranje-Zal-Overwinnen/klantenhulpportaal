<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InvalidateUserSessions
{
    /**
     * Invalidate existing sessions and API tokens after a password reset.
     */
    public function handle(PasswordReset $event): void
    {
        try {
            $table = config('session.table', 'sessions');

            if (Schema::hasTable($table)) {
                DB::connection(config('session.connection'))
                    ->table($table)
                    ->where('user_id', $event->user->getKey())
                    ->delete();
            }

            $event->user->tokens()->delete();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

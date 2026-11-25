<?php

namespace App\Policies;

use App\Models\Reply;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReplyPolicy
{
    /**
     * Determine whether the user can add a reply.
     */
    public function create(User $user): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can update the reply.
     */
    public function update(User $user, Reply $reply): Response|bool
    {
        return $this->adminOnly($user);
    }

    private function adminOnly(User $user): Response|bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Response::deny('Alleen administrators kunnen reageren op tickets.');
    }
}

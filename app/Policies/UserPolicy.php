<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view the user overview.
     */
    public function viewAny(User $user): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $target): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $target): Response|bool
    {
        if (! $user->isAdmin()) {
            return Response::deny('Deze actie is alleen toegestaan voor administrators.');
        }

        if ($user->is($target)) {
            return Response::deny('Je kunt je eigen account niet verwijderen.');
        }

        return true;
    }

    private function adminOnly(User $user): Response|bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Response::deny('Deze actie is alleen toegestaan voor administrators.');
    }
}

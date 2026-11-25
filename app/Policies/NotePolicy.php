<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    /**
     * Determine whether the user can view notes.
     */
    public function viewAny(User $user): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can create a note.
     */
    public function create(User $user): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can update the note.
     */
    public function update(User $user, Note $note): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can delete the note.
     */
    public function delete(User $user, Note $note): Response|bool
    {
        return $this->adminOnly($user);
    }

    private function adminOnly(User $user): Response|bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Response::deny('Notities zijn alleen toegankelijk voor administrators.');
    }
}

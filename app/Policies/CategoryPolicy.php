<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view categories.
     *
     * Every authenticated user needs the category list to create a ticket.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create a category.
     */
    public function create(User $user): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, Category $category): Response|bool
    {
        return $this->adminOnly($user);
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, Category $category): Response|bool
    {
        return $this->adminOnly($user);
    }

    private function adminOnly(User $user): Response|bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Response::deny('Deze actie is alleen toegestaan voor administrators.');
    }
}

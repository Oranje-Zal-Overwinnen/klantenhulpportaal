<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view the ticket overview.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): Response|bool
    {
        if ($user->isAdmin() || $ticket->user_id === $user->id) {
            return true;
        }

        return Response::deny('Je hebt geen toegang tot dit ticket.');
    }

    /**
     * Determine whether the user can create a ticket.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): Response|bool
    {
        if ($user->isAdmin() || $ticket->user_id === $user->id) {
            return true;
        }

        return Response::deny('Je mag alleen je eigen tickets aanpassen.');
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, Ticket $ticket): Response|bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Response::deny('Alleen administrators kunnen tickets toewijzen.');
    }

    /**
     * Determine whether the user can change the ticket status.
     */
    public function updateStatus(User $user, Ticket $ticket): Response|bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Response::deny('Alleen administrators kunnen de status van een ticket wijzigen.');
    }
}

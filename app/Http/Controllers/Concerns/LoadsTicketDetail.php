<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Resources\TicketResource;
use App\Models\Note;
use App\Models\Ticket;
use App\Models\User;

trait LoadsTicketDetail
{
    protected function detailTicket(Ticket $ticket, User $user): TicketResource
    {
        $relations = [
            'creator',
            'assignee',
            'categories',
            'replies' => fn($query) => $query->with('author')->oldest('created_at')->oldest('id'),
        ];

        if ($user->can('viewAny', Note::class)) {
            $relations['notes'] = fn($query) => $query->with('author')->oldest('created_at')->oldest('id');
        }

        $ticket->load($relations);

        return new TicketResource($ticket);
    }
}

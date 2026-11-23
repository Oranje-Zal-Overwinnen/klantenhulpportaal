<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;

trait LoadsTicketDetail
{
    protected function detailTicket(Ticket $ticket): TicketResource
    {
        $relations = [
            'creator',
            'assignee',
            'categories',
            'replies' => fn ($query) => $query->with('author')->oldest('created_at'),
        ];

        if (auth()->user()?->isAdmin()) {
            $relations['notes'] = fn ($query) => $query->with('author')->oldest('created_at');
        }

        $ticket->load($relations);

        return new TicketResource($ticket);
    }
}

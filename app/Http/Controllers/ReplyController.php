<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReplyRequest;
use App\Http\Requests\UpdateReplyRequest;
use App\Http\Resources\TicketResource;
use App\Models\Reply;
use App\Models\Ticket;
use App\Notifications\TicketReplyNotification;

class ReplyController extends Controller
{
    public function store(StoreReplyRequest $request, Ticket $ticket)
    {
        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $ticket->touch();
        $ticket->load('creator');

        if (
            $ticket->creator
            && ! $ticket->creator->trashed()
            && $ticket->creator->id !== $request->user()->id
        ) {
            $ticket->creator->notify(new TicketReplyNotification($ticket, $reply));
        }

        return $this->ticketDetail($ticket);
    }

    public function update(UpdateReplyRequest $request, Reply $reply)
    {
        $reply->update([
            'body' => $request->validated('body'),
        ]);

        $ticket = $reply->ticket;
        $ticket->touch();

        return $this->ticketDetail($ticket);
    }

    private function ticketDetail(Ticket $ticket)
    {
        $ticket->load([
            'creator',
            'assignee',
            'categories',
            'replies' => fn ($query) => $query->with('author')->oldest('created_at'),
        ]);

        return new TicketResource($ticket);
    }
}

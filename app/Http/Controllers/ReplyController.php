<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsTicketDetail;
use App\Http\Requests\StoreReplyRequest;
use App\Http\Requests\UpdateReplyRequest;
use App\Models\Reply;
use App\Models\Ticket;
use App\Notifications\TicketReplyNotification;

class ReplyController extends Controller
{
    use LoadsTicketDetail;

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

        return $this->detailTicket($ticket);
    }

    public function update(UpdateReplyRequest $request, Reply $reply)
    {
        $reply->update([
            'body' => $request->validated('body'),
        ]);

        $ticket = $reply->ticket;
        $ticket->touch();

        return $this->detailTicket($ticket);
    }
}

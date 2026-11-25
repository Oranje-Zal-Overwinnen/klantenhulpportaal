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
        $this->authorize('create', Reply::class);

        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $ticket->touch();
        $ticket->load('creator');

        $creator = $ticket->creator;

        if ($creator && ! $creator->trashed() && $creator->isNot($request->user())) {
            $creator->notify(new TicketReplyNotification($ticket, $reply));
        }

        return $this->detailTicket($ticket, $request->user());
    }

    public function update(UpdateReplyRequest $request, Reply $reply)
    {
        $this->authorize('update', $reply);

        $reply->update([
            'body' => $request->validated('body'),
        ]);

        $ticket = $reply->ticket;
        $ticket->touch();

        return $this->detailTicket($ticket, $request->user());
    }
}

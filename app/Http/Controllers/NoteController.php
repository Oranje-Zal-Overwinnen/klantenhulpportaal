<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsTicketDetail;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Ticket;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    use LoadsTicketDetail;

    public function store(StoreNoteRequest $request, Ticket $ticket)
    {
        $this->authorize('create', Note::class);

        $ticket->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $ticket->touch();

        return $this->detailTicket($ticket, $request->user());
    }

    public function update(UpdateNoteRequest $request, Note $note)
    {
        $this->authorize('update', $note);

        $note->update([
            'body' => $request->validated('body'),
        ]);

        $ticket = $note->ticket;
        $ticket->touch();

        return $this->detailTicket($ticket, $request->user());
    }

    public function destroy(Request $request, Note $note)
    {
        $this->authorize('delete', $note);

        $ticket = $note->ticket;
        $note->delete();
        $ticket->touch();

        return $this->detailTicket($ticket, $request->user());
    }
}

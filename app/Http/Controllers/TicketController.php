<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        return $this->allTickets($request);
    }

    public function store(StoreTicketRequest $request)
    {
        $data = $request->validated();
        $categoryIds = $data['categories'] ?? [];
        unset($data['categories']);

        $data['user_id'] = $request->user()->id;
        $data['status'] = Ticket::STATUS_PENDING;

        $ticket = Ticket::create($data);
        $ticket->categories()->sync($categoryIds);

        return $this->allTickets($request);
    }

    public function show(Request $request, Ticket $ticket)
    {
        if (! $request->user()->isAdmin() && $ticket->user_id !== $request->user()->id) {
            abort(403, 'Je hebt geen toegang tot dit ticket.');
        }

        return $this->detailTicket($ticket);
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        $ticket->update([
            'assigned_to' => $request->input('assigned_to'),
        ]);

        return $this->detailTicket($ticket);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        if (! $request->user()->isAdmin() && $ticket->user_id !== $request->user()->id) {
            abort(403, 'Je mag alleen je eigen tickets aanpassen.');
        }

        $data = $request->validated();
        $categoryIds = $data['categories'] ?? null;
        unset($data['categories']);

        $ticket->update($data);

        if ($categoryIds !== null) {
            $ticket->categories()->sync($categoryIds);
        }

        return $this->allTickets($request);
    }

    private function allTickets(Request $request)
    {
        $tickets = Ticket::query()
            ->with(['creator', 'assignee', 'categories'])
            ->when(! $request->user()->isAdmin(), function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest('created_at')
            ->get();

        return TicketResource::collection($tickets);
    }

    private function detailTicket(Ticket $ticket)
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

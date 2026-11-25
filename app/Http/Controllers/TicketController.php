<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsTicketDetail;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    use LoadsTicketDetail;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        return $this->allTickets($request);
    }

    public function store(StoreTicketRequest $request)
    {
        $this->authorize('create', Ticket::class);

        DB::transaction(function () use ($request) {
            $ticket = Ticket::create([
                ...$request->safe()->except('categories'),
                'user_id' => $request->user()->id,
                'status' => Ticket::STATUS_PENDING,
            ]);

            $ticket->categories()->sync($request->validated('categories') ?? []);
        });

        return $this->allTickets($request);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return $this->detailTicket($ticket, $request->user());
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $ticket->update([
            'assigned_to' => $request->validated('assigned_to'),
        ]);

        return $this->detailTicket($ticket, $request->user());
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket)
    {
        $this->authorize('updateStatus', $ticket);

        $ticket->update([
            'status' => $request->validated('status'),
        ]);

        return $this->detailTicket($ticket, $request->user());
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $ticket->update($request->safe()->except('categories'));

            $categoryIds = $request->validated('categories');

            if (is_array($categoryIds)) {
                $ticket->categories()->sync($categoryIds);
            }
        });

        return $this->allTickets($request);
    }

    private function allTickets(Request $request)
    {
        $tickets = Ticket::query()
            ->with(['creator', 'assignee', 'categories'])
            ->visibleTo($request->user())
            ->latest('created_at')
            ->latest('id')
            ->get();

        return TicketResource::collection($tickets);
    }
}

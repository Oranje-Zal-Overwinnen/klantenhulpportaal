<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
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
}

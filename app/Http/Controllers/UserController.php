<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return $this->allUsers();
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return $this->allUsers();
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'Je kunt je eigen account niet verwijderen.',
            ], 422);
        }

        $unresolvedTickets = Ticket::query()
            ->where('status', '!=', Ticket::STATUS_RESOLVED)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('assigned_to', $user->id);
            })
            ->count();

        if ($unresolvedTickets > 0) {
            return response()->json([
                'message' => 'Deze gebruiker kan niet worden verwijderd omdat er nog niet-afgehandelde tickets aan gekoppeld zijn.',
                'tickets_count' => $unresolvedTickets,
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Gebruiker succesvol verwijderd.',
        ]);
    }

    private function allUsers()
    {
        return UserResource::collection(
            User::query()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
        );
    }
}

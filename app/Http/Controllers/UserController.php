<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Ticket;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return $this->allUsers();
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return $this->allUsers();
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

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

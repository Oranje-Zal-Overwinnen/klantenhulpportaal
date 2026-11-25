<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        $data = $request->validated();

        if ($user->isLastAdmin() && $data['role'] !== User::ROLE_ADMIN) {
            return response()->json([
                'message' => 'Er moet minimaal één administrator actief blijven.',
                'errors' => [
                    'role' => ['Er moet minimaal één administrator actief blijven.'],
                ],
            ], 422);
        }

        $user->update($data);

        return $this->allUsers();
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $unresolvedTickets = DB::transaction(function () use ($user) {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $unresolvedTickets = Ticket::query()
                ->where('status', '!=', Ticket::STATUS_RESOLVED)
                ->where(function ($query) use ($lockedUser) {
                    $query->where('user_id', $lockedUser->id)
                        ->orWhere('assigned_to', $lockedUser->id);
                })
                ->count();

            if ($unresolvedTickets > 0) {
                return $unresolvedTickets;
            }

            $lockedUser->delete();

            return 0;
        });

        if ($unresolvedTickets > 0) {
            return response()->json([
                'message' => 'Deze gebruiker kan niet worden verwijderd omdat er nog niet-afgehandelde tickets aan gekoppeld zijn.',
                'tickets_count' => $unresolvedTickets,
            ], 422);
        }

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

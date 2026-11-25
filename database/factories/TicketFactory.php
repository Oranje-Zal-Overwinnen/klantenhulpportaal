<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => Ticket::STATUS_PENDING,
            'user_id' => User::factory(),
            'assigned_to' => null,
        ];
    }

    /**
     * Indicate that the ticket is assigned to an administrator.
     */
    public function assignedTo(User $admin): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $admin->id,
        ]);
    }

    /**
     * Indicate that the ticket has been resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_RESOLVED,
        ]);
    }
}

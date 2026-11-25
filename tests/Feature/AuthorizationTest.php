<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Note;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\TicketReplyNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function ticketFor(User $owner): Ticket
    {
        return Ticket::factory()->create(['user_id' => $owner->id]);
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = $this->ticketFor($owner);

        Sanctum::actingAs($other);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Je hebt geen toegang tot dit ticket.');
    }

    public function test_admin_can_view_any_ticket(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = $this->ticketFor(User::factory()->create());

        Sanctum::actingAs($admin);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id);
    }

    public function test_owner_can_update_own_ticket(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticketFor($owner);

        Sanctum::actingAs($owner);

        $this->putJson("/api/tickets/{$ticket->id}", [
            'title' => 'Aangepaste titel',
            'description' => 'Aangepaste omschrijving',
            'categories' => [],
        ])->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Aangepaste titel',
        ]);
    }

    public function test_user_cannot_update_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = $this->ticketFor($owner);

        Sanctum::actingAs($other);

        $this->putJson("/api/tickets/{$ticket->id}", [
            'title' => 'Niet toegestaan',
            'description' => 'Niet toegestaan',
            'categories' => [],
        ])->assertForbidden();
    }

    public function test_non_admin_cannot_assign_a_ticket(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticketFor($owner);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/tickets/{$ticket->id}/assignment", [
            'assigned_to' => null,
        ])->assertForbidden();
    }

    public function test_admin_can_assign_a_ticket_to_an_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = $this->ticketFor(User::factory()->create());

        Sanctum::actingAs($admin);

        $this->patchJson("/api/tickets/{$ticket->id}/assignment", [
            'assigned_to' => $admin->id,
        ])->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_manage_categories(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/categories', ['name' => 'Netwerk'])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['name' => 'Netwerk']);
    }

    public function test_admin_can_manage_categories(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->postJson('/api/categories', ['name' => 'Netwerk'])
            ->assertOk();

        $this->assertDatabaseHas('categories', ['name' => 'Netwerk']);
    }

    public function test_non_admin_cannot_create_notes_or_replies(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticketFor($owner);

        Sanctum::actingAs($owner);

        $this->postJson("/api/tickets/{$ticket->id}/notes", ['body' => 'Interne notitie'])
            ->assertForbidden();

        $this->postJson("/api/tickets/{$ticket->id}/replies", ['body' => 'Reactie'])
            ->assertForbidden();

        $this->assertDatabaseCount('notes', 0);
        $this->assertDatabaseCount('replies', 0);
    }

    public function test_admin_can_create_notes_and_replies(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $ticket = $this->ticketFor(User::factory()->create());

        Sanctum::actingAs($admin);

        $this->postJson("/api/tickets/{$ticket->id}/notes", ['body' => 'Interne notitie'])
            ->assertOk();

        $this->postJson("/api/tickets/{$ticket->id}/replies", ['body' => 'Reactie'])
            ->assertOk();

        $this->assertDatabaseHas('notes', ['ticket_id' => $ticket->id, 'body' => 'Interne notitie']);
        $this->assertDatabaseHas('replies', ['ticket_id' => $ticket->id, 'body' => 'Reactie']);

        Notification::assertSentTo($ticket->creator, TicketReplyNotification::class);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/users/{$admin->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Je kunt je eigen account niet verwijderen.');
    }

    public function test_admin_cannot_delete_a_user_with_unresolved_tickets(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $this->ticketFor($user);

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/users/{$user->id}")
            ->assertUnprocessable()
            ->assertJsonPath('tickets_count', 1);
    }

    public function test_registration_sends_a_verification_email_and_verifies_the_user(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'first_name' => 'Sanne',
            'last_name' => 'de Vries',
            'email' => 'sanne@example.com',
            'phone_number' => '0612345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $user = User::where('email', 'sanne@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmailNotification::class);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect('/login?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_note_can_be_updated_and_deleted_by_an_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = $this->ticketFor(User::factory()->create());
        $note = Note::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $this->putJson("/api/notes/{$note->id}", ['body' => 'Aangepast'])
            ->assertOk();

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'body' => 'Aangepast']);

        $this->deleteJson("/api/notes/{$note->id}")
            ->assertOk();

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_non_admin_cannot_update_or_delete_a_note(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticketFor($user);
        $note = Note::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => User::factory()->admin()->create()->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/notes/{$note->id}", ['body' => 'Niet toegestaan'])
            ->assertForbidden();

        $this->deleteJson("/api/notes/{$note->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('notes', ['id' => $note->id]);
    }

    public function test_reply_can_only_be_updated_by_an_admin(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticketFor($user);
        $reply = Reply::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => User::factory()->admin()->create()->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/replies/{$reply->id}", ['body' => 'Niet toegestaan'])
            ->assertForbidden();

        $this->assertDatabaseMissing('replies', ['id' => $reply->id, 'body' => 'Niet toegestaan']);
    }

    public function test_categories_are_visible_to_regular_users(): void
    {
        $user = User::factory()->create();
        Category::factory()->create(['name' => 'Hardware']);

        Sanctum::actingAs($user);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Hardware');
    }

    public function test_ticket_overview_only_returns_own_tickets_for_regular_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownTicket = $this->ticketFor($owner);
        $this->ticketFor($other);

        Sanctum::actingAs($owner);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownTicket->id);
    }

    public function test_ticket_overview_returns_all_tickets_for_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $this->ticketFor(User::factory()->create());
        $this->ticketFor(User::factory()->create());

        Sanctum::actingAs($admin);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_non_admin_cannot_update_ticket_status(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticketFor($owner);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_RESOLVED,
        ])->assertForbidden();
    }

    public function test_admin_can_update_ticket_status(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = $this->ticketFor(User::factory()->create());

        Sanctum::actingAs($admin);

        $this->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_RESOLVED,
        ])->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => Ticket::STATUS_RESOLVED,
        ]);
    }

    public function test_category_with_tickets_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $ticket = $this->ticketFor(User::factory()->create());
        $ticket->categories()->attach($category);

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/categories/{$category->id}")
            ->assertUnprocessable()
            ->assertJsonPath('tickets_count', 1);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_notes_are_hidden_from_regular_users(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticketFor($owner);
        Note::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => User::factory()->admin()->create()->id,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonMissingPath('data.notes');
    }

    public function test_regular_users_do_not_see_assignee_contact_details(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'phone_number' => '0612345678',
        ]);
        $ticket = Ticket::factory()->create([
            'user_id' => $owner->id,
            'assigned_to' => $admin->id,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.assignee.first_name', $admin->first_name)
            ->assertJsonMissingPath('data.assignee.email')
            ->assertJsonMissingPath('data.assignee.phone_number');
    }

    public function test_admins_see_assignee_contact_details(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => User::factory()->create()->id,
            'assigned_to' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.assignee.email', $admin->email)
            ->assertJsonPath('data.assignee.phone_number', $admin->phone_number);
    }

    public function test_database_seeder_creates_an_admin_and_a_regular_user(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@klantenhulp.nl',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user@klantenhulp.nl',
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'De ingevoerde inloggegevens zijn onjuist.');
    }

    public function test_password_reset_flow(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_reset_with_an_invalid_token_fails(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'De resetlink is ongeldig of verlopen.');
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->unverified()->create();

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertForbidden()
            ->assertJsonPath('code', 'email_unverified');

        $this->assertGuest();
    }

    public function test_unverified_user_can_request_a_new_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->postJson('/api/email/verification-notification', [
            'email' => $user->email,
        ])->assertOk();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_verification_resend_is_generic_for_unknown_email(): void
    {
        Notification::fake();

        $this->postJson('/api/email/verification-notification', [
            'email' => 'unknown@example.com',
        ])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_verified_user_is_not_sent_another_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/email/verification-notification', [
            'email' => $user->email,
        ])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_last_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->putJson("/api/users/{$admin->id}", [
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'role' => User::ROLE_USER,
            'phone_number' => $admin->phone_number,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Er moet minimaal één administrator actief blijven.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_admin_can_demote_themselves_when_another_admin_exists(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->putJson("/api/users/{$admin->id}", [
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'role' => User::ROLE_USER,
            'phone_number' => $admin->phone_number,
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_admin_can_delete_another_admin_when_one_remains(): void
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->admin()->create();

        Sanctum::actingAs($actor);

        $this->deleteJson("/api/users/{$target->id}")
            ->assertOk();

        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_category_can_be_deleted_when_no_tickets_are_linked(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/categories/{$category->id}")
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_verification_email_is_queued(): void
    {
        Queue::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        Queue::assertPushed(SendQueuedNotifications::class);
    }

    public function test_reply_notification_is_queued(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $ticket = $this->ticketFor($owner);

        Sanctum::actingAs($admin);

        $this->postJson("/api/tickets/{$ticket->id}/replies", [
            'body' => 'We hebben je ticket in behandeling genomen.',
        ])->assertOk();

        Queue::assertPushed(SendQueuedNotifications::class);
    }
}

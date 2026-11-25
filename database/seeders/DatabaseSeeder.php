<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Note;
use App\Models\Reply;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'first_name' => 'Beheerder',
            'last_name' => 'Admin',
            'email' => 'admin@klantenhulp.nl',
            'phone_number' => '0612345678',
        ]);

        $user = User::factory()->create([
            'first_name' => 'Sanne',
            'last_name' => 'de Vries',
            'email' => 'user@klantenhulp.nl',
            'phone_number' => '0687654321',
        ]);

        $otherUsers = User::factory()->count(3)->create();

        $categories = collect([
            'Hardware',
            'Software',
            'Netwerk',
            'Account',
            'Overig',
        ])->mapWithKeys(fn(string $name) => [
            $name => Category::factory()->create(['name' => $name]),
        ]);

        $tickets = collect([
            [
                'title' => 'Laptop start niet op',
                'description' => 'Sinds vanochtend start mijn laptop niet meer op. Het scherm blijft zwart.',
                'status' => Ticket::STATUS_PENDING,
                'user_id' => $user->id,
                'assigned_to' => null,
                'categories' => ['Hardware'],
            ],
            [
                'title' => 'E-mail werkt niet na wachtwoordwijziging',
                'description' => 'Ik kan sinds het wijzigen van mijn wachtwoord geen e-mail meer ontvangen.',
                'status' => Ticket::STATUS_IN_PROGRESS,
                'user_id' => $user->id,
                'assigned_to' => $admin->id,
                'categories' => ['Software'],
            ],
            [
                'title' => 'VPN-verbinding valt steeds weg',
                'description' => 'De VPN-verbinding verbreekt meerdere keren per dag automatisch.',
                'status' => Ticket::STATUS_RESOLVED,
                'user_id' => $user->id,
                'assigned_to' => $admin->id,
                'categories' => ['Netwerk'],
            ],
            [
                'title' => 'Wachtwoord vergeten',
                'description' => 'Ik ben mijn wachtwoord vergeten en kan niet meer inloggen.',
                'status' => Ticket::STATUS_PENDING,
                'user_id' => $otherUsers[0]->id,
                'assigned_to' => null,
                'categories' => ['Account'],
            ],
            [
                'title' => 'Printer op afdeling 2 werkt niet',
                'description' => 'Printer geeft een papierstoring aan, maar er zit geen papier vast.',
                'status' => Ticket::STATUS_IN_PROGRESS,
                'user_id' => $otherUsers[1]->id,
                'assigned_to' => $admin->id,
                'categories' => ['Hardware', 'Netwerk'],
            ],
            [
                'title' => 'Nieuwe software installeren',
                'description' => 'Graag wil ik de nieuwste versie van het kantoorsoftwarepakket installeren.',
                'status' => Ticket::STATUS_RESOLVED,
                'user_id' => $otherUsers[2]->id,
                'assigned_to' => $admin->id,
                'categories' => ['Software'],
            ],
            [
                'title' => 'Geen toegang tot gedeelde map',
                'description' => 'Ik krijg een foutmelding wanneer ik de gedeelde map van mijn afdeling open.',
                'status' => Ticket::STATUS_PENDING,
                'user_id' => $otherUsers[0]->id,
                'assigned_to' => null,
                'categories' => ['Account', 'Netwerk'],
            ],
        ])->map(function (array $data) use ($categories): Ticket {
            $categoryNames = $data['categories'];
            unset($data['categories']);

            $ticket = Ticket::factory()->create($data);
            $ticket->categories()->attach($categories->only($categoryNames)->pluck('id'));

            return $ticket;
        });

        Reply::factory()->create([
            'ticket_id' => $tickets[1]->id,
            'user_id' => $admin->id,
            'body' => 'We hebben het probleem in behandeling genomen en onderzoeken de e-mailinstellingen.',
        ]);

        Reply::factory()->create([
            'ticket_id' => $tickets[1]->id,
            'user_id' => $user->id,
            'body' => 'Bedankt voor de update. Ik wacht het resultaat af.',
        ]);

        Reply::factory()->create([
            'ticket_id' => $tickets[4]->id,
            'user_id' => $admin->id,
            'body' => 'Er is een monteur langs geweest en de printer is weer operationeel.',
        ]);

        Note::factory()->create([
            'ticket_id' => $tickets[1]->id,
            'user_id' => $admin->id,
            'body' => 'Controleer eerst de Exchange-connector voordat de gebruiker wordt gecontacteerd.',
        ]);

        Note::factory()->create([
            'ticket_id' => $tickets[2]->id,
            'user_id' => $admin->id,
            'body' => 'VPN-profiel opnieuw uitgerold; ticket kan worden afgesloten.',
        ]);
    }
}

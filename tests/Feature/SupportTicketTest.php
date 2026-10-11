<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 500,
            'bank' => 1000,
            'stars' => 10,
            'respect' => 10,
        ], $attrs));
    }

    public function test_user_can_view_support_tickets_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('help.tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('Support Tickets');
        $response->assertSee('Nieuw Ticket Aanmaken');
    }

    public function test_user_can_create_a_support_ticket(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $response = $this->actingAs($user)->post(route('help.tickets.store'), [
            'subject' => 'Vraag over de isolatiecellen in Open Beta',
            'category' => 'vraag',
            'priority' => 'normaal',
            'message' => 'Hoeveel seconden zit je vast in de isoleercel bij een mislukte kluiskraak?',
        ]);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'character_id' => $char->id,
            'subject' => 'Vraag over de isolatiecellen in Open Beta',
            'category' => 'vraag',
            'status' => 'open',
        ]);

        $ticket = SupportTicket::first();
        $this->assertNotNull($ticket);
        $this->assertCount(1, $ticket->messages);
        $this->assertEquals('Hoeveel seconden zit je vast in de isoleercel bij een mislukte kluiskraak?', $ticket->messages->first()->message);

        $response->assertRedirect(route('help.tickets.show', $ticket));
    }

    public function test_user_can_reply_to_their_ticket(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'character_id' => $char->id,
            'ticket_number' => 'TBS-ABC-123',
            'subject' => 'Bug report test',
            'category' => 'bug',
            'priority' => 'hoog',
            'status' => 'open',
        ]);

        $ticket->messages()->create([
            'user_id' => $user->id,
            'character_id' => $char->id,
            'is_staff' => false,
            'message' => 'Eerste bericht van speler.',
        ]);

        $response = $this->actingAs($user)->post(route('help.tickets.reply', $ticket), [
            'message' => 'Hier is aanvullende informatie over de bug.',
        ]);

        $response->assertSessionHas('success');
        $this->assertCount(2, $ticket->fresh()->messages);
        $this->assertEquals('open', $ticket->fresh()->status);
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        $this->createCharacter($user1);

        /** @var User $user2 */
        $user2 = User::factory()->create();
        $char2 = $this->createCharacter($user2);

        $ticket = SupportTicket::create([
            'user_id' => $user2->id,
            'character_id' => $char2->id,
            'ticket_number' => 'TBS-XYZ-999',
            'subject' => 'Geheim ticket van speler 2',
            'category' => 'vraag',
            'priority' => 'normaal',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user1)->get(route('help.tickets.show', $ticket));
        $response->assertStatus(403);
    }

    public function test_staff_can_view_all_tickets_and_reply_in_admin_panel(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_staff' => true, 'email' => 'admin_test@tbskliniek.nl']);
        $this->createCharacter($admin);

        /** @var User $player */
        $player = User::factory()->create(['email' => 'speler@test.nl']);
        $playerChar = $this->createCharacter($player, ['name' => 'KliniekBoef']);

        $ticket = SupportTicket::create([
            'user_id' => $player->id,
            'character_id' => $playerChar->id,
            'ticket_number' => 'TBS-101-555',
            'subject' => 'Speler heeft hulp nodig',
            'category' => 'bug',
            'priority' => 'hoog',
            'status' => 'open',
        ]);

        $ticket->messages()->create([
            'user_id' => $player->id,
            'character_id' => $playerChar->id,
            'is_staff' => false,
            'message' => 'Mijn koffer opent niet!',
        ]);

        // Staff views tickets list
        $indexResponse = $this->actingAs($admin)->get(route('admin.tickets.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('TBS-101-555');
        $indexResponse->assertSee('KliniekBoef');

        // Staff replies to ticket
        $replyResponse = $this->actingAs($admin)->post(route('admin.tickets.reply', $ticket), [
            'message' => 'Beste KliniekBoef, we hebben dit opgelost. Probeer nu opnieuw.',
            'status' => 'beantwoord',
        ]);

        $replyResponse->assertSessionHas('success');
        $ticket->refresh();
        $this->assertEquals('beantwoord', $ticket->status);
        $this->assertTrue($ticket->last_reply_by_staff);
        $this->assertCount(2, $ticket->messages);
        $this->assertTrue($ticket->messages->last()->is_staff);
    }

    public function test_user_can_close_their_ticket(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'character_id' => $char->id,
            'ticket_number' => 'TBS-CLS-001',
            'subject' => 'Vraag opgelost',
            'category' => 'vraag',
            'priority' => 'laag',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->post(route('help.tickets.close', $ticket));

        $response->assertSessionHas('success');
        $ticket->refresh();
        $this->assertEquals('gesloten', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    public function test_dashboard_displays_open_beta_announcement_banner(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Open Beta Testfase');
        $response->assertSee('TBS Kliniek de Game is momenteel in Open Beta!');
        $response->assertSee(route('help.tickets.create'));
    }
}

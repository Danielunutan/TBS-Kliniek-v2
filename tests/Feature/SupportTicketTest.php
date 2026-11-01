<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
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
            'subject' => 'Vraag over de isolatiecellen',
            'category' => 'vraag',
            'priority' => 'normaal',
            'message' => 'Hoeveel seconden zit je vast in de isoleercel bij een mislukte kluiskraak?',
        ]);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'character_id' => $char->id,
            'subject' => 'Vraag over de isolatiecellen',
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

    public function test_dashboard_does_not_display_open_beta_announcement_banner(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Open Beta Testfase');
        $response->assertDontSee('TBS Kliniek de Game is momenteel in Open Beta!');
    }

    public function test_user_can_submit_gang_transfer_bonus_ticket(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $response = $this->actingAs($user)->post(route('help.tickets.store'), [
            'category' => 'gang_overstap',
            'gang_name' => 'Peaky Blinders',
            'gang_tag' => 'PB',
            'external_gang_link' => 'https://www.barribob.nl/gang/peaky-blinders',
            'member_names' => 'Arthur, Thomas, John',
            'proof_details' => 'Wij zijn al 2 jaar actief op Barribob en willen overstappen.',
            'priority' => 'hoog',
        ]);

        $ticket = SupportTicket::latest('id')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('gang_overstap', $ticket->category);
        $this->assertEquals('Bende Overstap Bonus: Peaky Blinders [PB]', $ticket->subject);
        $this->assertEquals('Peaky Blinders', $ticket->metadata['gang_name']);
        $this->assertEquals('PB', $ticket->metadata['gang_tag']);
        $this->assertEquals('https://www.barribob.nl/gang/peaky-blinders', $ticket->metadata['external_gang_link']);
        $this->assertStringContainsString('Arthur, Thomas, John', $ticket->metadata['member_names']);
        $response->assertRedirect(route('help.tickets.show', $ticket));
    }

    public function test_staff_can_approve_gang_transfer_bonus_and_auto_create_gang_with_treasury_and_vip(): void
    {
        /** @var User $leaderUser */
        $leaderUser = User::factory()->create();
        $leader = $this->createCharacter($leaderUser, ['name' => 'TommyShelby']);

        /** @var User $memberUser */
        $memberUser = User::factory()->create();
        $member = $this->createCharacter($memberUser, ['name' => 'ArthurShelby']);

        /** @var User $admin */
        $admin = User::factory()->create(['is_staff' => true, 'name' => 'Daniel']);
        $this->createCharacter($admin, ['name' => 'AdminDaniel']);

        // Create ticket
        $ticket = SupportTicket::create([
            'user_id' => $leaderUser->id,
            'character_id' => $leader->id,
            'ticket_number' => 'TBS-GNG-777',
            'subject' => 'Gang Overstap Bonus: Peaky Blinders [PB]',
            'category' => 'gang_overstap',
            'priority' => 'hoog',
            'status' => 'open',
            'metadata' => [
                'gang_name' => 'Peaky Blinders',
                'gang_tag' => 'PB',
                'external_gang_link' => 'https://www.barribob.nl/gang/peaky-blinders',
                'member_names' => 'ArthurShelby',
                'proof_details' => 'Top 3 gang op Barribob',
            ],
        ]);

        // Staff approves gang transfer
        $approveResponse = $this->actingAs($admin)->post(route('admin.tickets.approve_gang', $ticket), [
            'gang_name' => 'Peaky Blinders',
            'gang_tag' => 'PB',
            'bonus_cash' => 5000000,
            'vip_days' => 31,
        ]);

        $approveResponse->assertSessionHas('success');

        // Verify Gang created
        $gang = Gang::where('name', 'Peaky Blinders')->first();
        $this->assertNotNull($gang);
        $this->assertEquals('PB', $gang->tag);
        $this->assertEquals(5000000, $gang->treasury);
        $this->assertEquals($leader->id, $gang->leader_character_id);
        $this->assertTrue($gang->hasActiveVipBoost());

        // Verify Leader assigned and has VIP
        $leader->refresh();
        $this->assertEquals($gang->id, $leader->gang_id);
        $this->assertEquals('leader', $leader->gang_role);
        $this->assertTrue($leader->isVip());

        // Verify Member received VIP and joined gang
        $member->refresh();
        $this->assertEquals($gang->id, $member->gang_id);
        $this->assertTrue($member->isVip());

        // Verify ticket marked approved and replied to
        $ticket->refresh();
        $this->assertEquals('beantwoord', $ticket->status);
        $this->assertTrue($ticket->isGangTransferApproved());
        $this->assertCount(1, $ticket->messages);
        $this->assertTrue($ticket->messages->first()->is_staff);
        $this->assertStringContainsString('€ 5.000.000', $ticket->messages->first()->message);
        $this->assertStringContainsString('31 dagen', $ticket->messages->first()->message);
    }
}

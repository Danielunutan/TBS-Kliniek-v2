<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket5AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_index_escapes_script_tags_in_character_names(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'ChatSpeler',
        ]);

        $otherUser = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $otherUser->id,
            'name' => '</script><script>alert(1)</script>',
        ]);

        $response = $this->actingAs($user)->get(route('chat.index'));
        $response->assertOk();
        $response->assertDontSee('</script><script>alert(1)</script>', false);
    }

    public function test_admin_ticket_show_renders_approved_gang_transfer_without_timestamp_crash(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        $character = Character::factory()->create([
            'user_id' => $admin->id,
            'name' => 'TicketAdmin',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-90001',
            'user_id' => $admin->id,
            'character_id' => $character->id,
            'category' => 'gang_overstap',
            'subject' => 'Overstap aanvraag',
            'status' => 'beantwoord',
            'priority' => 'normaal',
            'metadata' => [
                'gang_name' => 'De Krijgers',
                'gang_tag' => 'KRG',
                'bonus_approved' => true,
                'created_gang_id' => 1,
                'created_gang_name' => 'De Krijgers',
                'created_gang_tag' => 'KRG',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.tickets.show', $ticket));
        $response->assertOk();
        $response->assertSee('De Krijgers');
    }

    public function test_armwrestling_and_casino_views_disable_actions_when_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 50000,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user)->get(route('armwrestling.index'))
            ->assertOk()
            ->assertSee('Code Blauw (Geen Armworstelen)')
            ->assertSee('disabled', false);

        $this->actingAs($user)->get(route('casino.index'))
            ->assertOk()
            ->assertSee('Math.min(10000000, Math.max(10, cash))', false);
    }

    public function test_login_preserves_avatar_6_on_validation_failure(): void
    {
        $response = $this->withSession([
            '_old_input' => [
                'avatar' => 'assets/characters/avatar_male_6.jpg',
            ],
        ])->get(route('login'));

        $response->assertOk();
        $response->assertSee('assets/characters/avatar_male_6.jpg');
    }

    public function test_battlepass_and_battle_views_use_secure_encoding_and_js(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'level' => 5,
        ]);

        $bpResponse = $this->actingAs($user)->get(route('battlepass.index'));
        $bpResponse->assertOk();
        $bpResponse->assertSee('window.__TBS_BP_DATA__ = JSON.parse(', false);

        $battleResponse = $this->actingAs($user)->get(route('battle.index'));
        $battleResponse->assertOk();
        $battleResponse->assertSee('encodeURIComponent(catKey)', false);
    }

    public function test_cinema_view_guards_comatose_and_restricts_official_checkbox(): void
    {
        $user = User::factory()->create(['is_onboarded' => true, 'role' => 'user']);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'coma_until' => now()->addHours(2),
        ]);

        $response = $this->actingAs($user)->get(route('cinema.index'));
        $response->assertOk();
        $response->assertSee('Code Blauw');
        $response->assertDontSee('Officiële Zaal (TBS Kliniek Hoofdzaal)');

        $admin = User::factory()->create(['is_onboarded' => true, 'role' => 'admin']);
        Character::factory()->create(['user_id' => $admin->id]);

        $adminResponse = $this->actingAs($admin)->get(route('cinema.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Officiële Zaal (TBS Kliniek Hoofdzaal)');
    }
}

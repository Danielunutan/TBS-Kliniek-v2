<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Friend;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditBatch8SecurityAndLogicTest extends TestCase
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
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 10,
            'respect' => 10,
            'title' => 'Modelpatiënt',
        ], $attrs));
    }

    /**
     * Test that profile overview safely handles orphaned/deleted friend records without crashing.
     */
    public function test_profile_overview_handles_orphaned_friend_records_without_crashing(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, ['name' => 'MainPlayer']);

        $otherUser = User::factory()->create();
        $otherChar = $this->createCharacter($otherUser, ['name' => 'DeletedFriend']);

        Friend::create([
            'character_id' => $character->id,
            'friend_id' => $otherChar->id,
            'status' => 'accepted',
            'is_bff' => false,
        ]);

        DB::statement('PRAGMA foreign_keys = OFF');
        $otherChar->delete();
        DB::statement('PRAGMA foreign_keys = ON');

        $response = $this->actingAs($user)->get(route('profile.show', $character->name));

        $response->assertStatus(200);
        $response->assertSee('MainPlayer');
    }

    /**
     * Test that account settings view does not mutate in-memory username_changed_at date.
     */
    public function test_account_settings_view_does_not_mutate_username_changed_at(): void
    {
        $user = User::factory()->create();
        $originalDate = Carbon::now()->subMonths(2)->startOfMinute();
        $character = $this->createCharacter($user, [
            'username_changed_at' => $originalDate,
        ]);

        $response = $this->actingAs($user)->get(route('settings.account'));

        $response->assertStatus(200);
        $character->refresh();
        $this->assertEquals(
            $originalDate->format('Y-m-d H:i'),
            $character->username_changed_at->format('Y-m-d H:i')
        );
    }

    /**
     * Test that shop gifting selector safely encodes special characters in patient names.
     */
    public function test_shop_gifting_selector_safely_encodes_special_characters(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user, ['name' => 'SelfPlayer']);

        $otherUser = User::factory()->create();
        $this->createCharacter($otherUser, ['name' => 'O\'Connor <script>"alert(1)"</script>']);

        $response = $this->actingAs($user)->get(route('shop.index'));

        $response->assertStatus(200);
        $response->assertDontSee("name: 'O'Connor", false);
    }

    /**
     * Test that treatment page handles missing outcome key in treatment_history gracefully.
     */
    public function test_treatment_page_renders_safely_with_missing_outcome_key(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'treatment_history' => [
                'title' => 'Oude Evaluatie',
                'score' => 45,
                'notes' => ['Gedrag stabiel'],
                'date' => '01-01-2026',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('treatment.index'));

        $response->assertStatus(200);
        $response->assertSee('Oude Evaluatie');
        $response->assertSee('Score: 45%');
    }

    /**
     * Test that support ticket messages escape raw HTML entities while formatting markdown.
     */
    public function test_support_ticket_messages_escape_raw_html_while_formatting_markdown(): void
    {
        $user = User::factory()->create(['name' => 'TestGebruiker']);
        $character = $this->createCharacter($user);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'ticket_number' => 'TCK-TEST-001',
            'subject' => 'Hulp gevraagd',
            'category' => 'vraag',
            'priority' => 'normaal',
            'status' => 'open',
        ]);

        $msg = SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'character_id' => $character->id,
            'is_staff' => false,
            'message' => '<script>alert("xss")</script> **Belangrijk bericht**',
        ]);

        $response = $this->actingAs($user)->get(route('help.tickets.show', $ticket));

        $response->assertStatus(200);
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
        $response->assertSee('<strong class="font-extrabold text-slate-900 dark:text-white">Belangrijk bericht</strong>', false);
    }

    /**
     * Test that timeline, vault, and tunnel views render without runtime errors.
     */
    public function test_timeline_vault_and_tunnel_views_render_properly(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $this->actingAs($user)->get(route('timeline.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('vault.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('tunnel.index'))->assertStatus(200);
    }

    /**
     * Test that shop, profile, gangs, and admin moderation views whitelist tab query parameters against Alpine x-data XSS and select a valid default bundle.
     */
    public function test_shop_profile_gangs_and_moderation_views_whitelist_tab_query_parameters_and_default_bundle(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $character = $this->createCharacter($admin, ['name' => 'TabGuardPlayer']);

        $payload = "',alert(1),'";

        // 1. Shop view whitelists tab and defaults selectedBundleId to afdelingsbaas_deal
        $shopRes = $this->actingAs($admin)->get(route('shop.index', ['tab' => $payload]));
        $shopRes->assertStatus(200);
        $shopRes->assertSee("shopTab: 'pakketten'", false);
        $shopRes->assertSee("selectedBundleId: 'afdelingsbaas_deal'", false);
        $shopRes->assertDontSee('alert(1)', false);

        // 2. Profile view whitelists tab and custom_tab
        $profileRes = $this->actingAs($admin)->get(route('profile.show', [
            'name' => $character->name,
            'tab' => $payload,
            'custom_tab' => $payload,
        ]));
        $profileRes->assertStatus(200);
        $profileRes->assertSee("activeTab: 'overzicht'", false);
        $profileRes->assertSee("activeCustomTab: 'sticker'", false);
        $profileRes->assertDontSee('alert(1)', false);

        // 3. Gangs view whitelists tab and beheer_tab
        $gangsRes = $this->actingAs($admin)->get(route('gangs.index', [
            'tab' => $payload,
            'beheer_tab' => $payload,
        ]));
        $gangsRes->assertStatus(200);
        $gangsRes->assertSee("activeGangTab: 'alle_bendes'", false);
        $gangsRes->assertSee("beheerTab: 'leden'", false);
        $gangsRes->assertDontSee('alert(1)', false);

        // 4. Admin moderation view whitelists tab
        $modRes = $this->actingAs($admin)->get(route('admin.moderation.index', ['tab' => $payload]));
        $modRes->assertStatus(200);
        $modRes->assertSee("activeTab: 'bans'", false);
        $modRes->assertDontSee('alert(1)', false);
    }
}

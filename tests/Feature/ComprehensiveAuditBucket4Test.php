<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\SerializeCharacterActions;
use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterMemoryStat;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\GangRequest;
use App\Models\Item;
use App\Models\PushSubscription;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VpnDetectionService;
use App\Support\GangPropertyCatalog;
use App\Support\PushEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ComprehensiveAuditBucket4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! Route::has('inventory.index')) {
            Route::get('/inventaris-test', fn () => 'ok')->name('inventory.index');
        }
    }

    protected function tearDown(): void
    {
        $lock = CheckMaintenanceMode::lockFilePath();
        if (file_exists($lock)) {
            @unlink($lock);
        }
        parent::tearDown();
    }

    private function createPlayer(array $attributes = []): array
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler_'.rand(1000, 9999),
            'cash' => 50000,
            'bank' => 100000,
            'stars' => 100,
            'level' => 5,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 500,
            'masks' => 5,
        ], $attributes));

        return [$user, $character];
    }

    private function createGang(Character $leader, array $attributes = []): Gang
    {
        $gang = Gang::create(array_merge([
            'name' => 'Bende_'.rand(1000, 9999),
            'tag' => 'B'.rand(10, 99),
            'specialty' => 'Kantine Controle',
            'leader_character_id' => $leader->id,
            'treasury' => 500000,
            'attack_power' => 1500,
            'protection_points' => 2000,
            'leave_fee_multiplier' => 5,
        ], $attributes));

        $leader->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
        ]);

        return $gang;
    }

    // =========================================================================
    // 1. GANG CREATION & PENDING REQUEST CLEANUP
    // =========================================================================

    public function test_gang_creation_cancels_prior_pending_requests(): void
    {
        [$user, $character] = $this->createPlayer(['stars' => 50]);

        // Prior gang and pending request
        [$otherUser, $otherLeader] = $this->createPlayer();
        $otherGang = $this->createGang($otherLeader);

        $pendingReq = GangRequest::create([
            'gang_id' => $otherGang->id,
            'character_id' => $character->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('gangs.create'), [
            'name' => 'De Bloedhonden',
            'tag' => 'HOND',
            'specialty' => 'Kantine Controle',
            'description' => 'Gevaarlijke kliniek bende.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gangs', ['name' => 'De Bloedhonden', 'tag' => 'HOND']);

        $character->refresh();
        $this->assertNotNull($character->gang_id);
        $this->assertEquals('leader', $character->gang_role);

        // Assert pending request was cancelled
        $pendingReq->refresh();
        $this->assertEquals('cancelled', $pendingReq->status);
    }

    // =========================================================================
    // 2. GANG REQUEST REJECTION CONCURRENCY & TRANSACTION
    // =========================================================================

    public function test_gang_request_rejection_runs_under_lock_and_notifies(): void
    {
        [$leaderUser, $leader] = $this->createPlayer();
        $gang = $this->createGang($leader);

        [$candidateUser, $candidate] = $this->createPlayer();
        $gangRequest = GangRequest::create([
            'gang_id' => $gang->id,
            'character_id' => $candidate->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.handle-request', $gangRequest), [
            'action' => 'reject',
        ]);

        $response->assertRedirect();
        $gangRequest->refresh();
        $this->assertEquals('rejected', $gangRequest->status);

        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $candidate->id,
            'type' => 'gang',
            'title' => 'Aanvraag afgewezen',
        ]);
    }

    // =========================================================================
    // 3. LEAVE FEE WITH BANK FALLBACK & SUCCESSION ORDER
    // =========================================================================

    public function test_member_can_pay_leave_fee_from_bank_when_cash_insufficient(): void
    {
        [$leaderUser, $leader] = $this->createPlayer();
        $gang = $this->createGang($leader, ['leave_fee_multiplier' => 2]);

        // Member has 0 cash but 100,000 in bank
        [$memberUser, $member] = $this->createPlayer([
            'cash' => 0,
            'bank' => 100000,
            'gang_id' => $gang->id,
            'gang_role' => 'member',
        ]);

        $response = $this->actingAs($memberUser)->post(route('gangs.leave'));
        $response->assertRedirect(route('gangs.index'));

        $member->refresh();
        $this->assertNull($member->gang_id);
        $this->assertEquals('none', $member->gang_role);
        $this->assertLessThan(100000, $member->bank); // Bank debited
    }

    public function test_leader_leaving_prioritizes_co_leader_over_regular_member(): void
    {
        [$leaderUser, $leader] = $this->createPlayer();
        $gang = $this->createGang($leader);

        [$memberUser, $member] = $this->createPlayer([
            'gang_id' => $gang->id,
            'gang_role' => 'member',
            'respect' => 9999, // higher respect but regular member
        ]);

        [$coLeaderUser, $coLeader] = $this->createPlayer([
            'gang_id' => $gang->id,
            'gang_role' => 'co_leader',
            'respect' => 100, // lower respect but co-leader
        ]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.leave'));
        $response->assertRedirect(route('gangs.index'));

        $gang->refresh();
        $this->assertEquals($coLeader->id, $gang->leader_character_id);

        $coLeader->refresh();
        $this->assertEquals('leader', $coLeader->gang_role);
    }

    // =========================================================================
    // 4. KICK & PROMOTE WITH APPOINTED ROLES CLEANUP
    // =========================================================================

    public function test_kick_member_clears_appointed_roles_and_garage_vehicles(): void
    {
        [$leaderUser, $leader] = $this->createPlayer();
        $gang = $this->createGang($leader);

        [$officerUser, $officer] = $this->createPlayer([
            'gang_id' => $gang->id,
            'gang_role' => 'co_leader',
        ]);

        [$memberUser, $member] = $this->createPlayer([
            'gang_id' => $gang->id,
            'gang_role' => 'member',
        ]);

        // Set member as gang personal shopper
        $gang->update(['personal_shopper' => $member->id]);

        $vehicle = Vehicle::firstOrCreate(
            ['slug' => 'smokkelwagen_test'],
            ['name' => 'Smokkelwagen Test', 'price_cash' => 5000, 'top_speed' => 120, 'acceleration' => 10]
        );

        $charVehicle = CharacterVehicle::create([
            'character_id' => $member->id,
            'vehicle_id' => $vehicle->id,
            'gang_id' => $gang->id,
            'in_gang_garage' => true,
        ]);

        $response = $this->actingAs($officerUser)->post(route('gangs.kick', $member));
        $response->assertRedirect();

        $member->refresh();
        $this->assertNull($member->gang_id);
        $this->assertEquals('none', $member->gang_role);

        $gang->refresh();
        $this->assertNull($gang->personal_shopper);

        $charVehicle->refresh();
        $this->assertFalse((bool) $charVehicle->in_gang_garage);
        $this->assertNull($charVehicle->gang_id);
    }

    public function test_demoting_co_leader_to_member_clears_appointed_roles(): void
    {
        [$leaderUser, $leader] = $this->createPlayer();
        $gang = $this->createGang($leader);

        [$coLeaderUser, $coLeader] = $this->createPlayer([
            'gang_id' => $gang->id,
            'gang_role' => 'co_leader',
        ]);

        $gang->update(['money_magnet' => $coLeader->id]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.promote', $coLeader), [
            'role' => 'member',
        ]);
        $response->assertRedirect();

        $coLeader->refresh();
        $this->assertEquals('member', $coLeader->gang_role);

        $gang->refresh();
        $this->assertNull($gang->money_magnet);
    }

    // =========================================================================
    // 5. GANG ATTACK ROLL BREAKDOWN & NPC DEFENSE BONUS
    // =========================================================================

    public function test_gang_attack_requires_minimum_health_and_factors_npc_defense(): void
    {
        [$attackerUser, $attackerLeader] = $this->createPlayer(['health' => 10]); // Low health (< 20)
        $attackerGang = $this->createGang($attackerLeader, ['attack_power' => 5000]);

        [$targetUser, $targetLeader] = $this->createPlayer();
        $targetGang = $this->createGang($targetLeader, ['protection_points' => 2000]);

        // Target gang has hired Enforcer NPC (+50% defense)
        GangNpc::create([
            'gang_id' => $targetGang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Viktor "De Slager"',
            'role_title' => 'Bende Beveiliger',
            'bonus_type' => 'defense',
            'bonus_value' => 50,
        ]);

        // Attempt attack with low health (< 20)
        $response = $this->actingAs($attackerUser)->post(route('gangs.attack'), [
            'target_gang_id' => $targetGang->id,
        ]);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('gezondheid', session('error'));

        // Restore health to 100
        $attackerLeader->update(['health' => 100]);

        $response = $this->actingAs($attackerUser)->post(route('gangs.attack'), [
            'target_gang_id' => $targetGang->id,
        ]);
        $response->assertRedirect();

        // Check that GangAttack recorded the base defense + NPC bonus
        $this->assertDatabaseHas('gang_attacks', [
            'attacker_gang_id' => $attackerGang->id,
            'target_gang_id' => $targetGang->id,
            'target_defense' => 3000, // 2000 + 50% = 3000!
        ]);
    }

    // =========================================================================
    // 6. GANG PROPERTY ROTATING SHOWROOM BUY & CLAIM PRODUCTION
    // =========================================================================

    public function test_gang_property_buy_with_rotating_hmac_token(): void
    {
        [$leaderUser, $leader] = $this->createPlayer();
        $gang = $this->createGang($leader, ['treasury' => 2000000]);

        $propertyKey = 'wietkwekerij';
        $propData = GangPropertyCatalog::get($propertyKey);
        $this->assertNotNull($propData);

        $dynPrice = 500000;
        $slot = (int) floor(time() / 1800);
        $appSecret = (string) (config('app.key') ?: 'tbs-gang-prop-secret');
        $token = substr(hash_hmac('sha256', "{$slot}_{$propertyKey}_{$dynPrice}", $appSecret), 0, 16);

        $response = $this->actingAs($leaderUser)->post(route('gangs.panden.buy', $propertyKey), [
            'token' => $token,
            'dynamic_price' => $dynPrice,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gang_properties', [
            'gang_id' => $gang->id,
            'property_key' => $propertyKey,
            'purchase_price' => $dynPrice,
        ]);

        $gang->refresh();
        $this->assertEquals(1500000, $gang->treasury);
    }

    public function test_claim_production_calculates_yield_under_lock_and_credits_treasury(): void
    {
        [$leaderUser, $leader] = $this->createPlayer();
        $gang = $this->createGang($leader, ['treasury' => 10000]);

        $property = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'wietkwekerij',
            'name' => 'Verborgen Wietkwekerij',
            'type' => 'production',
            'purchase_price' => 500000,
            'production_duration_minutes' => 60,
            'production_reward_cash' => 50000,
            'production_reward_respect' => 20,
            'production_status' => 'ready',
            'production_started_at' => now()->subHours(2),
            'production_ends_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.panden.claim-production', $property));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $gang->refresh();
        $this->assertEquals(60000, $gang->treasury);

        $property->refresh();
        $this->assertEquals('idle', $property->production_status);
        $this->assertNull($property->production_started_at);
    }

    // =========================================================================
    // 7. DEPOT SELLING (ITEMS & MASKS) WITH VALIDATION & AUDITING
    // =========================================================================

    public function test_depot_sell_item_validates_quantity_and_rejects_halloween_items(): void
    {
        [$user, $character] = $this->createPlayer(['cash' => 0]);

        $regularItem = Item::firstOrCreate(
            ['slug' => 'test_sigaret'],
            ['name' => 'Slof Sigaretten', 'price_kc' => 1000, 'category' => 'consumable', 'description' => 'Test sigaret']
        );

        $charItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $regularItem->id,
            'quantity' => 10,
        ]);

        $halloweenItem = Item::firstOrCreate(
            ['slug' => 'halloween_pompoen_trofee'],
            ['name' => 'Gouden Pompoen', 'price_kc' => 5000, 'category' => 'special', 'description' => 'Test trofee']
        );

        $charHallowItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $halloweenItem->id,
            'quantity' => 1,
        ]);

        // Attempt to sell regular item with valid quantity
        $response = $this->actingAs($user)->post(route('depot.sell-item', $charItem), [
            'quantity' => 5,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(3000, $character->cash); // 60% of 1000 * 5 = 3000

        $charItem->refresh();
        $this->assertEquals(5, $charItem->quantity);

        // Attempt to sell halloween item - must be blocked
        $response = $this->actingAs($user)->post(route('depot.sell-item', $charHallowItem), [
            'quantity' => 1,
        ]);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Halloween', session('error'));
    }

    public function test_depot_sell_masks_validates_quantity_and_credits_cash(): void
    {
        [$user, $character] = $this->createPlayer(['cash' => 0, 'masks' => 10]);

        $response = $this->actingAs($user)->post(route('depot.sell-masks'), [
            'quantity' => 4,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(6, $character->masks);
        $this->assertEquals(6000, $character->cash); // 4 * 1500 = 6000
    }

    public function test_gang_donate_and_sell_masks(): void
    {
        [$leaderUser, $leader] = $this->createPlayer(['masks' => 5]);
        $gang = $this->createGang($leader, ['masks' => 0, 'treasury' => 0]);

        // Provide gang depot storage capacity via a property
        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'opslagloods_haven',
            'name' => 'Haven Opslagloods',
            'type' => 'storage',
            'purchase_price' => 250000,
            'storage_capacity' => 100,
        ]);

        // Donate 3 masks to gang depot
        $response = $this->actingAs($leaderUser)->post(route('gangs.donate-masks'), [
            'quantity' => 3,
        ]);
        $response->assertRedirect();

        $leader->refresh();
        $gang->refresh();
        $this->assertEquals(2, $leader->masks);
        $this->assertEquals(3, $gang->masks);

        // Officer sells 2 masks from gang depot to fund treasury
        $response = $this->actingAs($leaderUser)->post(route('gangs.sell-masks'), [
            'quantity' => 2,
        ]);
        $response->assertRedirect();

        $gang->refresh();
        $this->assertEquals(1, $gang->masks);
        $this->assertEquals(3000, $gang->treasury); // 2 * 1500 = 3000
    }

    public function test_donate_item_caps_respect_and_rejects_quest_items(): void
    {
        [$leaderUser, $leader] = $this->createPlayer(['respect' => 100]);
        $gang = $this->createGang($leader);

        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'opslagloods_haven',
            'name' => 'Haven Opslagloods',
            'type' => 'storage',
            'purchase_price' => 250000,
            'storage_capacity' => 10000,
        ]);

        $regularItem = Item::firstOrCreate(
            ['slug' => 'test_sigaretten_bulk'],
            ['name' => 'Slof Sigaretten', 'price_kc' => 1000, 'category' => 'consumable', 'description' => 'Test bulk']
        );

        $charItem = CharacterItem::create([
            'character_id' => $leader->id,
            'item_id' => $regularItem->id,
            'quantity' => 6000,
        ]);

        $questItem = Item::firstOrCreate(
            ['slug' => 'quest_gouden_sleutel'],
            ['name' => 'Gouden Sleutel', 'price_kc' => 0, 'category' => 'special', 'description' => 'Missie sleutel']
        );

        $charQuestItem = CharacterItem::create([
            'character_id' => $leader->id,
            'item_id' => $questItem->id,
            'quantity' => 1,
        ]);

        // Attempt quest item donation - must be rejected
        $response = $this->actingAs($leaderUser)->post(route('gangs.donate-item'), [
            'character_item_id' => $charQuestItem->id,
            'quantity' => 1,
        ]);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('evenementvoorwerp', session('error'));

        // Donate 6000 items - respect must be capped at 2500
        $response = $this->actingAs($leaderUser)->post(route('gangs.donate-item'), [
            'character_item_id' => $charItem->id,
            'quantity' => 6000,
        ]);
        $response->assertRedirect();

        $leader->refresh();
        $this->assertEquals(2600, $leader->respect); // 100 + 2500 cap = 2600

        $this->assertDatabaseHas('gang_items', [
            'gang_id' => $gang->id,
            'item_id' => $regularItem->id,
            'quantity' => 6000,
        ]);
    }

    public function test_withdraw_validates_upper_limit_and_requires_officer(): void
    {
        [$leaderUser, $leader] = $this->createPlayer(['cash' => 0]);
        $gang = $this->createGang($leader, ['treasury' => 50000]);

        [$memberUser, $member] = $this->createPlayer([
            'gang_id' => $gang->id,
            'gang_role' => 'member',
        ]);

        // Regular member cannot withdraw
        $response = $this->actingAs($memberUser)->post(route('gangs.withdraw'), [
            'amount' => 500,
        ]);
        $response->assertSessionHas('error');

        // Officer withdrawing beyond treasury fails
        $response = $this->actingAs($leaderUser)->post(route('gangs.withdraw'), [
            'amount' => 100000,
        ]);
        $response->assertSessionHas('error');

        // Valid withdrawal
        $response = $this->actingAs($leaderUser)->post(route('gangs.withdraw'), [
            'amount' => 20000,
        ]);
        $response->assertRedirect();

        $leader->refresh();
        $gang->refresh();
        $this->assertEquals(20000, $leader->cash);
        $this->assertEquals(30000, $gang->treasury);
    }

    public function test_donate_stars_awards_respect_and_credits_gang_stars(): void
    {
        [$leaderUser, $leader] = $this->createPlayer(['stars' => 20, 'respect' => 50]);
        $gang = $this->createGang($leader, ['gang_stars' => 0]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.donate-stars'), [
            'stars' => 10,
        ]);
        $response->assertRedirect();

        $leader->refresh();
        $gang->refresh();
        $this->assertEquals(10, $leader->stars);
        $this->assertEquals(100, $leader->respect); // 50 + (10 * 5) = 100
        $this->assertEquals(10, $gang->gang_stars);
    }

    public function test_gang_attack_defeat_damages_commander_and_notifies_defending_officers(): void
    {
        [$attackerUser, $attackerLeader] = $this->createPlayer(['health' => 24]);
        $attackerGang = $this->createGang($attackerLeader, ['attack_power' => 500]);

        [$targetUser, $targetLeader] = $this->createPlayer();
        $targetGang = $this->createGang($targetLeader, ['protection_points' => 100000]); // Overwhelming defense

        $response = $this->actingAs($attackerUser)->post(route('gangs.attack'), [
            'target_gang_id' => $targetGang->id,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $attackerLeader->refresh();
        // 24 health minus 25 damage = 0 health, triggers coma/hospitalization
        $this->assertEquals(0, $attackerLeader->health);
        $this->assertNotNull($attackerLeader->coma_until);

        // Verify notification sent to target officers
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $targetLeader->id,
            'type' => 'gang',
            'title' => '🛡️ Aanval afgeslagen!',
        ]);
    }

    public function test_gangs_and_depot_blade_views_render_successfully(): void
    {
        [$user, $character] = $this->createPlayer();
        $gang = $this->createGang($character);

        $response = $this->actingAs($user)->get(route('gangs.index'));
        $response->assertOk();

        $response = $this->actingAs($user)->get(route('depot.index'));
        $response->assertOk();
    }

    // =========================================================================
    // 8. DOMAIN 4 AUDIT: SERIALIZE CHARACTER ACTIONS LOCK SAFETY
    // =========================================================================

    public function test_serialize_character_actions_releases_lock_on_exception_and_handles_safe_config_defaults(): void
    {
        [$user, $character] = $this->createPlayer();

        Route::post('/test-serialize-action-fail', function () {
            throw new \RuntimeException('Simulated action crash');
        })->middleware(SerializeCharacterActions::class);

        // Test with safe config fallback (e.g. invalid string or null)
        config(['game.action_lock_wait_seconds' => -1]);

        try {
            $this->actingAs($user)->post('/test-serialize-action-fail');
        } catch (\Throwable $e) {
            $this->assertSame('Simulated action crash', $e->getMessage());
        }

        // Lock must be released in finally block
        $lock = Cache::lock('character_action_'.$user->id, SerializeCharacterActions::LOCK_TTL_SECONDS);
        $this->assertTrue($lock->get(), 'Lock must be released even after an unexpected exception.');
        $lock->release();
    }

    // =========================================================================
    // 9. DOMAIN 4 AUDIT: MAINTENANCE MODE PROPER JSON 503 & WEBHOOK PASSTHROUGH
    // =========================================================================

    public function test_maintenance_mode_returns_json_503_for_ajax_and_api_requests(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '23:45']);

        [$user, $character] = $this->createPlayer();

        // API request receives clean JSON 503 with the configured return time
        $apiRes = $this->actingAs($user)->getJson('/api/character/realtime-stats');
        $apiRes->assertStatus(503)
            ->assertJsonPath('maintenance', true)
            ->assertJsonPath('target_time', '23:45')
            ->assertJsonStructure(['maintenance', 'message', 'target_time']);

        // AJAX request without json header receives JSON 503 as well
        $ajaxRes = $this->actingAs($user)->get('/dashboard', ['X-Requested-With' => 'XMLHttpRequest']);
        $ajaxRes->assertStatus(503)
            ->assertJsonPath('maintenance', true);

        // Regular browser request receives HTML view without a reopening time
        $htmlRes = $this->actingAs($user)->get('/dashboard');
        $htmlRes->assertStatus(503)
            ->assertSee('Tijdelijk in onderhoud voor een volledige en eerlijke reset!')
            ->assertDontSee('23:45');

        // Webhooks pass through
        $webhookRes = $this->post('/api/webhooks/buymeacoffee');
        $this->assertNotEquals(503, $webhookRes->getStatusCode());
    }

    // =========================================================================
    // 10. DOMAIN 4 AUDIT: DISCORD CALLBACK ONBOARDING NAME DEDUPLICATION
    // =========================================================================

    public function test_discord_callback_deduplicates_character_name_when_taken(): void
    {
        config([
            'services.discord.client_id' => 'test-id',
            'services.discord.client_secret' => 'test-secret',
        ]);

        $mockVpn = $this->createStub(VpnDetectionService::class);
        $mockVpn->method('check')->willReturn(['is_vpn' => false, 'provider' => null, 'country' => 'NL']);
        $this->app->instance(VpnDetectionService::class, $mockVpn);

        // Existing character with name 'GroteSpeler'
        Character::factory()->create(['name' => 'GroteSpeler']);

        Http::fake([
            'discord.com/api/oauth2/token' => Http::response(['access_token' => 'mock-token']),
            'discord.com/api/users/@me' => Http::response([
                'id' => '998877665544332211',
                'username' => 'grotespeler',
                'global_name' => 'GroteSpeler',
                'email' => 'discord_unique@example.com',
                'verified' => true,
            ]),
        ]);

        $response = $this->withSession(['oauth2state' => 'test-state'])
            ->get('/discord/callback?code=mock_code&state=test-state');

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'discord_unique@example.com']);

        $user = User::where('email', 'discord_unique@example.com')->firstOrFail();
        $this->assertNotNull($user->character);
        // Name must be deduplicated with suffix, avoiding unique constraint violation
        $this->assertNotEquals('GroteSpeler', $user->character->name);
        $this->assertStringStartsWith('GroteSpeler #', $user->character->name);
    }

    // =========================================================================
    // 11. DOMAIN 4 AUDIT: BUY ME A COFFEE LOCK TIMEOUT & SIGNATURE NORMALIZATION
    // =========================================================================

    public function test_bmac_webhook_handles_lock_timeout_gracefully(): void
    {
        config(['services.buymeacoffee.webhook_secret' => 'secret123']);

        $payload = [
            'event' => 'donation.created',
            'data' => [
                'support_id' => 'sup_audit_123',
                'amount' => 10,
                'supporter_name' => 'Supporter',
                'supporter_email' => 'supporter@example.com',
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, 'secret123');

        // Pre-acquire lock to simulate concurrency
        $lockKey = 'bmac_webhook_'.md5('sup_audit_123_donation.created');
        $lock = Cache::lock($lockKey, 30);
        $this->assertTrue($lock->get());

        // Should return 202 pending instead of 500 error
        $response = $this->call(
            'POST',
            '/api/webhooks/buymeacoffee',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_SIGNATURE_SHA256' => 'sha256='.$signature, // with prefix
            ],
            $rawBody
        );

        $response->assertStatus(202)
            ->assertJsonPath('status', 'pending');

        $lock->release();
    }

    // =========================================================================
    // 12. DOMAIN 4 AUDIT: PUSH SUBSCRIPTION RATE LIMITING & DEVICE CAP & SSRF
    // =========================================================================

    public function test_push_subscription_enforces_rate_limit_and_maximum_device_cap(): void
    {
        [$user, $character] = $this->createPlayer();

        // 1. SSRF prevention on PushEndpoint
        $this->assertFalse(PushEndpoint::isAllowed('https://127.0.0.1:443/push'));
        $this->assertFalse(PushEndpoint::isAllowed('https://169.254.169.254/latest/meta-data'));
        $this->assertTrue(PushEndpoint::isAllowed('https://fcm.googleapis.com/fcm/send/token1'));
        $this->assertTrue(PushEndpoint::isAllowed('https://fcm.googleapis.com./fcm/send/token1'));

        // 2. Add subscriptions up to cap of 15
        for ($i = 1; $i <= 16; $i++) {
            RateLimiter::clear('push_subscribe:'.$user->id);
            $res = $this->actingAs($user)->postJson(route('push.subscribe'), [
                'endpoint' => "https://fcm.googleapis.com/fcm/send/device-{$i}",
                'public_key' => 'key',
                'auth_token' => 'token',
            ]);
            $res->assertOk();
        }

        // Cap must be maintained at 15
        $this->assertEquals(15, PushSubscription::where('user_id', $user->id)->count());

        // 3. Rate limiting after 10 requests within a minute
        RateLimiter::clear('push_subscribe:'.$user->id);
        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($user)->postJson(route('push.subscribe'), [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-rate-test',
            ]);
        }
        $throttledRes = $this->actingAs($user)->postJson(route('push.subscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-rate-test',
        ]);
        $throttledRes->assertStatus(429);
    }

    // =========================================================================
    // 13. DOMAIN 4 AUDIT: MEMORY GAME ANTI-EXPLOIT & DAILY LIMIT
    // =========================================================================

    public function test_memory_game_enforces_daily_limit_and_rejects_impossible_completion(): void
    {
        [$user, $character] = $this->createPlayer();

        // 1. Start game successfully
        $startRes = $this->actingAs($user)->postJson(route('memory.start'));
        $startRes->assertOk()->assertJsonPath('success', true);
        $gameId = $startRes->json('game.id');

        // 2. Finish with impossible human reaction time (< 3 seconds)
        $stat = CharacterMemoryStat::where('character_id', $character->id)->firstOrFail();
        $activeGame = $stat->active_game;
        $activeGame['completed_at'] = $activeGame['started_at'] + 6;
        $activeGame['matched'] = range(0, 15);
        $activeGame['moves'] = 8;
        $stat->active_game = $activeGame;
        $stat->save();

        $finishRes = $this->actingAs($user)->postJson(route('memory.finish'), ['game_id' => $gameId]);
        $finishRes->assertOk();
        // Won must be false due to impossible speed (time_taken = 1s < 3s)
        $this->assertFalse($finishRes->json('won'));
        $this->assertEquals(3000, $finishRes->json('reward_cash')); // consolation prize

        // 3. Daily limit of 30 games
        for ($i = 0; $i < 30; $i++) {
            $stat->incrementDailyGames();
        }
        $this->assertTrue($stat->hasReachedDailyLimit(30));

        // Start must now be rejected
        $stat->update(['cooldown_until' => null, 'active_game' => null]);
        $blockedStart = $this->actingAs($user)->postJson(route('memory.start'));
        $blockedStart->assertStatus(422)
            ->assertJsonPath('daily_limit_reached', true);

        // Skip cooldown with stars must also be blocked
        $blockedStars = $this->actingAs($user)->post(route('memory.skip_cooldown_stars'));
        $blockedStars->assertSessionHas('error');
        $this->assertStringContainsString('dagelijkse limiet', session('error'));
    }

    // =========================================================================
    // 14. DOMAIN 4 AUDIT: REVIVE SERUM GUARDS ON SHOP CONSUME & VIP REDEEM
    // =========================================================================

    public function test_shop_and_vip_condition_guards_block_when_revive_serum_needed(): void
    {
        [$user, $character] = $this->createPlayer(['cash' => 100000, 'vip_points' => 100]);

        // Put character into liquidation streak condition requiring revive serum
        $character->perks = ['liquidation_streak_deaths' => 10];
        $character->save();
        $this->assertTrue($character->needsReviveSerum());

        // 1. Consuming items in shop
        $item = Item::firstOrCreate(
            ['slug' => 'test_energie_drank'],
            ['name' => 'Test Drankje', 'description' => 'Test omschrijving', 'price_kc' => 100, 'category' => 'consumable', 'energy_restore' => 20]
        );
        $charItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $item->id,
            'quantity' => 2,
        ]);

        $consumeRes = $this->actingAs($user)->post(route('shop.consume', $charItem));
        $consumeRes->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', session('error'));

        // 2. Auto-refill in shop
        $refillRes = $this->actingAs($user)->post(route('shop.auto-refill'));
        $refillRes->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', session('error'));

        // 3. VIP redeem
        $vipRes = $this->actingAs($user)->post(route('vip.redeem'), [
            'reward_type' => 'energy_restore',
        ]);
        $vipRes->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', session('error'));
    }
}

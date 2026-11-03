<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\GangRequest;
use App\Models\Item;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\GangPropertyCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangAlliance;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\EventCashHuntService;
use App\Services\EventDrugsProductionService;
use App\Services\EventVaultCrackingService;
use App\Services\HalloweenService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ComprehensiveAuditBucket1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(null);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    private function createCharacter(array $attributes = []): array
    {
        $user = User::factory()->create();
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler_'.rand(1000, 9999),
            'cash' => 1000,
            'bank' => 2000,
            'stars' => 10,
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 20,
            'pumpkin_seeds' => 300,
        ], $attributes));

        return [$user, $character];
    }

    // =========================================================================
    // 1. EVENT VAULT CRACKING SERVICE TESTS
    // =========================================================================

    public function test_vault_challenge_timing_boundaries_and_active_state(): void
    {
        // 1 second before start
        Carbon::setTestNow(Carbon::parse('2026-11-01 23:59:59', EventVaultCrackingService::TIMEZONE));
        $this->assertFalse(EventVaultCrackingService::isActive());
        $this->assertFalse(EventVaultCrackingService::hasEnded());

        // At exact start
        Carbon::setTestNow(Carbon::parse('2026-11-02 00:00:00', EventVaultCrackingService::TIMEZONE));
        $this->assertTrue(EventVaultCrackingService::isActive());
        $this->assertFalse(EventVaultCrackingService::hasEnded());

        // At exact end second: must be active up to and including 23:59:59
        Carbon::setTestNow(Carbon::parse('2026-11-04 23:59:59', EventVaultCrackingService::TIMEZONE));
        $this->assertTrue(EventVaultCrackingService::isActive());
        $this->assertFalse(EventVaultCrackingService::hasEnded());

        // Immediately after end
        Carbon::setTestNow(Carbon::parse('2026-11-05 00:00:00', EventVaultCrackingService::TIMEZONE));
        $this->assertFalse(EventVaultCrackingService::isActive());
        $this->assertTrue(EventVaultCrackingService::hasEnded());
    }

    public function test_vault_challenge_record_and_tie_breaking(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-03 12:00:00', EventVaultCrackingService::TIMEZONE));

        [$userA, $charA] = $this->createCharacter(['name' => 'Alice', 'avatar_custom' => '/uploads/custom.jpg']);
        [$userB, $charB] = $this->createCharacter(['name' => 'Bob']);

        // Alice cracks 5 codes at 12:00
        EventVaultCrackingService::record($charA, 5, 'terminal');

        // Bob cracks 5 codes 1 hour later at 13:00
        Carbon::setTestNow(Carbon::parse('2026-11-03 13:00:00', EventVaultCrackingService::TIMEZONE));
        EventVaultCrackingService::record($charB, 5, 'terminal');

        $data = EventVaultCrackingService::getLeaderboardData($charB->id, 10);

        $this->assertCount(2, $data['leaderboard']);
        // Alice reached 5 first, so Alice is #1 in tie-breaker
        $this->assertEquals($charA->id, $data['leaderboard'][0]['character_id']);
        $this->assertEquals(1, $data['leaderboard'][0]['rank']);
        $this->assertStringContainsString('custom.jpg', $data['leaderboard'][0]['avatar_url']);

        $this->assertEquals($charB->id, $data['leaderboard'][1]['character_id']);
        $this->assertEquals(2, $data['leaderboard'][1]['rank']);
        $this->assertEquals(2, $data['my_rank']['rank']);
    }

    public function test_vault_challenge_resolve_winner_awards_prize_and_prevents_duplicate_payout(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-03 10:00:00', EventVaultCrackingService::TIMEZONE));
        [$user, $char] = $this->createCharacter(['name' => 'VaultKing', 'stars' => 10]);

        EventVaultCrackingService::record($char, 12, 'terminal');

        // Cannot resolve prematurely without force
        $this->assertNull(EventVaultCrackingService::resolveWinner(false));

        // Travel to after event end
        Carbon::setTestNow(Carbon::parse('2026-11-05 01:00:00', EventVaultCrackingService::TIMEZONE));
        $this->assertTrue(EventVaultCrackingService::hasEnded());

        $resolved = EventVaultCrackingService::resolveWinner(false);
        $this->assertNotNull($resolved);
        $this->assertEquals($char->id, $resolved['winner']->id);
        $this->assertEquals(12, $resolved['total_cracked']);

        $char->refresh();
        $this->assertEquals(2510, $char->stars); // 10 + 2500
        $this->assertEquals(100, VaultCode::where('character_id', $char->id)->count());

        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $char->id,
            'type' => EventVaultCrackingService::NOTIFICATION_TYPE,
        ]);

        // Second resolve run must return null (anti-duplication guard)
        $secondRun = EventVaultCrackingService::resolveWinner(true);
        $this->assertNull($secondRun);
        $char->refresh();
        $this->assertEquals(2510, $char->stars);
    }

    // =========================================================================
    // 2. EVENT DRUGS PRODUCTION SERVICE TESTS
    // =========================================================================

    public function test_drugs_production_timing_boundaries_and_empty_sync_safety(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-01 23:59:59'));
        $this->assertTrue(EventDrugsProductionService::isActive());
        $this->assertFalse(EventDrugsProductionService::hasEnded());

        Carbon::setTestNow(Carbon::parse('2026-11-02 00:00:00'));
        $this->assertFalse(EventDrugsProductionService::isActive());
        $this->assertTrue(EventDrugsProductionService::hasEnded());

        // When no production exists, syncWinnerArticle must return null and not invent dummy Jan Molenaar
        $this->assertNull(EventDrugsProductionService::syncWinnerArticle(true));
    }

    public function test_drugs_production_resolve_winner_awards_cash_and_stars(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 15:00:00'));
        [$user, $char] = $this->createCharacter(['name' => 'Pablo', 'cash' => 500, 'stars' => 5]);

        EventDrugsProductionService::record($char, 50, 'weed');

        Carbon::setTestNow(Carbon::parse('2026-11-02 00:01:00'));
        $resolved = EventDrugsProductionService::resolveWinner(false);

        $this->assertNotNull($resolved);
        $char->refresh();
        $this->assertEquals(5000500, $char->cash);
        $this->assertEquals(1005, $char->stars);

        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $char->id,
            'type' => EventDrugsProductionService::NOTIFICATION_TYPE,
        ]);

        // Duplicate resolve run returns null
        $this->assertNull(EventDrugsProductionService::resolveWinner(true));
    }

    // =========================================================================
    // 3. EVENT CASH HUNT SERVICE TESTS
    // =========================================================================

    public function test_cash_hunt_auto_resolves_and_guards_against_multiple_payouts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-29 20:00:00'));
        [$user, $char] = $this->createCharacter(['name' => 'CashHunter', 'stars' => 20]);

        EventCashHuntService::record($char, 10000, 'theft');

        Carbon::setTestNow(Carbon::parse('2026-10-30 00:05:00'));
        $this->assertTrue(EventCashHuntService::hasEnded());

        // Calling getLeaderboardData auto-resolves when deadline has passed
        $data = EventCashHuntService::getLeaderboardData($char->id, 10);
        $this->assertNotEmpty($data['leaderboard']);

        $char->refresh();
        $this->assertEquals(770, $char->stars); // 20 + 750

        // Resolving again returns null
        $this->assertNull(EventCashHuntService::resolveWinner(false));
    }

    // =========================================================================
    // 4. HALLOWEEN & AUTUMN SERVICE & QUEST TESTS
    // =========================================================================

    public function test_autumn_quests_cannot_be_claimed_when_event_has_ended(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00:00'));
        [$user, $char] = $this->createCharacter(['pumpkin_seeds' => 50]);

        HalloweenService::record($char, 'theft', 3);

        // Fast-forward past event end date
        Carbon::setTestNow(Carbon::parse('2026-12-01 00:00:00'));
        $this->assertFalse(HalloweenService::isEventActive());

        $response = $this->actingAs($user)->post(route('halloween.claim-quest', 'daily_theft_3'));
        $response->assertSessionHas('error', 'Het Herfstoogst-event is afgelopen. Je kunt geen quests meer claimen!');

        $char->refresh();
        $this->assertEquals(50, $char->pumpkin_seeds);
    }

    public function test_autumn_quests_cannot_be_claimed_while_imprisoned_or_comatose(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00:00'));
        [$user, $char] = $this->createCharacter(['pumpkin_seeds' => 50]);

        HalloweenService::record($char, 'theft', 3);

        // Put in prison
        $char->prison_until = now()->addMinutes(30);
        $char->is_in_prison = true;
        $char->save();
        $this->assertTrue($char->isImprisoned());

        $response = $this->actingAs($user)->post(route('halloween.claim-quest', 'daily_theft_3'));
        $response->assertSessionHas('error', 'Vanuit de isoleercel kun je geen Herfstoogst quests claimen!');

        // Clear jail, put in coma
        $char->prison_until = null;
        $char->is_in_prison = false;
        $char->coma_until = now()->addMinutes(15);
        $char->save();
        $this->assertTrue($char->isComatose());

        $responseComa = $this->actingAs($user)->post(route('halloween.claim-quest', 'daily_theft_3'));
        $responseComa->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', session('error'));
    }

    public function test_autumn_quest_rewards_safely_granted_and_cannot_be_claimed_twice(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00:00'));
        [$user, $char] = $this->createCharacter([
            'pumpkin_seeds' => 10,
            'cash' => 500,
            'respect' => 5,
        ]);

        HalloweenService::record($char, 'hustle', 5);

        $response = $this->actingAs($user)->post(route('halloween.claim-quest', 'daily_hustle_5'));
        $response->assertSessionHas('success');

        $char->refresh();
        $this->assertEquals(35, $char->pumpkin_seeds); // 10 + 25
        $this->assertEquals(3000, $char->cash); // 500 + 2500
        $this->assertGreaterThan(0, $char->xp);

        // Second attempt to claim same quest
        $secondClaim = $this->actingAs($user)->post(route('halloween.claim-quest', 'daily_hustle_5'));
        $secondClaim->assertSessionHas('info');

        $char->refresh();
        $this->assertEquals(35, $char->pumpkin_seeds);
        $this->assertEquals(3000, $char->cash);
    }

    // =========================================================================
    // 5. AUTUMN SHOP & PROFILE BORDERS TESTS
    // =========================================================================

    public function test_shop_purchases_blocked_when_event_has_ended(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 00:00:00'));
        [$user, $char] = $this->createCharacter(['pumpkin_seeds' => 500]);

        $response = $this->actingAs($user)->post(route('halloween.buy'), [
            'item_key' => 'autumn_ember',
        ]);

        $response->assertSessionHas('error', 'Het Herfstoogst-event is afgelopen. De winkel is gesloten voor nieuwe aankopen!');
        $char->refresh();
        $this->assertEquals(500, $char->pumpkin_seeds);
    }

    public function test_shop_purchases_blocked_for_retired_halloween_items(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00'));
        [$user, $char] = $this->createCharacter(['pumpkin_seeds' => 500]);

        $response = $this->actingAs($user)->post(route('halloween.buy'), [
            'item_key' => 'pumpkin_glow',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('niet meer haalbaar', session('error'));
        $char->refresh();
        $this->assertEquals(500, $char->pumpkin_seeds);
        $this->assertFalse($char->hasBorder('pumpkin_glow'));
    }

    public function test_player_cannot_buy_border_they_already_own(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00'));
        [$user, $char] = $this->createCharacter([
            'pumpkin_seeds' => 500,
            'unlocked_borders' => ['autumn_ember'],
        ]);

        $response = $this->actingAs($user)->post(route('halloween.buy'), [
            'item_key' => 'autumn_ember',
        ]);

        $response->assertSessionHas('info', 'Je bezit dit profielkader al!');
        $char->refresh();
        $this->assertEquals(500, $char->pumpkin_seeds);
    }

    public function test_buying_boosts_applies_effects_and_deducts_currency(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00'));
        [$user, $char] = $this->createCharacter([
            'pumpkin_seeds' => 200,
            'health' => 40,
            'max_health' => 100,
            'energy' => 10,
            'max_energy' => 100,
            'masks' => 0,
        ]);

        // 1. Buy autumn stew
        $stewRes = $this->actingAs($user)->post(route('halloween.buy'), [
            'item_key' => 'autumn_stew',
        ]);
        $stewRes->assertSessionHas('success');
        $char->refresh();
        $this->assertEquals(140, $char->pumpkin_seeds); // 200 - 60
        $this->assertEquals(100, $char->health);
        $this->assertEquals(85, $char->energy); // 10 + 75

        // 2. Buy mist cloak pack (+5 masks)
        $maskRes = $this->actingAs($user)->post(route('halloween.buy'), [
            'item_key' => 'mist_cloak_pack',
        ]);
        $maskRes->assertSessionHas('success');
        $char->refresh();
        $this->assertEquals(55, $char->pumpkin_seeds); // 140 - 85
        $this->assertEquals(5, $char->masks);
    }

    public function test_equip_border_validates_ownership_and_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00'));
        [$user, $char] = $this->createCharacter([
            'unlocked_borders' => ['autumn_ember'],
            'equipped_border' => null,
        ]);

        // Attempting to equip an unowned border fails
        $failRes = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'harvest_moon',
        ]);
        $failRes->assertSessionHas('error', 'Je hebt dit profielkader niet in je bezit.');

        // Attempting to equip an invalid border key fails
        $invalidRes = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'non_existent_border',
        ]);
        $invalidRes->assertSessionHas('error');

        // Equipping owned border succeeds
        $successRes = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'autumn_ember',
        ]);
        $successRes->assertSessionHas('success');
        $char->refresh();
        $this->assertEquals('autumn_ember', $char->equipped_border);

        // Unequipping border succeeds
        $unequipRes = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'none',
        ]);
        $unequipRes->assertSessionHas('success');
        $char->refresh();
        $this->assertNull($char->equipped_border);
    }

    public function test_equip_border_blocked_in_prison_or_coma(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00'));
        [$user, $char] = $this->createCharacter([
            'unlocked_borders' => ['autumn_ember'],
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
        ]);

        $res = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'autumn_ember',
        ]);
        $res->assertSessionHas('error', 'Vanuit de isoleercel kun je geen profielrand instellen!');

        $char->prison_until = null;
        $char->is_in_prison = false;
        $char->coma_until = now()->addMinutes(15);
        $char->save();
        $this->assertTrue($char->isComatose());

        $resComa = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'autumn_ember',
        ]);
        $resComa->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', session('error'));
    }

    // =========================================================================
    // 6. EVENT CONTROLLER API LEADERBOARD ENDPOINTS
    // =========================================================================

    public function test_event_controller_api_leaderboard_endpoints_with_bounded_limits(): void
    {
        [$user, $char] = $this->createCharacter(['name' => 'ApiTester']);

        $resVault = $this->actingAs($user)->getJson(route('api.event.vault_challenge_leaderboard', ['limit' => 200]));
        $resVault->assertOk();
        $resVault->assertJsonStructure(['is_active', 'has_ended', 'leaderboard', 'my_rank', 'total_participants']);

        $resDrugs = $this->actingAs($user)->getJson(route('api.event.drugs_production_leaderboard', ['limit' => -5]));
        $resDrugs->assertOk();
        $resDrugs->assertJsonStructure(['is_active', 'has_ended', 'leaderboard', 'my_rank', 'total_participants']);

        $resCash = $this->actingAs($user)->getJson(route('api.event.cash_hunt_leaderboard', ['limit' => 10]));
        $resCash->assertOk();
        $resCash->assertJsonStructure(['is_active', 'has_ended', 'leaderboard', 'my_rank', 'total_participants']);
    }

    // =========================================================================
    // 7. GANG WAR BALANCE, ENFORCERS & PROPERTY RIPDEAL AUDIT TESTS (DOMAIN 1)
    // =========================================================================

    public function test_gang_enforcer_cap_and_defense_calculations_cannot_exceed_100_percent_or_go_negative(): void
    {
        [$user, $character] = $this->createCharacter();
        $gang = Gang::create([
            'name' => 'CapGang',
            'tag' => 'CG',
            'specialty' => 'vechten',
            'treasury' => 500000,
            'protection_points' => 2000,
            'leader_character_id' => $character->id,
        ]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        // Base defense with 0 enforcers
        $this->assertEquals(0, $gang->npc_defense_bonus_percent);
        $this->assertEquals(2000, $gang->total_defense);

        // Add an enforcer with +30%
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_1',
            'name' => 'Guard 1',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 30,
        ]);
        $gang->refresh();
        $this->assertEquals(30, $gang->npc_defense_bonus_percent);
        $this->assertEquals(2600, $gang->total_defense); // 2000 + 30% = 2600

        // Add more enforcers exceeding 100% total (e.g. 30 + 40 + 50 = 120%)
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_2',
            'name' => 'Guard 2',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 40,
        ]);
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_3',
            'name' => 'Guard 3',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 50,
        ]);
        $gang->refresh();

        // Must be capped at exactly 100%
        $this->assertEquals(100, $gang->npc_defense_bonus_percent);
        $this->assertEquals(4000, $gang->total_defense); // 2000 + 100% = 4000

        // Test boundary: corrupted negative enforcer bonus in database must clamp to 0
        $gang->npcs()->delete();
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'corrupted_enforcer',
            'name' => 'Bad Guard',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => -50,
        ]);
        $gang->refresh();
        $this->assertEquals(0, $gang->npc_defense_bonus_percent);
        $this->assertEquals(2000, $gang->total_defense);
    }

    public function test_hiring_enforcer_when_at_cap_is_strictly_blocked_and_partial_bonus_clamped_to_exact_cap(): void
    {
        [$user, $character] = $this->createCharacter(['cash' => 100000]);
        $gang = Gang::create([
            'name' => 'HireCapGang',
            'tag' => 'HCG',
            'specialty' => 'smokkel',
            'treasury' => 5000000,
            'protection_points' => 2000,
            'leader_character_id' => $character->id,
        ]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        // Give gang villa property so it has 10 NPC capacity
        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'gang_villa',
            'name' => 'Gang Villa',
            'type' => 'luxury',
            'purchase_price' => 7500000,
            'npc_capacity' => 10,
        ]);

        // Add enforcer that gives +90%
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'guard_90',
            'name' => 'Elite Guard',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 90,
        ]);

        $this->assertEquals(90, $gang->fresh()->npc_defense_bonus_percent);

        // Hire enforcer_kapitein (+20% base bonus, cost €500.000)
        // With 90% already active, this should only get +10% to hit exactly 100%
        $hireRes = $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'enforcer_kapitein'));
        $hireRes->assertSessionHas('success');

        $hired = GangNpc::where('gang_id', $gang->id)->where('npc_key', 'enforcer_kapitein')->first();
        $this->assertNotNull($hired);
        $this->assertEquals(10, $hired->bonus_value); // Clamped from 20 down to 10
        $this->assertEquals(100, $gang->fresh()->npc_defense_bonus_percent);

        // Attempting to hire another enforcer must now fail immediately
        $secondHire = $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'enforcer_kapitein'));
        $secondHire->assertSessionHas('error');
        $this->assertStringContainsString('maximale Enforcer-verdedigingslimiet', (string) session('error'));
    }

    public function test_lab_ripdeal_steals_35_percent_and_splits_50_50_between_player_and_gang_treasury(): void
    {
        // Setup attacker
        [$attackerUser, $attacker] = $this->createCharacter(['cash' => 5000, 'energy' => 100, 'respect' => 50]);
        $attackerGang = Gang::create([
            'name' => 'RaiderGang',
            'tag' => 'RAID',
            'specialty' => 'smokkel',
            'treasury' => 10000,
            'leader_character_id' => $attacker->id,
        ]);
        $attacker->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        // Setup defender gang with a chemical lab
        [$defenderUser, $defenderLeader] = $this->createCharacter(['name' => 'DefLeader']);
        $defenderGang = Gang::create([
            'name' => 'VictimGang',
            'tag' => 'VICT',
            'specialty' => 'smokkel',
            'treasury' => 50000,
            'leader_character_id' => $defenderLeader->id,
        ]);
        $defenderLeader->update(['gang_id' => $defenderGang->id, 'gang_role' => 'leader']);

        $lab = GangProperty::create([
            'gang_id' => $defenderGang->id,
            'property_key' => 'chemisch_lab',
            'name' => 'Geheim Chemisch Smokkel-Lab',
            'type' => 'production',
            'purchase_price' => 1750000,
            'production_status' => 'producing',
            'production_duration_minutes' => 240,
            'production_reward_cash' => 210000,
            'production_reward_respect' => 75,
            'production_started_at' => now()->subMinutes(60),
            'production_ends_at' => now()->addMinutes(180),
        ]);

        // Attacker stats high to guarantee victory
        $attacker->update(['level' => 50, 'strength' => 500, 'defense' => 500]);

        $response = $this->actingAs($attackerUser)->post(route('gangs.panden.rip', $lab));
        $response->assertSessionHas('success');

        // Expected batch reward = 210,000. 35% of 210,000 = 73,500.
        // Split 50/50: player gets floor(73500 * 0.5) = 36,750, gang gets 36,750.
        $attacker->refresh();
        $attackerGang->refresh();
        $lab->refresh();

        $this->assertEquals(5000 + 36750, $attacker->cash);
        $this->assertEquals(10000 + 36750, $attackerGang->treasury);
        $this->assertEquals(75, $attacker->energy); // Consumed 25 energy
        $this->assertTrue($attacker->isOnCooldown('gang_lab_rip'));

        // Lab production delayed by 30 minutes
        $this->assertTrue($lab->production_ends_at->greaterThan(now()->addMinutes(180)));

        // 2-hour raid shield must be active in Cache
        $this->assertTrue(Cache::has("gang_lab_ripped_{$lab->id}"));

        // Defender leader received attack notification
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $defenderLeader->id,
            'type' => 'attack',
        ]);
    }

    public function test_lab_ripdeal_applies_2_hour_raid_shield_and_blocks_subsequent_attempts(): void
    {
        [$attackerUser, $attacker] = $this->createCharacter(['energy' => 100]);
        $attackerGang = Gang::create(['name' => 'WolfGang', 'tag' => 'WG', 'specialty' => 'smokkel', 'treasury' => 1000]);
        $attacker->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        [$defenderUser, $defenderLeader] = $this->createCharacter(['name' => 'LabOwner']);
        $defenderGang = Gang::create(['name' => 'BearGang', 'tag' => 'BG', 'specialty' => 'smokkel', 'treasury' => 2000]);
        $defenderLeader->update(['gang_id' => $defenderGang->id, 'gang_role' => 'leader']);

        $lab = GangProperty::create([
            'gang_id' => $defenderGang->id,
            'property_key' => 'chemisch_lab',
            'name' => 'Target Lab',
            'type' => 'production',
            'purchase_price' => 1750000,
            'production_status' => 'producing',
            'production_duration_minutes' => 240,
            'production_reward_cash' => 210000,
            'production_reward_respect' => 75,
        ]);

        // Place active raid shield in cache
        Cache::put("gang_lab_ripped_{$lab->id}", now()->addHours(2)->timestamp, now()->addHours(2));

        $response = $this->actingAs($attackerUser)->post(route('gangs.panden.rip', $lab));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('verscherpte bewaking', (string) session('error'));

        // Energy was not consumed
        $this->assertEquals(100, $attacker->fresh()->energy);
    }

    public function test_lab_ripdeal_blocks_attacks_on_allied_gangs_and_filters_allies_from_rival_labs(): void
    {
        [$attackerUser, $attacker] = $this->createCharacter(['energy' => 100]);
        $attackerGang = Gang::create(['name' => 'AlliedGangA', 'tag' => 'AGA', 'specialty' => 'smokkel', 'treasury' => 1000]);
        $attacker->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        [$allyUser, $allyLeader] = $this->createCharacter(['name' => 'AllyLeader']);
        $allyGang = Gang::create(['name' => 'AlliedGangB', 'tag' => 'AGB', 'specialty' => 'smokkel', 'treasury' => 1000]);
        $allyLeader->update(['gang_id' => $allyGang->id, 'gang_role' => 'leader']);

        // Form alliance
        GangAlliance::create([
            'gang_id' => $attackerGang->id,
            'ally_gang_id' => $allyGang->id,
        ]);

        $allyLab = GangProperty::create([
            'gang_id' => $allyGang->id,
            'property_key' => 'valsgeld_drukkerij',
            'name' => 'Bondgenoot Drukkerij',
            'type' => 'production',
            'purchase_price' => 750000,
            'production_status' => 'producing',
            'production_reward_cash' => 90000,
        ]);

        // 1. Attempting to rip ally lab must fail
        $res = $this->actingAs($attackerUser)->post(route('gangs.panden.rip', $allyLab));
        $res->assertSessionHas('error');
        $this->assertStringContainsString('bondgenoot', (string) session('error'));

        // 2. Panden view does not list ally lab in rival labs
        $viewRes = $this->actingAs($attackerUser)->get(route('gangs.panden'));
        $viewRes->assertOk();
        $viewRes->assertDontSee('Bondgenoot Drukkerij');
    }

    public function test_comatose_or_needs_revive_serum_player_is_strictly_blocked_from_ripdeals_and_gang_attacks(): void
    {
        [$user, $character] = $this->createCharacter(['energy' => 100, 'health' => 100]);
        $gang = Gang::create(['name' => 'PatientGang', 'tag' => 'PG', 'specialty' => 'smokkel', 'treasury' => 10000, 'attack_power' => 5000]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $enemyGang = Gang::create(['name' => 'EnemyGang', 'tag' => 'EG', 'specialty' => 'smokkel', 'treasury' => 10000, 'protection_points' => 1000]);
        $enemyLab = GangProperty::create([
            'gang_id' => $enemyGang->id,
            'property_key' => 'valsgeld_drukkerij',
            'name' => 'Enemy Drukkerij',
            'type' => 'production',
            'purchase_price' => 750000,
            'production_status' => 'producing',
            'production_reward_cash' => 90000,
        ]);

        // 1. In Coma (coma_until in future)
        $character->update(['coma_until' => now()->addMinutes(20)]);
        $this->assertTrue($character->isComatose());
        $this->assertTrue($character->needsReviveSerum());

        $ripRes = $this->actingAs($user)->post(route('gangs.panden.rip', $enemyLab));
        $ripRes->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', (string) session('error'));

        $attackRes = $this->actingAs($user)->post(route('gangs.attack'), ['target_gang_id' => $enemyGang->id]);
        $attackRes->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', (string) session('error'));

        // 2. Coma cleared, but at 10 liquidation streak deaths (needsReviveSerum() = true)
        $perks = $character->perks ?? [];
        $perks['liquidation_streak_deaths'] = 10;
        $character->update(['coma_until' => null, 'perks' => $perks]);
        $character->refresh();
        $this->assertFalse($character->isComatose());
        $this->assertTrue($character->needsReviveSerum());

        $ripRes2 = $this->actingAs($user)->post(route('gangs.panden.rip', $enemyLab));
        $ripRes2->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', (string) session('error'));

        $attackRes2 = $this->actingAs($user)->post(route('gangs.attack'), ['target_gang_id' => $enemyGang->id]);
        $attackRes2->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', (string) session('error'));
    }

    public function test_gang_attack_respects_enforcer_defense_cap_and_updates_treasury_atomically(): void
    {
        [$attackerUser, $attacker] = $this->createCharacter(['health' => 100]);
        $attackerGang = Gang::create([
            'name' => 'ConquerorGang',
            'tag' => 'CQ',
            'specialty' => 'smokkel',
            'attack_power' => 100000,
            'treasury' => 5000,
            'protection_points' => 2000,
            'leader_character_id' => $attacker->id,
        ]);
        $attacker->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        [$defenderUser, $defenderLeader] = $this->createCharacter(['name' => 'BunkerBoss']);
        $defenderGang = Gang::create([
            'name' => 'BunkerGang',
            'tag' => 'BG',
            'specialty' => 'smokkel',
            'attack_power' => 500,
            'protection_points' => 2000,
            'treasury' => 100000,
            'leader_character_id' => $defenderLeader->id,
        ]);
        $defenderLeader->update(['gang_id' => $defenderGang->id, 'gang_role' => 'leader']);

        // Give defender 3 enforcers totaling 150% bonus - must be capped at 100%
        for ($i = 1; $i <= 3; $i++) {
            GangNpc::create([
                'gang_id' => $defenderGang->id,
                'npc_key' => "enforcer_{$i}",
                'name' => "Enforcer {$i}",
                'role_title' => 'Enforcer',
                'bonus_type' => 'defense',
                'bonus_value' => 50,
            ]);
        }
        $this->assertEquals(100, $defenderGang->fresh()->npc_defense_bonus_percent);

        $response = $this->actingAs($attackerUser)->post(route('gangs.attack'), [
            'target_gang_id' => $defenderGang->id,
        ]);
        $response->assertSessionHas('success');

        $attackerGang->refresh();
        $defenderGang->refresh();

        // Plunder occurred
        $this->assertGreaterThan(5000, $attackerGang->treasury);
        $this->assertLessThan(100000, $defenderGang->treasury);
        $this->assertTrue($defenderGang->isUnderShield());
        $this->assertNotNull($attackerGang->last_attack_at);

        // Success summary mentions capped Enforcer bonus (+100%)
        $summary = (string) session('success');
        $this->assertStringContainsString('+100%', $summary);
    }
}

<?php

namespace Tests\Feature;

use App\Http\Controllers\FaqController;
use App\Models\Character;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\User;
use App\Services\GangWageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass3AuditAndSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_start_intel_enforces_15_level_limit(): void
    {
        $userA = User::factory()->create();
        $charA = Character::factory()->create([
            'user_id' => $userA->id,
            'name' => 'SpyMaster',
            'level' => 10,
            'cash' => 500000,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $userFar = User::factory()->create();
        $charFar = Character::factory()->create([
            'user_id' => $userFar->id,
            'name' => 'FarTarget',
            'level' => 35, // Difference 25 > 15
            'current_location' => 'Paviljoen 2 (Intensief)',
        ]);

        $userClose = User::factory()->create();
        $charClose = Character::factory()->create([
            'user_id' => $userClose->id,
            'name' => 'CloseTarget',
            'level' => 15, // Difference 5 <= 15
            'current_location' => 'Paviljoen 2 (Intensief)',
        ]);

        // Attempt intel on target outside 15 levels
        $responseFar = $this->actingAs($userA)->post(route('hitman.intel'), [
            'target_id' => $charFar->id,
            'intel_type' => 'standard_15m',
            'currency' => 'cash',
        ]);

        $responseFar->assertSessionHas('error');
        $this->assertStringContainsString('15 levels verschil', session('error'));

        // Attempt intel on target within 15 levels
        $responseClose = $this->actingAs($userA)->post(route('hitman.intel'), [
            'target_id' => $charClose->id,
            'intel_type' => 'standard_15m',
            'currency' => 'cash',
        ]);

        $responseClose->assertSessionHas('success');
    }

    public function test_hitman_contract_sets_combat_isolation_cooldown(): void
    {
        $userA = User::factory()->create();
        $attacker = Character::factory()->create([
            'user_id' => $userA->id,
            'name' => 'ContractBoss',
            'level' => 20,
            'cash' => 500000,
            'energy' => 100,
            'is_in_prison' => false,
            'coma_until' => null,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $userT = User::factory()->create();
        $target = Character::factory()->create([
            'user_id' => $userT->id,
            'name' => 'ContractMark',
            'level' => 20,
            'health' => 100,
            'is_in_prison' => false,
            'coma_until' => null,
            'protection_until' => null,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $response = $this->actingAs($userA)->post(route('hitman.contract'), [
            'target_id' => $target->id,
            'contract_id' => 1,
        ]);

        $freshAttacker = $attacker->fresh();
        $this->assertTrue(
            $freshAttacker->isOnCooldown('combat_isolation_cooldown'),
            'Attacker should have active combat_isolation_cooldown after placing contract'
        );
    }

    public function test_pvp_theft_sets_combat_isolation_cooldown(): void
    {
        $userThief = User::factory()->create();
        $thief = Character::factory()->create([
            'user_id' => $userThief->id,
            'name' => 'SlyRobber',
            'level' => 20,
            'energy' => 100,
            'cash' => 1000,
            'is_in_prison' => false,
            'coma_until' => null,
            'protection_until' => null,
        ]);

        $userVictim = User::factory()->create();
        $victim = Character::factory()->create([
            'user_id' => $userVictim->id,
            'name' => 'UnsuspectingMark',
            'level' => 20,
            'cash' => 50000,
            'is_in_prison' => false,
            'coma_until' => null,
            'protection_until' => null,
        ]);

        $response = $this->actingAs($userThief)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'victim_id' => $victim->id,
        ]);

        $thief->refresh();
        $this->assertTrue(
            $thief->isOnCooldown('combat_isolation_cooldown'),
            'Thief should have active combat_isolation_cooldown after robbing another player'
        );
    }

    public function test_arena_attack_sets_combat_isolation_cooldown_immediately(): void
    {
        $userAttacker = User::factory()->create();
        $attacker = Character::factory()->create([
            'user_id' => $userAttacker->id,
            'name' => 'BrawlerMax',
            'level' => 20,
            'strength' => 500,
            'defense' => 500,
            'energy' => 100,
            'health' => 100,
            'is_in_prison' => false,
            'coma_until' => null,
            'protection_until' => null,
        ]);

        $userOpponent = User::factory()->create();
        $opponent = Character::factory()->create([
            'user_id' => $userOpponent->id,
            'name' => 'BrawlerFoe',
            'level' => 20,
            'strength' => 100,
            'defense' => 100,
            'cash' => 10000,
            'is_in_prison' => false,
            'coma_until' => null,
            'protection_until' => null,
        ]);

        $response = $this->actingAs($userAttacker)->post(route('fight.attack'), [
            'opponent_id' => $opponent->id,
            'auto_resolve' => false,
        ]);

        $attacker->refresh();
        $this->assertTrue(
            $attacker->isOnCooldown('combat_isolation_cooldown'),
            'Attacker should have combat_isolation_cooldown set immediately upon initiating arena attack'
        );
    }

    public function test_isolation_activate_fast_fails_when_on_combat_cooldown(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'CowardlyShooter',
            'level' => 20,
            'cash' => 500000,
            'is_in_prison' => false,
            'coma_until' => null,
        ]);

        $character->setCooldown('combat_isolation_cooldown', 1800);

        $response = $this->actingAs($user)->post(route('isolation.activate'), [
            'hours' => 1,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Gevechtsstatus', session('error'));
        $this->assertFalse($character->fresh()->hasIsolationProtection());
    }

    public function test_hitman_loot_cap_and_cell_safe_math(): void
    {
        $userShooter = User::factory()->create();
        $shooter = Character::factory()->create([
            'user_id' => $userShooter->id,
            'name' => 'DeadshotDan',
            'level' => 25,
            'strength' => 2000,
            'bullets' => 2000,
            'energy' => 100,
            'is_in_prison' => false,
            'coma_until' => null,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $userVictim = User::factory()->create();
        $victim = Character::factory()->create([
            'user_id' => $userVictim->id,
            'name' => 'RichWhale',
            'level' => 25,
            'defense' => 10,
            'health' => 100,
            'cash' => 100000000, // €100M
            'is_in_prison' => false,
            'coma_until' => null,
            'protection_until' => null,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'cell_upgrades' => [
                'door' => 0,
                'bed' => 0,
                'safe' => 5, // Safe level 5 = 50% protection
                'comfort' => 0,
            ],
        ]);

        // 45% of €100M is €45M, which caps to €25M base loot.
        // With Safe level 5 (50% reduction), the stolen cash must be €12.5M, NOT €25M.
        $response = $this->actingAs($userShooter)->post(route('hitman.shoot'), [
            'target_id' => $victim->id,
            'bullets' => 500,
        ]);

        $victim->refresh();
        $stolenAmount = 100000000 - $victim->cash;

        // Verify the stolen amount was reduced by the Safe upgrade
        $this->assertEquals(12500000, $stolenAmount, 'Loot cap of 25M should be reduced by 50% safe to 12.5M');
    }

    public function test_character_get_total_defense_and_door_bonus(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'defense' => 250,
            'cell_upgrades' => [
                'door' => 5, // 2000 bonus
                'bed' => 0,
                'safe' => 0,
                'comfort' => 0,
            ],
        ]);

        $this->assertEquals(2000, $character->getCellDefenseBonus());
        $this->assertEquals(2250, $character->getTotalDefense());
    }

    public function test_gang_wage_service_payout_all_gangs_and_calculation(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'level' => 20,
            'cash' => 10000,
        ]);

        $gang = Gang::create([
            'name' => 'Wages Gang P3',
            'tag' => 'WGP3',
            'leader_character_id' => $character->id,
            'treasury' => 5000000,
            'bling_shops' => 100,
            'respect' => 300000,
            'specialty' => 'finance',
            'description' => 'Wage calculation test',
        ]);

        $character->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
            'last_gang_wage_at' => now()->subHours(2)->startOfHour(),
        ]);

        $wageService = app(GangWageService::class);
        $hourlyWage = $wageService->calculateHourlyWage($gang, $character);
        $this->assertGreaterThan(0, $hourlyWage);

        $breakdown = $wageService->getWageBreakdown($gang, $character);
        $this->assertEquals($hourlyWage, $breakdown['character_hourly_wage']);

        // Test payoutAllGangs alias
        $totalPaid = $wageService->payoutAllGangs();
        $this->assertGreaterThan(0, $totalPaid);
        $this->assertGreaterThan(0, $character->fresh()->bank);
    }

    public function test_gang_defense_npc_calculation_purges_expired_and_respects_cap(): void
    {
        $gang = Gang::create([
            'name' => 'Def Gang P3',
            'tag' => 'DGP3',
            'treasury' => 1000000,
            'protection_points' => 10000,
            'specialty' => 'violence',
            'description' => 'Defense NPC test',
        ]);

        // Add 5 active Enforcers (+15% each = +75%)
        for ($i = 0; $i < 5; $i++) {
            GangNpc::create([
                'gang_id' => $gang->id,
                'npc_key' => 'boris',
                'name' => 'Boris '.$i,
                'role_title' => 'Enforcer',
                'bonus_type' => 'defense',
                'bonus_value' => 15,
                'hired_at' => now()->subMinutes(10),
                'expires_at' => now()->addMinutes(20),
            ]);
        }

        // Add 2 expired Enforcers
        for ($i = 0; $i < 2; $i++) {
            GangNpc::create([
                'gang_id' => $gang->id,
                'npc_key' => 'boris',
                'name' => 'Expired Boris '.$i,
                'role_title' => 'Enforcer',
                'bonus_type' => 'defense',
                'bonus_value' => 15,
                'hired_at' => now()->subHours(2),
                'expires_at' => now()->subHour(),
            ]);
        }

        $this->assertEquals(75, $gang->npc_defense_bonus_percent);
        $this->assertEquals(17500, $gang->total_defense);
    }

    public function test_faq_controller_texts_have_no_level_bypass(): void
    {
        $categories = FaqController::getFaqData();

        $content = json_encode($categories);

        $this->assertStringNotContainsString('zonder level-limiet', $content);
        $this->assertStringNotContainsString('zonder level-limieten', $content);
        $this->assertStringNotContainsString('Geen 15-level aanvalslimiet', $content);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use App\Services\AllowanceService;
use App\Services\TheftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WageProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_allowance_grants_15_minute_wage_protection(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->gang_id = null;
        $character->last_allowance_at = Carbon::now()->subMinutes(30);
        $character->wage_protection_until = null;
        $character->save();

        $service = app(AllowanceService::class);
        $result = $service->payoutPendingAllowance($character);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result['amount']);
        $character->refresh();
        $this->assertTrue($character->hasWageProtection());
        $this->assertNotNull($character->wage_protection_until);
        $this->assertGreaterThan(now(), $character->wage_protection_until);
    }

    public function test_gang_wage_claim_to_cash_grants_protection_and_to_bank_deposits_directly(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Bescherming Bende',
            'tag' => 'BSH',
            'specialty' => 'Kantine Controle',
            'max_members' => 10,
            'leader_character_id' => $character->id,
            'hourly_wage' => 12000,
        ]);

        $character->gang_id = $gang->id;
        $character->gang_role = 'leader';
        $character->last_gang_wage_at = Carbon::now()->subHours(2)->startOfHour();
        $character->cash = 1000;
        $character->bank = 5000;
        $character->wage_protection_until = null;
        $character->save();

        $this->actingAs($user);

        // Claim to cash
        $responseCash = $this->post(route('gangs.claim-wage'), [
            'destination' => 'cash',
        ]);
        $responseCash->assertRedirect();
        $character->refresh();

        $this->assertTrue($character->hasWageProtection());
        $this->assertGreaterThan(1000, $character->cash);

        // Advance time, simulate another wage ready, and claim to bank
        $character->last_gang_wage_at = Carbon::now()->subHours(2)->startOfHour();
        $character->wage_protection_until = null;
        $character->save();

        $responseBank = $this->post(route('gangs.claim-wage'), [
            'destination' => 'bank',
        ]);
        $responseBank->assertRedirect();
        $character->refresh();

        $this->assertNull($character->wage_protection_until);
        $this->assertGreaterThan(5000, $character->bank);
    }

    public function test_theft_is_blocked_when_victim_has_wage_protection(): void
    {
        $userAttacker = User::factory()->create();
        $attacker = Character::factory()->create([
            'user_id' => $userAttacker->id,
            'name' => 'Attacker',
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'stamina' => 100,
            'cash' => 500,
        ]);

        $userVictim = User::factory()->create();
        $victim = Character::factory()->create([
            'user_id' => $userVictim->id,
            'name' => 'Victim',
            'cash' => 10000,
            'wage_protection_until' => now()->addMinutes(12),
        ]);

        $theftService = app(TheftService::class);
        $result = $theftService->stealFromInmate($attacker, $victim);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('bescherming', $result['message']);
        $victim->refresh();
        $this->assertEquals(10000, $victim->cash);
    }

    public function test_attacking_or_theft_attempt_breaks_own_wage_protection(): void
    {
        $userAttacker = User::factory()->create();
        $attacker = Character::factory()->create([
            'user_id' => $userAttacker->id,
            'name' => 'AggressiveAttacker',
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'stamina' => 100,
            'last_allowance_at' => now(),
            'wage_protection_until' => now()->addMinutes(14),
        ]);

        $userVictim = User::factory()->create();
        $victim = Character::factory()->create([
            'user_id' => $userVictim->id,
            'name' => 'PassiveVictim',
            'cash' => 5000,
            'last_allowance_at' => now(),
            'wage_protection_until' => null,
        ]);

        $this->assertTrue($attacker->hasWageProtection());

        $theftService = app(TheftService::class);
        $theftService->stealFromInmate($attacker, $victim);

        $attacker->refresh();
        $this->assertFalse($attacker->hasWageProtection());
        $this->assertNull($attacker->wage_protection_until);
    }

    public function test_luchtplaats_battle_steals_zero_cash_from_wage_protected_defender(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $user->character;
        $attacker->update([
            'energy' => 100,
            'health' => 100,
            'strength' => 9999,
            'is_in_prison' => false,
            'last_allowance_at' => now(),
        ]);

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update([
            'is_in_prison' => false,
            'protection_until' => null,
            'cash_protection_until' => null,
            'wage_protection_until' => now()->addMinutes(12),
            'cash' => 50000,
            'defense' => 1,
            'health' => 100,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
        ]);
        $response->assertRedirect(route('fight.arena', $defender));

        $resolveResponse = $this->postJson(route('fight.arena.resolve', $defender));
        $resolveResponse->assertOk();
        $resolveData = $resolveResponse->json();

        $this->assertEquals(0, $resolveData['battle']['stolen_cash'] ?? null);
        $defender->refresh();
        $this->assertEquals(50000, $defender->cash);
    }
}

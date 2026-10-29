<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\TheftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashProtectionSystemTest extends TestCase
{
    use RefreshDatabase;

    private function createCharacter(array $attrs = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler_'.rand(1000, 9999),
            'level' => 10,
            'strength' => 20,
            'defense' => 20,
            'speed' => 20,
            'cash' => 500000,
            'bank' => 1000000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'vip_points' => 50,
            'stars' => 100,
            'respect' => 100,
            'is_in_prison' => false,
        ], $attrs));
    }

    public function test_vip_shop_redeem_cash_protection_sets_cash_protection_until(): void
    {
        $char = $this->createCharacter(['vip_points' => 30]);

        $response = $this->actingAs($char->user)->post(route('vip.redeem'), [
            'reward_type' => 'cash_protection',
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertEquals(10, $char->vip_points);
        $this->assertTrue($char->hasCashProtection());
        $this->assertTrue($char->isProtected());
        $this->assertNotNull($char->cash_protection_until);
        $this->assertTrue($char->cash_protection_until->isFuture());
    }

    public function test_attacking_does_not_break_vip_cash_protection(): void
    {
        $char = $this->createCharacter([
            'cash_protection_until' => now()->addHours(24),
            'protection_until' => now()->addMinutes(15), // Temporary recovery shield
        ]);

        $this->assertTrue($char->hasCashProtection());

        // Perform breakProtection (which happens when attacking)
        $char->breakProtection();
        $char->refresh();

        // Recovery shield should be cleared, but VIP Cash Protection MUST REMAIN INTACT!
        $this->assertNull($char->protection_until);
        $this->assertTrue($char->hasCashProtection());
        $this->assertTrue($char->isProtected());
    }

    public function test_luchtplaats_attack_is_blocked_when_defender_has_cash_protection(): void
    {
        $attacker = $this->createCharacter(['level' => 25, 'strength' => 50, 'energy' => 100]);
        $defender = $this->createCharacter([
            'level' => 20,
            'cash' => 500000,
            'cash_protection_until' => now()->addHours(24),
        ]);

        $this->assertTrue($defender->hasCashProtection());

        $response = $this->actingAs($attacker->user)->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
        ]);

        $response->assertSessionHas('error');
        $defender->refresh();
        $this->assertEquals(500000, $defender->cash);
    }

    public function test_theft_is_blocked_when_victim_has_cash_protection(): void
    {
        $thief = $this->createCharacter(['level' => 20, 'speed' => 50, 'energy' => 100]);
        $victim = $this->createCharacter([
            'level' => 18,
            'cash' => 250000,
            'cash_protection_until' => now()->addHours(24),
        ]);

        $theftService = app(TheftService::class);
        $result = $theftService->stealFromInmate($thief, $victim);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['cash_stolen']);
        $this->assertStringContainsString('Cash Bescherming', $result['message']);
        $victim->refresh();
        $this->assertEquals(250000, $victim->cash);
    }
}

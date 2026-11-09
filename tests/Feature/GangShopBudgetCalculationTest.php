<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangShopBudgetCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_cash_automatically_calculates_exact_units_without_overcharging(): void
    {
        $user = User::factory()->create();
        $gang = Gang::factory()->create([
            'treasury' => 2000000000, // 2 miljard in kas
            'attack_power' => 500,
            'protection_points' => 1000,
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
        ]);

        $this->actingAs($user);

        // Player wants to spend €1.000.000.000 (1 miljard) on Power
        // 1.000.000.000 / 15.000 = 66.666 units
        // Actual cost = 66.666 * 15.000 = 999.990.000
        // Change kept in treasury = 10.000
        $response = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'power',
            'budget_cash' => 1000000000,
        ]);

        $response->assertSessionHas('success');

        $gang->refresh();
        $expectedCost = 66666 * 15000; // 999.990.000
        $this->assertEquals(2000000000 - $expectedCost, $gang->treasury);
        $this->assertEquals(500 + (66666 * 5000), $gang->attack_power);
    }

    public function test_budget_cash_caps_at_current_gang_treasury(): void
    {
        $user = User::factory()->create();
        $gang = Gang::factory()->create([
            'treasury' => 40000, // Only €40.000 in treasury
            'attack_power' => 500,
            'protection_points' => 1000,
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
        ]);

        $this->actingAs($user);

        // Player requests 100.000 budget, but gang only has 40.000
        // Affordable = floor(40.000 / 15.000) = 2 units (€30.000)
        $response = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'protection',
            'budget_cash' => 100000,
        ]);

        $response->assertSessionHas('success');

        $gang->refresh();
        $this->assertEquals(10000, $gang->treasury); // 40.000 - 30.000 = 10.000
        $this->assertEquals(1000 + (2 * 5000), $gang->protection_points);
    }
}


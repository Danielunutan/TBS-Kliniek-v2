<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\EnergyService;
use App\Support\CellUpgradeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CellUpgradesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_cell_page_and_catalog(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000000,
        ]);

        $response = $this->actingAs($user)->get(route('cell.index'));

        $response->assertStatus(200);
        $response->assertSee('Mijn TBS Cel &amp; Upgrades', false);
        $response->assertSee('Luxe Boxspring &amp; Traagschuim Matras', false);
        $response->assertSee('Versterkte Stalen Deur &amp; Camerasysteem', false);
    }

    public function test_can_purchase_cell_upgrades_and_receive_bonuses(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000000,
        ]);

        // 1. Upgrade bed to level 1 (€500.000)
        $response = $this->actingAs($user)->post(route('cell.upgrade', 'bed'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(9500000, $character->cash);
        $this->assertEquals(1, $character->getCellUpgradeLevel('bed'));
        $this->assertEquals(0.08, $character->getCellEnergyIntervalReduction());

        // 2. Upgrade door to level 1 (€750.000)
        $response = $this->actingAs($user)->post(route('cell.upgrade', 'door'));
        $response->assertRedirect();

        $character->refresh();
        $this->assertEquals(8750000, $character->cash);
        $this->assertEquals(1, $character->getCellUpgradeLevel('door'));
        $this->assertEquals(100, $character->getCellDefenseBonus());

        // 3. Upgrade safe to level 1 (€500.000)
        $response = $this->actingAs($user)->post(route('cell.upgrade', 'safe'));
        $response->assertRedirect();

        $character->refresh();
        $this->assertEquals(8250000, $character->cash);
        $this->assertEquals(1, $character->getCellUpgradeLevel('safe'));
        $this->assertEquals(10, $character->getCellCashProtectionPct());
    }

    public function test_cannot_purchase_upgrade_without_enough_cash(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000, // Not enough for €500k
        ]);

        $response = $this->actingAs($user)->post(route('cell.upgrade', 'bed'));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $character->refresh();
        $this->assertEquals(0, $character->getCellUpgradeLevel('bed'));
        $this->assertEquals(10000, $character->cash);
    }
}


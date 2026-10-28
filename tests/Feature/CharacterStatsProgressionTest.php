<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterStatsProgressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_shop_purchase_of_equipment_adds_to_inventory_not_base_stats(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'TestVechter',
            'level' => 5,
            'strength' => 20,
            'defense' => 15,
            'stamina' => 10,
            'intelligence' => 10,
            'charisma' => 10,
            'discipline' => 10,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 50000,
            'bank' => 0,
        ]);

        $boks = Item::where('slug', 'boksbeugel')->first();
        if (! $boks) {
            $boks = Item::create([
                'name' => 'Boksbeugel Test',
                'slug' => 'boksbeugel',
                'description' => 'Test boksbeugel',
                'category' => 'equipment',
                'price_kc' => 500,
                'strength_bonus' => 5,
                'is_buyable' => true,
            ]);
        }

        $this->actingAs($user)
            ->post(route('shop.buy', $boks), [
                'quantity' => 1,
            ])
            ->assertSessionHas('success');

        $character->refresh();

        // Base strength MUST stay at 20, not inflated by direct stat boost
        $this->assertEquals(20, $character->strength);

        // Inventory item must exist with quantity 1 (unique equipment)
        $inv = CharacterItem::where('character_id', $character->id)
            ->where('item_id', $boks->id)
            ->first();
        $this->assertNotNull($inv);
        $this->assertEquals(1, $inv->quantity);

        // Attempting to buy again must fail because equipment is unique
        $this->actingAs($user)
            ->post(route('shop.buy', $boks), ['quantity' => 1])
            ->assertSessionHas('error');

        // Effective strength includes single piece bonus (+5) = 25
        $this->assertEquals(25, $character->getEffectiveStrength());
    }

    public function test_level_stat_cap_formula(): void
    {
        $character = new Character([
            'level' => 10,
            'strength' => 20,
        ]);

        // Max stat for level 10: 100 + 10 * 50 = 600
        $this->assertEquals(600, $character->getMaxStatForLevel());
    }
}

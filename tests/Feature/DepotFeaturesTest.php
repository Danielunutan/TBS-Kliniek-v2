<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepotFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = []): array
    {
        $user = User::factory()->create();
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate'.rand(1000, 9999),
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 50,
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 10,
            'pumpkin_seeds' => 120,
            'masks' => 5,
        ], $attributes));

        return [$user, $character];
    }

    public function test_depot_page_loads_with_all_tabs_and_event_data(): void
    {
        [$user, $character] = $this->createInmate(['pumpkin_seeds' => 120]);

        $regularItem = Item::create([
            'name' => 'Frikandelbroodje',
            'slug' => 'frikandelbroodje',
            'description' => 'Lekker warm broodje',
            'category' => 'food',
            'rarity' => 'common',
            'price_kc' => 200,
            'energy_restore' => 25,
            'icon' => 'fa-bread-slice',
            'is_buyable' => true,
        ]);

        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $regularItem->id,
            'quantity' => 2,
        ]);

        $eventItem = Item::create([
            'name' => '🍬 Snoep of Je Leven Zakje',
            'slug' => 'halloween_candy_bag',
            'description' => 'Griezelig snoepzakje vol lekkers.',
            'category' => 'consumable',
            'rarity' => 'legendary',
            'price_kc' => 2500,
            'energy_restore' => 35,
            'icon' => 'fa-candy-cane',
            'is_buyable' => false,
        ]);

        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $eventItem->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('depot.index'));

        $response->assertStatus(200);
        $response->assertSee('Depot & Centrale Opslag');
        $response->assertSee('Persoonlijk Varia (Event Opslag');
        $response->assertSee('120');
        $response->assertSee('Frikandelbroodje');
        $response->assertSee('Snoep of Je Leven Zakje');
    }

    public function test_event_items_do_not_count_towards_standard_storage_used(): void
    {
        [$user, $character] = $this->createInmate(['masks' => 2]);

        $regularItem = Item::create([
            'name' => 'Smokkel Sigaretten',
            'slug' => 'sigaretten',
            'description' => 'Pakje peuken',
            'category' => 'contraband',
            'rarity' => 'common',
            'price_kc' => 300,
            'energy_restore' => 20,
            'icon' => 'fa-smoking',
            'is_buyable' => true,
        ]);

        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $regularItem->id,
            'quantity' => 3,
        ]);

        $eventItem = Item::create([
            'name' => '🕯️ Behekste Voodoo Pop',
            'slug' => 'halloween_voodoo_doll',
            'description' => 'Griezelige pop',
            'category' => 'special',
            'rarity' => 'legendary',
            'price_kc' => 5000,
            'energy_restore' => 0,
            'icon' => 'fa-wand-magic-sparkles',
            'is_buyable' => false,
        ]);

        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $eventItem->id,
            'quantity' => 10,
        ]);

        // Standard storage should be 3 (sigaretten) + 2 (masks) = 5, EXCLUDING the 10 event items!
        $this->assertEquals(5, $character->getStorageUsed());
    }

    public function test_player_can_sell_item_from_depot(): void
    {
        [$user, $character] = $this->createInmate(['cash' => 1000]);

        $item = Item::create([
            'name' => 'Overval Zakmes',
            'slug' => 'zakmes',
            'description' => 'Scherp mes',
            'category' => 'weapon',
            'rarity' => 'common',
            'price_kc' => 2000,
            'icon' => 'fa-knife',
            'is_buyable' => true,
        ]);

        $charItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $item->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('depot.sell-item', $charItem), [
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertGreaterThan(1000, $character->cash);

        $charItem->refresh();
        $this->assertEquals(1, $charItem->quantity);
    }

    public function test_player_can_consume_halloween_candy_bag(): void
    {
        [$user, $character] = $this->createInmate([
            'energy' => 20,
            'max_energy' => 100,
        ]);

        $candyBag = Item::create([
            'name' => '🍬 Snoep of Je Leven Zakje',
            'slug' => 'halloween_candy_bag',
            'description' => 'Griezelig snoepzakje',
            'category' => 'consumable',
            'rarity' => 'legendary',
            'price_kc' => 2500,
            'energy_restore' => 35,
            'icon' => 'fa-candy-cane',
            'is_buyable' => false,
        ]);

        $charItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $candyBag->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('shop.consume', $charItem));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(55, $character->energy);
        $this->assertDatabaseMissing('character_items', ['id' => $charItem->id]);
    }
}

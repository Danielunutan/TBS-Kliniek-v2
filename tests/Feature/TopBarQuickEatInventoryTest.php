<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopBarQuickEatInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_desktop_top_bar_renders_quick_eat_dropdown_with_consumable_items(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        // Ensure character has consumable food items
        $frikandel = Item::where('slug', 'frikandelbroodje')->first();
        $this->assertNotNull($frikandel);

        CharacterItem::updateOrCreate([
            'character_id' => $character->id,
            'item_id' => $frikandel->id,
        ], [
            'quantity' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Rugzak &bull; Snel Eten', false);
        $response->assertSee('Frikandelbroodje');
        $response->assertSee('1x Eten');
        $response->assertSee('Max ⚡', false);
    }

    public function test_player_can_consume_food_from_top_bar_via_json_request(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        // Set low energy
        $character->update(['energy' => 20]);

        $frikandel = Item::where('slug', 'frikandelbroodje')->first();
        $ci = CharacterItem::updateOrCreate([
            'character_id' => $character->id,
            'item_id' => $frikandel->id,
        ], [
            'quantity' => 2,
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('shop.consume', $ci), [
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $character->refresh();
        $this->assertGreaterThan(20, $character->energy);
        $this->assertEquals(1, $ci->fresh()->quantity);
    }

    public function test_player_can_use_fill_max_option_from_top_bar(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->update(['energy' => 10]);

        $frikandel = Item::where('slug', 'frikandelbroodje')->first();
        $ci = CharacterItem::updateOrCreate([
            'character_id' => $character->id,
            'item_id' => $frikandel->id,
        ], [
            'quantity' => 5,
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('shop.consume', $ci), [
            'fill_max' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $character->refresh();
        $this->assertEquals($character->max_energy, $character->energy);
    }

    public function test_empty_state_renders_when_no_food_items_in_backpack(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        // Remove all food items from character inventory
        CharacterItem::where('character_id', $character->id)->delete();

        $this->actingAs($user);
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Geen snacks in je rugzak');
        $response->assertSee('Kantine & Shop Bezoeken', false);
        $response->assertSee('quick-eat-empty-state', false);
    }

    public function test_consuming_health_item_returns_realtime_health_in_json(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->update(['health' => 30, 'max_health' => 100]);

        $medkit = Item::where('slug', 'ehbo-noodverbanddoos')->first();
        if (! $medkit) {
            $medkit = Item::create([
                'name' => 'EHBO Doos',
                'slug' => 'ehbo-noodverbanddoos',
                'category' => 'gezondheid',
                'health_restore' => 50,
                'price_cash' => 100,
                'icon' => 'fa-medkit',
                'is_active' => true,
            ]);
        }

        $ci = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $medkit->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user);
        $response = $this->postJson(route('shop.consume', $ci), [
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'health' => 100,
            'max_health' => 100,
        ]);

        $character->refresh();
        $this->assertEquals(100, $character->health);
    }
}

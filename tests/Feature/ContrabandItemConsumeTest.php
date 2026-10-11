<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContrabandItemConsumeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacterWithItem(string $itemSlug, int $energy = 3, int $health = 90, int $quantity = 1): array
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SmokkelSpeler',
            'cash' => 1000,
            'bank' => 500,
            'respect' => 100,
            'energy' => $energy,
            'max_energy' => 100,
            'health' => $health,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
            'last_energy_refresh' => now(),
        ]);

        $item = Item::where('slug', $itemSlug)->firstOrFail();
        $charItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
        ]);

        return [$character, $charItem, $item];
    }

    public function test_consuming_cigarettes_restores_energy_and_health(): void
    {
        [$character, $charItem] = $this->createCharacterWithItem('sigaretten', 3, 90, 4);

        $this->actingAs($character->user);

        $response = $this->post(route('shop.consume', $charItem->id));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('+20 Energie', session('success'));
        $this->assertStringContainsString('+10 HP', session('success'));

        $character->refresh();
        $charItem->refresh();

        $this->assertEquals(23, $character->energy);
        $this->assertEquals(100, $character->health);
        $this->assertEquals(3, $charItem->quantity);
    }

    public function test_consuming_ritalin_restores_energy_and_health(): void
    {
        [$character, $charItem] = $this->createCharacterWithItem('ritalin', 10, 80, 1);

        $this->actingAs($character->user);

        $response = $this->post(route('shop.consume', $charItem->id));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('+35 Energie', session('success'));
        $this->assertStringContainsString('+15 HP', session('success'));

        $character->refresh();

        $this->assertEquals(45, $character->energy);
        $this->assertEquals(95, $character->health);
        $this->assertDatabaseMissing('character_items', ['id' => $charItem->id]);
    }

    public function test_consuming_phone_restores_energy_and_health(): void
    {
        [$character, $charItem] = $this->createCharacterWithItem('telefoon', 10, 50, 6);

        $this->actingAs($character->user);

        $response = $this->post(route('shop.consume', $charItem->id));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('+50 Energie', session('success'));
        $this->assertStringContainsString('+25 HP', session('success'));

        $character->refresh();
        $charItem->refresh();

        $this->assertEquals(60, $character->energy);
        $this->assertEquals(75, $character->health);
        $this->assertEquals(5, $charItem->quantity);
    }

    public function test_cannot_consume_recovery_item_when_both_energy_and_health_are_already_full(): void
    {
        [$character, $charItem] = $this->createCharacterWithItem('sigaretten', 100, 100, 2);

        $this->actingAs($character->user);

        $response = $this->post(route('shop.consume', $charItem->id));

        $response->assertSessionHas('info');
        $this->assertStringContainsString('zijn al maximaal', session('info'));

        $character->refresh();
        $charItem->refresh();

        $this->assertEquals(100, $character->energy);
        $this->assertEquals(100, $character->health);
        // Quantity must not be reduced!
        $this->assertEquals(2, $charItem->quantity);
    }
}

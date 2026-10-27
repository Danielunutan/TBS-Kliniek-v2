<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Models\VaultCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StarShopItemsTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(string $name, array $attrs = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 5000,
            'bank' => 0,
            'stars' => 100,
            'energy' => 10,
            'max_energy' => 100,
            'health' => 20,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'masks' => 0,
            'bullets' => 0,
            'xxl_cases' => 0,
            'unopened_bags' => 0,
            'is_in_prison' => false,
            'prison_until' => null,
            'protection_until' => null,
            'vest_until' => null,
        ], $attrs));
    }

    public function test_character_can_purchase_full_restore_with_stars(): void
    {
        $character = $this->createCharacter('TiredInmate', [
            'stars' => 10,
            'energy' => 5,
            'health' => 10,
        ]);

        $response = $this->actingAs($character->user)->post(route('shop.buy-star-item'), [
            'item_type' => 'full_restore',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(5, $character->stars); // 10 - 5
        $this->assertEquals(100, $character->energy);
        $this->assertEquals(100, $character->health);
    }

    public function test_character_can_escape_prison_with_stars(): void
    {
        $character = $this->createCharacter('JailedInmate', [
            'stars' => 20,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(45),
            'prison_reason' => 'Vechtpartij',
        ]);

        $response = $this->actingAs($character->user)->post(route('shop.buy-star-item'), [
            'item_type' => 'prison_escape',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(10, $character->stars); // 20 - 10
        $this->assertFalse($character->is_in_prison);
        $this->assertNull($character->prison_until);
        $this->assertNull($character->prison_reason);
    }

    public function test_cannot_escape_prison_when_not_in_prison(): void
    {
        $character = $this->createCharacter('FreeInmate', [
            'stars' => 20,
            'is_in_prison' => false,
            'prison_until' => null,
        ]);

        $response = $this->actingAs($character->user)->post(route('shop.buy-star-item'), [
            'item_type' => 'prison_escape',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $character->refresh();
        $this->assertEquals(20, $character->stars);
    }

    public function test_character_can_purchase_ammo_pack_with_stars(): void
    {
        $character = $this->createCharacter('Shooter', [
            'stars' => 30,
            'bullets' => 50,
        ]);

        $response = $this->actingAs($character->user)->post(route('shop.buy-star-item'), [
            'item_type' => 'ammo_pack',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(10, $character->stars); // 30 - 20
        $this->assertEquals(300, $character->bullets); // 50 + 250
        $this->assertNotNull($character->vest_until);
    }

    public function test_character_can_purchase_protection_with_stars(): void
    {
        $character = $this->createCharacter('ProtectedBoss', [
            'stars' => 50,
            'protection_until' => null,
        ]);

        $response = $this->actingAs($character->user)->post(route('shop.buy-star-item'), [
            'item_type' => 'protection_24h',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(20, $character->stars); // 50 - 30
        $this->assertNotNull($character->protection_until);
        $this->assertTrue($character->protection_until->isFuture());
    }

    public function test_character_can_purchase_vault_voucher_with_stars(): void
    {
        $character = $this->createCharacter('VaultSeeker', [
            'stars' => 30,
        ]);

        $response = $this->actingAs($character->user)->post(route('shop.buy-star-item'), [
            'item_type' => 'vault_code',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(5, $character->stars); // 30 - 25

        $code = VaultCode::where('character_id', $character->id)->where('is_redeemed', false)->first();
        $this->assertNotNull($code);
        $this->assertGreaterThanOrEqual(15000, $code->reward_cash);
    }

    public function test_insufficient_stars_returns_error(): void
    {
        $character = $this->createCharacter('BrokePatient', [
            'stars' => 2,
        ]);

        $response = $this->actingAs($character->user)->post(route('shop.buy-star-item'), [
            'item_type' => 'full_restore',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $character->refresh();
        $this->assertEquals(2, $character->stars);
    }
}

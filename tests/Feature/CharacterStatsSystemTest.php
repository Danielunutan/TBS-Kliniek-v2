<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\Mission;
use App\Models\User;
use App\Services\MissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterStatsSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_character_effective_stats_include_equipment_bonuses(): void
    {
        $character = Character::first();
        $character->update([
            'strength' => 15,
            'defense' => 12,
            'stamina' => 10,
            'intelligence' => 20,
            'charisma' => 14,
            'discipline' => 11,
        ]);

        $this->assertEquals(15, $character->getEffectiveStrength());
        $this->assertEquals(12, $character->getEffectiveDefense());

        $itemBoks = Item::firstOrCreate(['slug' => 'test-boksbeugel'], [
            'name' => 'Boksbeugel Test',
            'description' => 'Test item',
            'category' => 'equipment',
            'rarity' => 'common',
            'price_kc' => 200,
            'strength_bonus' => 5,
        ]);

        $itemKisten = Item::firstOrCreate(['slug' => 'test-kisten'], [
            'name' => 'Kisten Test',
            'description' => 'Test item',
            'category' => 'equipment',
            'rarity' => 'common',
            'price_kc' => 200,
            'defense_bonus' => 7,
        ]);

        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $itemBoks->id,
            'quantity' => 1,
        ]);

        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $itemKisten->id,
            'quantity' => 2, // Duplicate quantity capped at 1 unique piece bonus
        ]);

        $this->assertEquals(5, $character->getItemBonus('strength'));
        $this->assertEquals(7, $character->getItemBonus('defense'));
        $this->assertEquals(20, $character->getEffectiveStrength());
        $this->assertEquals(19, $character->getEffectiveDefense());
    }

    public function test_mission_system_uses_character_stats_and_can_award_stat_gains(): void
    {
        $character = Character::first();
        $character->update([
            'energy' => 100,
            'strength' => 50,
            'defense' => 40,
            'stamina' => 30,
            'intelligence' => 30,
            'charisma' => 20,
            'discipline' => 20,
        ]);

        $mission = Mission::firstOrCreate(['slug' => 'test-brawl-mission'], [
            'category' => 'gevecht',
            'title' => 'Grote Vechtpartij',
            'description' => 'Test vechten',
            'giver_name' => 'Barry Biceps',
            'location_name' => 'Binnenplaats',
            'required_level' => 1,
            'energy_cost' => 5,
            'success_rate' => 100, // 100% guarantee success for testing
            'min_cash_reward' => 200,
            'max_cash_reward' => 200,
            'xp_reward' => 50,
            'respect_reward' => 2,
            'sort_order' => 999,
        ]);

        $missionService = app(MissionService::class);
        $result = $missionService->execute($character, $mission);

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['cash_earned']);
        $this->assertEquals(50, $result['xp_earned']);
    }

    public function test_shop_gives_charisma_discount_on_item_purchases(): void
    {
        /** @var User $user */
        $user = User::first();
        $character = $user->character;
        $character->update([
            'cash' => 10000,
            'charisma' => 50, // 5% discount (min(15, floor(50 / 10)))
        ]);

        $item = Item::firstOrCreate(['slug' => 'test-gadget'], [
            'name' => 'Test Gadget',
            'description' => 'Test description',
            'category' => 'contraband',
            'rarity' => 'common',
            'price_kc' => 1000,
        ]);

        $this->actingAs($user);
        $response = $this->post(route('shop.buy', $item), [
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        // Price should be 1000 - 5% = 950
        $this->assertEquals(9050, $character->fresh()->cash);
    }

    public function test_hustle_attempt_uses_charisma_and_awards_rewards(): void
    {
        /** @var User $user */
        $user = User::first();
        $character = $user->character;
        $character->update([
            'cash' => 100,
            'energy' => 100,
            'charisma' => 40,
            'intelligence' => 30,
        ]);

        $this->actingAs($user);
        $response = $this->post(route('hustle.attempt'), [
            'hustle_id' => 1,
        ]);

        $response->assertRedirect();
        // Energy consumed
        $this->assertLessThan(100, $character->fresh()->energy);
    }
}

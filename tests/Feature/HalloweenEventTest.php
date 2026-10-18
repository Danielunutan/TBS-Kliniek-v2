<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\HalloweenQuestProgress;
use App\Models\User;
use App\Services\HalloweenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalloweenEventTest extends TestCase
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
            'pumpkin_seeds' => 200,
        ], $attributes));

        return [$user, $character];
    }

    public function test_player_can_view_halloween_quests_page(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->get(route('halloween.quests'));

        $response->assertOk();
        $response->assertSee('Halloween Quests');
        $response->assertSee('Pompoenpitten');
    }

    public function test_player_can_view_halloween_shop_page(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->get(route('halloween.shop'));

        $response->assertOk();
        $response->assertSee('Halloween Shop');
        $response->assertSee('Spooky Pumpkin Glow');
    }

    public function test_halloween_progress_records_actions_and_can_be_claimed(): void
    {
        [$user, $character] = $this->createInmate();

        HalloweenService::record($character, 'theft', 3);

        $periodKey = 'daily_'.now()->toDateString();
        $progress = HalloweenQuestProgress::where('character_id', $character->id)
            ->where('quest_key', 'daily_theft_3')
            ->where('period_key', $periodKey)
            ->first();

        $this->assertNotNull($progress);
        $this->assertEquals(3, $progress->current_count);
        $this->assertTrue($progress->isCompleted());
        $this->assertFalse($progress->is_claimed);

        $response = $this->actingAs($user)->post(route('halloween.claim-quest', 'daily_theft_3'));

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(225, $character->pumpkin_seeds); // 200 initial + 25 reward
    }

    public function test_player_can_buy_and_equip_avatar_border(): void
    {
        [$user, $character] = $this->createInmate(['pumpkin_seeds' => 200]);

        $response = $this->actingAs($user)->post(route('halloween.buy'), [
            'item_key' => 'pumpkin_glow',
        ]);

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(80, $character->pumpkin_seeds); // 200 - 120
        $this->assertTrue($character->hasBorder('pumpkin_glow'));
        $this->assertEquals('pumpkin_glow', $character->equipped_border);
        $this->assertStringContainsString('ring-orange-500', $character->getAvatarBorderClasses());

        // Unequip border
        $response = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'none',
        ]);

        $character->refresh();
        $this->assertNull($character->equipped_border);
    }
}

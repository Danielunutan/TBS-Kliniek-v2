<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingAjaxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 500,
            'bank' => 1000,
            'stars' => 10,
            'respect' => 10,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'intelligence' => 10,
            'charisma' => 10,
            'discipline' => 10,
        ], $attrs));
    }

    public function test_player_can_train_stat_via_ajax_without_page_reload(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 5000,
            'energy' => 100,
            'strength' => 10,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('training.train'), [
                'stat' => 'strength',
                'times' => 1,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'stat' => 'strength',
            'stat_gain' => 1,
            'new_value' => 11,
        ]);

        $character->refresh();
        $this->assertEquals(11, $character->strength);
        $this->assertEquals(90, $character->energy);
        $this->assertLessThan(5000, $character->cash);
    }

    public function test_player_can_train_multiple_points_at_once(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 25000,
            'energy' => 100,
            'defense' => 10,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('training.train'), [
                'stat' => 'defense',
                'times' => 5,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'stat' => 'defense',
            'stat_gain' => 5,
            'new_value' => 15,
        ]);

        $character->refresh();
        $this->assertEquals(15, $character->defense);
        $this->assertEquals(50, $character->energy);
    }

    public function test_training_fails_gracefully_when_insufficient_energy(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 10000,
            'energy' => 2,
            'strength' => 10,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('training.train'), [
                'stat' => 'strength',
                'times' => 1,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $character->refresh();
        $this->assertEquals(10, $character->strength);
    }

    public function test_combat_training_increases_stat_below_cap(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 1,
            'cash' => 5000,
            'energy' => 100,
            'strength' => 50, // Cap is 100 + (1 * 50) = 150
            'perks' => ['cold_blooded_pct' => 50],
        ]);

        $response = $this->actingAs($user)
            ->post(route('combat_training.train'), [
                'session_id' => 1, // Schaduwboksen (strength)
            ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Training voltooid', session('success'));

        $character->refresh();
        $this->assertEquals(51, $character->strength);
        $this->assertEquals(40, $character->xp);
        $this->assertEquals(90, $character->energy);
        $this->assertEquals(5000 - 150, $character->cash);
    }

    public function test_combat_training_blocks_when_stat_cap_is_reached_without_wasting_resources(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 1,
            'cash' => 5000,
            'energy' => 100,
            'strength' => 150, // Max cap for level 1 (100 + 1 * 50 = 150)
            'perks' => ['cold_blooded_pct' => 100],
        ]);

        $this->assertEquals(150, $character->getMaxStatForLevel());

        $response = $this->actingAs($user)
            ->post(route('combat_training.train'), [
                'session_id' => 1, // Schaduwboksen (strength)
            ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('LEVEL-PLAFOND BEREIKT', session('error'));

        $character->refresh();
        $this->assertEquals(150, $character->strength); // Unchanged
        $this->assertEquals(100, $character->energy); // Energy not deducted!
        $this->assertEquals(5000, $character->cash); // Cash not deducted!
    }

    public function test_combat_training_level_focus_mode_awards_double_xp_when_stat_is_at_cap(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 1,
            'xp' => 270, // 350 XP total needed for level 2, so +80 XP levels up to level 2!
            'cash' => 5000,
            'energy' => 100,
            'strength' => 150, // Max cap for level 1
            'perks' => ['cold_blooded_pct' => 100],
        ]);

        $response = $this->actingAs($user)
            ->post(route('combat_training.train'), [
                'session_id' => 1,
                'mode' => 'level',
            ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Focus op Level', session('success'));

        $character->refresh();
        $this->assertEquals(150, $character->strength);
        $this->assertEquals(2, $character->level);
        $this->assertEquals(200, $character->getMaxStatForLevel());
        $this->assertEquals(100, $character->energy); // Refilled on level up!
        $this->assertEquals(5000 - 150 + 300, $character->cash); // -150 cost + 300 level 2 cash bonus
    }

    public function test_sync_stats_with_level_cap_converts_exceeded_stats_to_xp_and_clamps_to_max(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 1,
            'xp' => 0,
            'strength' => 152, // 2 above cap of 150
            'defense' => 151,  // 1 above cap of 150
            'stamina' => 140,
            'discipline' => 150,
        ]);

        $response = $this->actingAs($user)->get(route('combat_training.index'));
        $response->assertOk();

        $character->refresh();
        $maxCap = $character->getMaxStatForLevel();
        $this->assertLessThanOrEqual($maxCap, $character->strength);
        $this->assertLessThanOrEqual($maxCap, $character->defense);
        $this->assertEquals(150, $character->strength);
        $this->assertEquals(150, $character->defense);
        $this->assertEquals(3 * 45, $character->xp);
    }

    public function test_training_level_focus_mode_via_ajax_awards_xp_without_exceeding_stat_cap(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 1,
            'xp' => 10,
            'cash' => 5000,
            'energy' => 100,
            'strength' => 150,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('training.train'), [
                'stat' => 'strength',
                'times' => 1,
                'mode' => 'level',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'mode' => 'level',
            'stat' => 'strength',
            'stat_gain' => 0,
            'xp_gain' => 50,
            'new_value' => 150,
        ]);

        $character->refresh();
        $this->assertEquals(150, $character->strength);
        $this->assertEquals(60, $character->xp);
    }

    public function test_arm_wrestle_npc_win_is_never_overturned_into_loss(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 5000,
            'energy' => 100,
            'strength' => 60,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('armwrestling.npc.finish'), [
                'npc_key' => 'sjonnie',
                'won' => true,
                'clicks' => 8,
                'duration_ms' => 2400,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'won' => true,
        ]);

        $character->refresh();
        $this->assertEquals(5000 + 1500, $character->cash);
        $this->assertEquals(95, $character->energy);
    }
}

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

    public function test_combat_training_allows_training_even_at_stat_cap_and_awards_xp(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 1,
            'cash' => 5000,
            'energy' => 100,
            'strength' => 65, // Cap for level 1 (50 + 1 * 15 = 65)
            'perks' => ['cold_blooded_pct' => 100],
        ]);

        $this->assertTrue($character->strength >= $character->getMaxStatForLevel());

        $response = $this->actingAs($user)
            ->post(route('combat_training.train'), [
                'session_id' => 1, // Schaduwboksen (strength)
            ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Training voltooid', session('success'));

        $character->refresh();
        $this->assertEquals(66, $character->strength); // Combat training increases stat (+1) without gym cap
        $this->assertEquals(40, $character->xp); // XP awarded
        $this->assertEquals(90, $character->energy); // Energy consumed
        $this->assertEquals(5000 - 150, $character->cash); // Cost deducted
    }
}

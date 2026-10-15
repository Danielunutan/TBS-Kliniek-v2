<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineStatusTest extends TestCase
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
        ], $attrs));
    }

    public function test_character_is_online_logic(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'last_active_at' => now(),
        ]);

        $this->assertTrue($character->isOnline());
        $this->assertEquals('Nu Online', $character->online_status_label);

        $character->last_active_at = now()->subMinutes(10);
        $this->assertFalse($character->isOnline());
        $this->assertStringContainsString('geleden', $character->online_status_label);

        $character->last_active_at = null;
        $this->assertFalse($character->isOnline());
        $this->assertEquals('Nog niet actief', $character->online_status_label);
    }

    public function test_online_count_helper(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->createCharacter($user1, [
            'last_active_at' => now(),
        ]);

        $this->createCharacter($user2, [
            'last_active_at' => now()->subMinutes(2),
        ]);

        $this->createCharacter($user3, [
            'last_active_at' => now()->subMinutes(20),
        ]);

        $this->assertEquals(2, Character::getOnlineCount());
    }

    public function test_middleware_updates_character_last_active_at(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'last_active_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        $character->refresh();
        $this->assertNotNull($character->last_active_at);
        $this->assertTrue($character->last_active_at->diffInSeconds(now()) < 5);
        $this->assertTrue($character->isOnline());

        $response->assertSee('Online');
    }
}

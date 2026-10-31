<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\CharacterMemoryStat;
use App\Models\CharacterTrueCrimeState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StarCooldownSkipsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function createCharacterWithStars(int $stars = 50): array
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SkipTester',
            'level' => 3,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'stars' => $stars,
            'respect' => 10,
            'is_onboarded' => true,
            'true_crime_last_played' => now()->subMinutes(10),
        ]);

        $memStat = CharacterMemoryStat::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addMinutes(15),
        ]);

        $c4Stat = CharacterConnectFourStat::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addMinutes(15),
        ]);

        CharacterTrueCrimeState::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addHours(24),
        ]);

        return [$user, $character, $memStat, $c4Stat];
    }

    public function test_true_crime_skip_cooldown_with_stars(): void
    {
        [$user, $character] = $this->createCharacterWithStars(30);

        $response = $this->actingAs($user)
            ->from(route('true_crime.index'))
            ->post(route('true_crime.skip_cooldown_stars'));

        $response->assertRedirect(route('true_crime.index'));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(15, $character->stars);
        $this->assertNull($character->true_crime_last_played);
    }

    public function test_memory_skip_cooldown_with_stars(): void
    {
        [$user, $character, $memStat] = $this->createCharacterWithStars(20);

        $response = $this->actingAs($user)
            ->from(route('memory.index'))
            ->post(route('memory.skip_cooldown_stars'));

        $response->assertRedirect(route('memory.index'));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(10, $character->stars);

        $memStat->refresh();
        $this->assertNull($memStat->cooldown_until);
    }

    public function test_connect_four_skip_cooldown_with_stars(): void
    {
        [$user, $character, , $c4Stat] = $this->createCharacterWithStars(25);

        $response = $this->actingAs($user)
            ->from(route('connect_four.index'))
            ->post(route('connect_four.skip_cooldown_stars'));

        $response->assertRedirect(route('connect_four.index'));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(15, $character->stars);

        $c4Stat->refresh();
        $this->assertNull($c4Stat->cooldown_until);
    }

    public function test_insufficient_stars_fails_gracefully(): void
    {
        [$user, $character, $memStat] = $this->createCharacterWithStars(2);

        $response = $this->actingAs($user)
            ->from(route('memory.index'))
            ->post(route('memory.skip_cooldown_stars'));

        $response->assertRedirect(route('memory.index'));
        $response->assertSessionHas('error');

        $character->refresh();
        $this->assertEquals(2, $character->stars);

        $memStat->refresh();
        $this->assertNotNull($memStat->cooldown_until);
    }
}

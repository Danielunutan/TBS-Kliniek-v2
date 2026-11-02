<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\CharacterMemoryStat;
use App\Models\CharacterTrueCrimeState;
use App\Models\User;
use App\Services\CooldownService;
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
            'name' => 'SkipTester_'.uniqid(),
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

    public function test_treatment_session_skip_costs_visually_stated_35_stars_and_clears_cooldown(): void
    {
        [$user, $character] = $this->createCharacterWithStars(72);

        $service = app(CooldownService::class);
        // Simulate a fresh 2-hour treatment session cooldown (~7129 seconds remaining like in screenshot)
        $service->setCooldown($character, 'treatment_session', 7129);
        $this->assertTrue($service->isOnCooldown($character, 'treatment_session'));

        // Skip via cooldown.skip route
        $response = $this->actingAs($user)
            ->from(route('treatment.index'))
            ->post(route('cooldown.skip'), ['action' => 'treatment_session']);

        $response->assertRedirect(route('treatment.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('35 Sterren', session('success'));

        $character->refresh();
        // 72 starting stars - 35 stars = 37 stars
        $this->assertEquals(37, $character->stars);
        $this->assertFalse($service->isOnCooldown($character, 'treatment_session'));
    }

    public function test_treatment_session_skip_fails_with_exact_cost_when_stars_insufficient(): void
    {
        [$user, $character] = $this->createCharacterWithStars(20);

        $service = app(CooldownService::class);
        $service->setCooldown($character, 'treatment_session', 7129);

        $response = $this->actingAs($user)
            ->from(route('treatment.index'))
            ->post(route('cooldown.skip'), ['action' => 'treatment_session']);

        $response->assertRedirect(route('treatment.index'));
        $response->assertSessionHas('error');
        // Error message must mention the actual cost (35), not an inflated dynamic cost (273)
        $this->assertStringContainsString('35 Sterren', session('error'));
        $this->assertStringNotContainsString('273', session('error'));

        $character->refresh();
        $this->assertEquals(20, $character->stars);
        $this->assertTrue($service->isOnCooldown($character, 'treatment_session'));
    }

    public function test_all_cooldown_actions_deduct_exact_base_skip_costs(): void
    {
        $service = app(CooldownService::class);

        $cases = [
            'hustle' => 10,
            'pickpocket' => 10,
            'locker' => 10,
            'package' => 10,
            'dagbesteding' => 10,
            'infirmary_palm' => 10,
            'infirmary_take' => 10,
            'extort' => 20,
            'produce_sigaretten' => 20,
            'produce_ritalin' => 35,
            'produce_wiet' => 40,
            'produce_telefoon' => 50,
            'treatment_session' => 35,
        ];

        foreach ($cases as $action => $expectedCost) {
            [$user, $character] = $this->createCharacterWithStars(100);
            $service->setCooldown($character, $action, 3600);

            $res = $this->actingAs($user)->post(route('cooldown.skip'), ['action' => $action]);
            $res->assertSessionHas('success');

            $character->refresh();
            $this->assertEquals(
                100 - $expectedCost,
                $character->stars,
                "Action '{$action}' should deduct exactly {$expectedCost} stars."
            );
            $this->assertFalse($service->isOnCooldown($character, $action));
        }
    }
}

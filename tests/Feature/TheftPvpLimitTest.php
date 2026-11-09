<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\TheftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TheftPvpLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_non_vip_can_rob_same_person_up_to_100_times_per_day(): void
    {
        $theftService = app(TheftService::class);

        $thiefUser = User::factory()->create();
        $thief = Character::factory()->create([
            'user_id' => $thiefUser->id,
            'level' => 10,
            'energy' => 1000,
            'max_energy' => 1000,
            'intelligence' => 50,
            'stamina' => 50,
            'is_vip' => false,
            'vip_expires_at' => null,
            'is_in_prison' => false,
        ]);

        $victim1 = Character::factory()->create([
            'name' => 'Slachtoffer Een',
            'cash' => 10000,
            'is_in_prison' => false,
            'protection_until' => null,
        ]);

        $victim2 = Character::factory()->create([
            'name' => 'Slachtoffer Twee',
            'cash' => 10000,
            'is_in_prison' => false,
            'protection_until' => null,
        ]);

        $today = now()->toDateString();
        $key1 = "pvp_thefts_{$thief->id}_{$victim1->id}_{$today}";

        // Simulate 99 thefts on victim1
        Cache::put($key1, 99, now()->endOfDay());

        // 100th attempt should succeed
        $res100 = $theftService->execute($thief, 'pocket', $victim1);
        $this->assertNotFalse(strpos($res100['message'], 'Je hebt') === false || strpos($res100['message'], 'gerold') !== false);
        $this->assertEquals(100, (int) Cache::get($key1));

        // 101st attempt should be blocked with the 100 limit message
        $res101 = $theftService->execute($thief, 'pocket', $victim1);
        $this->assertFalse($res101['success']);
        $this->assertStringContainsString('maximaal 100x per dag per persoon', $res101['message']);

        // Thief can still rob victim2 (limit is per person!)
        $key2 = "pvp_thefts_{$thief->id}_{$victim2->id}_{$today}";
        $resV2 = $theftService->execute($thief, 'pocket', $victim2);
        $this->assertEquals(1, (int) Cache::get($key2));
    }

    public function test_vip_can_rob_same_person_up_to_1000_times_per_day(): void
    {
        $theftService = app(TheftService::class);

        $thiefUser = User::factory()->create();
        $thief = Character::factory()->create([
            'user_id' => $thiefUser->id,
            'level' => 10,
            'energy' => 1000,
            'max_energy' => 1000,
            'intelligence' => 50,
            'stamina' => 50,
            'is_vip' => true,
            'vip_expires_at' => now()->addDays(30),
            'is_in_prison' => false,
        ]);

        $victim = Character::factory()->create([
            'name' => 'Rijk Slachtoffer',
            'cash' => 50000,
            'is_in_prison' => false,
            'protection_until' => null,
        ]);

        $today = now()->toDateString();
        $key = "pvp_thefts_{$thief->id}_{$victim->id}_{$today}";

        // At 100 thefts, VIP is not blocked!
        Cache::put($key, 100, now()->endOfDay());

        $res101 = $theftService->execute($thief, 'pocket', $victim);
        $this->assertEquals(101, (int) Cache::get($key));

        // Set to 999
        Cache::put($key, 999, now()->endOfDay());
        $res1000 = $theftService->execute($thief, 'pocket', $victim);
        $this->assertEquals(1000, (int) Cache::get($key));

        // 1001st attempt should be blocked with the 1000 limit message
        $res1001 = $theftService->execute($thief, 'pocket', $victim);
        $this->assertFalse($res1001['success']);
        $this->assertStringContainsString('maximaal 1000x per dag per persoon', $res1001['message']);
    }

    public function test_no_3_attempt_session_lockout_on_consecutive_thefts(): void
    {
        $theftService = app(TheftService::class);

        $thiefUser = User::factory()->create();
        $thief = Character::factory()->create([
            'user_id' => $thiefUser->id,
            'level' => 10,
            'energy' => 1000,
            'max_energy' => 1000,
            'intelligence' => 50,
            'stamina' => 50,
            'is_vip' => false,
            'is_in_prison' => false,
        ]);

        $victim = Character::factory()->create([
            'name' => 'Doelwit Slachtoffer',
            'cash' => 10000,
            'is_in_prison' => false,
            'protection_until' => null,
        ]);

        // Execute 5 consecutive thefts on the same victim
        for ($i = 1; $i <= 5; $i++) {
            $res = $theftService->execute($thief, 'pocket', $victim);
            $this->assertStringNotContainsString('3 keer achter elkaar', $res['message']);
        }

        $today = now()->toDateString();
        $key = "pvp_thefts_{$thief->id}_{$victim->id}_{$today}";
        $this->assertEquals(5, (int) Cache::get($key));
    }
}


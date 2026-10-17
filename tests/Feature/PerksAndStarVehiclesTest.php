<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\PrisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerksAndStarVehiclesTest extends TestCase
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
        ], $attributes));

        return [$user, $character];
    }

    public function test_player_can_buy_exclusive_star_vehicle_with_stars(): void
    {
        [$user, $character] = $this->createInmate(['stars' => 50]);

        $vehicle = Vehicle::create([
            'name' => 'Gepantserde Brabus G-Klasse (V12)',
            'slug' => 'brabus-g-klasse-v12-armored-test',
            'type' => 'auto',
            'brand' => 'Brabus',
            'price_cash' => 0,
            'price_stars' => 35,
            'speed_pct' => 88,
            'reliability_pct' => 98,
            'min_level' => 1,
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('garage.buy', $vehicle->id), ['payment' => 'stars']);

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(15, $character->stars);
        $this->assertTrue($character->characterVehicles()->where('vehicle_id', $vehicle->id)->exists());
    }

    public function test_player_cannot_buy_star_vehicle_without_enough_stars(): void
    {
        [$user, $character] = $this->createInmate(['stars' => 10]);

        $vehicle = Vehicle::create([
            'name' => 'Yamaha R1 Supersport 1000cc',
            'slug' => 'yamaha-r1-test',
            'type' => 'motor',
            'brand' => 'Yamaha',
            'price_cash' => 0,
            'price_stars' => 40,
            'speed_pct' => 98,
            'reliability_pct' => 85,
            'min_level' => 1,
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('garage.buy', $vehicle->id), ['payment' => 'stars']);

        $response->assertSessionHas('error');
        $character->refresh();
        $this->assertEquals(10, $character->stars);
        $this->assertFalse($character->characterVehicles()->where('vehicle_id', $vehicle->id)->exists());
    }

    public function test_player_can_buy_permanent_character_perk(): void
    {
        [$user, $character] = $this->createInmate(['stars' => 30]);

        $response = $this->actingAs($user)
            ->post(route('perks.buy', 'grotere_broekzakken'));

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(5, $character->stars);
        $this->assertTrue($character->hasPerk('grotere_broekzakken'));
    }

    public function test_player_cannot_buy_same_perk_twice(): void
    {
        [$user, $character] = $this->createInmate([
            'stars' => 50,
            'perks' => ['grotere_broekzakken'],
        ]);

        $response = $this->actingAs($user)
            ->post(route('perks.buy', 'grotere_broekzakken'));

        $response->assertSessionHas('error');
        $character->refresh();
        $this->assertEquals(50, $character->stars);
    }

    public function test_kluis_uitbreiding_perk_increases_storage_capacity(): void
    {
        [, $characterWithoutPerk] = $this->createInmate(['level' => 1]);
        $baseCapacity = $characterWithoutPerk->getStorageCapacity();

        [, $characterWithPerk] = $this->createInmate([
            'level' => 1,
            'perks' => ['kluis_uitbreiding'],
        ]);
        $upgradedCapacity = $characterWithPerk->getStorageCapacity();

        $this->assertEquals($baseCapacity + 25, $upgradedCapacity);
    }

    public function test_goede_advocaat_perk_reduces_isolation_time(): void
    {
        Carbon::setTestNow(now());
        $prison = app(PrisonService::class);

        [, $char1] = $this->createInmate();
        $prison->jailSeconds($char1, 100, 'Test');
        $char1->refresh();
        $normalSeconds = (int) now()->diffInSeconds($char1->prison_until);

        [, $char2] = $this->createInmate([
            'perks' => ['goede_advocaat'],
        ]);
        $prison->jailSeconds($char2, 100, 'Test');
        $char2->refresh();
        $reducedSeconds = (int) now()->diffInSeconds($char2->prison_until);

        $this->assertLessThan($normalSeconds, $reducedSeconds);
        $this->assertTrue($reducedSeconds >= 74 && $reducedSeconds <= 76);
        $this->assertTrue($normalSeconds >= 99 && $normalSeconds <= 101);

        Carbon::setTestNow();
    }

    public function test_legacy_cooldown_skip_informs_about_sponsor_ads(): void
    {
        [$user, $character] = $this->createInmate(['stars' => 10]);

        $response = $this->actingAs($user)
            ->post(route('cooldown.skip'), ['type' => 'hustle']);

        $response->assertSessionHas('info');
        $character->refresh();
        $this->assertEquals(10, $character->stars);
    }

    public function test_prison_release_via_sponsor_ad_is_unlimited(): void
    {
        [$user, $character] = $this->createInmate();

        // 1st time in prison
        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'prison_reason' => 'First violation',
        ]);

        $startRes1 = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'prison']);
        $startRes1->assertStatus(200);
        $token1 = $startRes1->json('token');

        // Fast-forward session watch duration for fallback
        Cache::put("tbs_ad_token_{$token1}", [
            'character_id' => $character->id,
            'reward_type' => 'prison',
            'token' => $token1,
            'started_at' => now()->subSeconds(20)->timestamp,
        ], 300);

        $claimRes1 = $this->actingAs($user)->postJson(route('sponsor.claim'), ['token' => $token1]);
        $claimRes1->assertStatus(200);
        $character->refresh();
        $this->assertFalse($character->isImprisoned());

        // 2nd time in prison on the same day!
        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(15),
            'prison_reason' => 'Second violation',
        ]);
        $user->refresh();

        $startRes2 = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'prison']);
        $startRes2->assertStatus(200);
        $token2 = $startRes2->json('token');

        Cache::put("tbs_ad_token_{$token2}", [
            'character_id' => $character->id,
            'reward_type' => 'prison',
            'token' => $token2,
            'started_at' => now()->subSeconds(20)->timestamp,
        ], 300);

        $claimRes2 = $this->actingAs($user)->postJson(route('sponsor.claim'), ['token' => $token2]);
        $claimRes2->assertStatus(200);
        $character->refresh();
        $this->assertFalse($character->isImprisoned());
    }
}

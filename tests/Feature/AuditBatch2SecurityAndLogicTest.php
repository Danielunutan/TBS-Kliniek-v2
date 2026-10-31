<?php

namespace Tests\Feature;

use App\Http\Controllers\FightController;
use App\Models\BloodFeud;
use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\Heist;
use App\Models\User;
use App\Models\VaultCode;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditBatch2SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_gang_heist_handles_missing_gang_respect_column_safely(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'GangLeader',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $gang = Gang::create([
            'name' => 'Test Syndicate',
            'tag' => 'SYND',
            'specialty' => 'combat',
            'leader_character_id' => $character->id,
            'treasury' => 50000,
            'masks' => 50,
        ]);

        $character->update(['gang_id' => $gang->id]);

        $vehicle = Vehicle::create([
            'name' => 'Audi RS6',
            'slug' => 'audi-rs6',
            'type' => 'sedan',
            'price_cash' => 85000,
            'base_speed' => 305,
            'speed' => 305,
            'top_speed' => 305,
            'handling' => 80,
            'acceleration' => 85,
            'reliability' => 90,
            'status' => 'available',
        ]);

        CharacterVehicle::create([
            'character_id' => $character->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 100,
            'is_in_garage' => true,
            'custom_tune_stage' => 1,
        ]);

        $heist = Heist::create([
            'name' => 'Waardetransport Overval',
            'slug' => 'waardetransport-overval',
            'description' => 'Test gang heist',
            'required_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 5,
            'required_vehicle_type' => 'any',
            'min_speed_required' => 100,
            'min_condition_required' => 50,
            'difficulty' => 'makkelijk',
            'payout_min' => 10000,
            'payout_max' => 20000,
            'respect_reward' => 10,
            'xp_reward' => 50,
            'is_gang_heist' => true,
            'gang_treasury_share_pct' => 50,
            'gang_respect_reward' => 250,
        ]);

        // Prior to our fix, when Schema::hasColumn('gangs', 'respect') was false,
        // Gang::where('id', $gang->id)->increment('respect', 250) threw QueryException.
        $this->assertFalse(Schema::hasColumn('gangs', 'respect'));

        $response = $this->actingAs($user)
            ->post(route('heists.launch', $heist));

        // Request must complete cleanly with redirect and not crash with 500 / QueryException
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('gangs', [
            'id' => $gang->id,
        ]);
    }

    public function test_solo_heist_atomically_deducts_and_guards_masks(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SoloBandit',
            'cash' => 5000,
            'bank' => 20000,
            'level' => 10,
            'energy' => 50,
            'max_energy' => 100,
            'masks' => 2,
            'is_onboarded' => true,
        ]);

        $vehicle = Vehicle::create([
            'name' => 'VW Golf GTI',
            'slug' => 'vw-golf-gti',
            'type' => 'hatchback',
            'price_cash' => 25000,
            'base_speed' => 240,
            'speed' => 240,
            'top_speed' => 240,
            'handling' => 70,
            'acceleration' => 75,
            'reliability' => 85,
            'status' => 'available',
        ]);

        CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 100,
            'is_in_garage' => true,
        ]);

        $heist = Heist::create([
            'name' => 'Kleine Juwelierskraak',
            'slug' => 'kleine-juwelierskraak',
            'description' => 'Test solo heist requiring 5 masks',
            'required_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 5,
            'required_vehicle_type' => 'any',
            'min_speed_required' => 100,
            'min_condition_required' => 50,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        // Attempt heist with only 2 masks (5 required)
        $response = $this->actingAs($user)
            ->post(route('heists.launch', $heist));

        $response->assertSessionHas('error');
        $this->assertEquals(2, $character->fresh()->masks);
    }

    public function test_hitman_avenging_blood_feud_does_not_crash_on_missing_gang_respect(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'Avenger',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 10,
            'bullets' => 200,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 50,
            'defense' => 50,
            'current_location' => 'De Luchtplaats',
            'is_onboarded' => true,
        ]);

        $gang = Gang::create([
            'name' => 'Avenger Mob',
            'tag' => 'AVNG',
            'specialty' => 'combat',
            'leader_character_id' => $attacker->id,
            'treasury' => 10000,
        ]);
        $attacker->update(['gang_id' => $gang->id]);

        $defenderUser = User::factory()->create();
        $defender = Character::create([
            'user_id' => $defenderUser->id,
            'name' => 'RivalTarget',
            'cash' => 2000,
            'bank' => 10000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 20,
            'max_health' => 100,
            'strength' => 10,
            'defense' => 10,
            'current_location' => 'De Luchtplaats',
            'is_onboarded' => true,
        ]);

        $bloodFeud = BloodFeud::create([
            'attacker_id' => $defender->id,
            'victim_id' => $attacker->id,
            'victim_gang_id' => $gang->id,
            'expires_at' => now()->addHours(24),
            'is_avenged' => false,
        ]);

        $this->assertFalse(Schema::hasColumn('gangs', 'respect'));

        $response = $this->actingAs($attackerUser)
            ->post(route('hitman.shoot'), [
                'target_id' => $defender->id,
                'bullets' => 50,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($bloodFeud->fresh()->is_avenged);
        $this->assertEquals($attacker->id, $bloodFeud->fresh()->avenged_by_id);
    }

    public function test_hitman_backfire_tenth_liquidation_sets_coma_and_medical_shield(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'BackfireVictim',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 10,
            'bullets' => 200,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 1,
            'defense' => 1,
            'perks' => ['liquidation_streak_deaths' => 9], // Next death will be 10th
            'current_location' => 'De Luchtplaats',
            'is_onboarded' => true,
        ]);

        $defenderUser = User::factory()->create();
        $defender = Character::create([
            'user_id' => $defenderUser->id,
            'name' => 'TankDefender',
            'cash' => 2000,
            'bank' => 10000,
            'level' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 500,
            'defense' => 500,
            'vest_until' => now()->addHours(12),
            'current_location' => 'De Luchtplaats',
            'is_onboarded' => true,
        ]);

        // When attackerStreak reaches 10, attacker must enter Code Blauw (health 0, coma_until set, protection_until set)
        $attacker->respect = 50;
        $attackerStreak = $attacker->incrementLiquidationStreakDeaths();
        $this->assertEquals(10, $attackerStreak);

        if ($attackerStreak >= Character::MAX_LIQUIDATIONS_BEFORE_SERUM) {
            $attacker->health = 0;
            $attacker->coma_until = now()->addHours(6);
            $attacker->protection_until = now()->addMinutes(30);
            $attacker->save();
        }

        $fresh = $attacker->fresh();
        $this->assertEquals(0, $fresh->health);
        $this->assertNotNull($fresh->coma_until);
        $this->assertNotNull($fresh->protection_until);
        $this->assertTrue($fresh->protection_until->isFuture());
    }

    public function test_shop_buy_bundle_with_invalid_recipient_returns_friendly_error(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'GenerousPlayer',
            'cash' => 10000,
            'bank' => 50000,
            'stars' => 500,
            'level' => 10,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('shop.buy-bundle'), [
                'bundle_id' => 'kliniek_starter',
                'quantity' => 1,
                'recipient_id' => 999999, // Non-existent recipient
            ]);

        $response->assertSessionHas('error', 'De gekozen ontvanger bestaat niet.');
        // Stars must not be deducted
        $this->assertEquals(500, $character->fresh()->stars);
    }

    public function test_treatment_generates_valid_unique_vault_code(): void
    {
        $code = VaultCode::generateUniqueCode('VERLOF');

        $this->assertStringStartsWith('VERLOF-', $code);
        $this->assertMatchesRegularExpression('/^VERLOF-[A-Z0-9]+$/', $code);
    }

    public function test_fight_commit_pending_battle_rejects_mismatched_attacker(): void
    {
        $user1 = User::factory()->create();
        $attacker1 = Character::create([
            'user_id' => $user1->id,
            'name' => 'RealAttacker',
            'cash' => 1000,
            'level' => 5,
            'is_onboarded' => true,
        ]);

        $user2 = User::factory()->create();
        $attacker2 = Character::create([
            'user_id' => $user2->id,
            'name' => 'ImpostorAttacker',
            'cash' => 1000,
            'level' => 5,
            'is_onboarded' => true,
        ]);

        $defender = Character::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Defender',
            'cash' => 5000,
            'level' => 5,
            'is_onboarded' => true,
        ]);

        session([
            'pending_arena_battle' => [
                'attacker_id' => $attacker1->id,
                'defender_id' => $defender->id,
                'won' => true,
                'resolved' => false,
                'stolen_cash' => 500,
            ],
        ]);

        $controller = app(FightController::class);
        $result = $controller->commitPendingBattle($attacker2);

        // Must reject since attacker2 is not the attacker in session
        $this->assertNull($result);
    }
}

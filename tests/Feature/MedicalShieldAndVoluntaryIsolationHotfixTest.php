<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterCooldown;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalShieldAndVoluntaryIsolationHotfixTest extends TestCase
{
    use RefreshDatabase;

    private function createCharacter(array $attributes = []): Character
    {
        $user = User::factory()->create();

        return Character::factory()->create(array_merge([
            'user_id' => $user->id,
            'level' => 10,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 50000,
            'bank' => 200000,
            'is_in_prison' => false,
            'coma_until' => null,
            'stars' => 100,
        ], $attributes));
    }

    public function test_medical_shield_distinguishes_from_voluntary_isolation(): void
    {
        $char = $this->createCharacter();
        $char->grantMedicalShield(30);
        $char->refresh();

        $this->assertTrue($char->hasIsolationProtection());
        $this->assertTrue($char->hasMedicalShield());
        $this->assertFalse($char->isInVoluntaryIsolation());
        $this->assertGreaterThan(0, $char->getMedicalShieldRemainingSeconds());
        $this->assertFalse($char->isActionBlockedByIsolation('missions.index'));
    }

    public function test_views_show_medical_shield_instead_of_voluntary_isolation_banner(): void
    {
        $char = $this->createCharacter();
        $char->grantMedicalShield(30);

        // 1. Luchtplaats
        $fightResp = $this->actingAs($char->user)->get(route('fight.index'));
        $fightResp->assertOk();
        $fightResp->assertSee('Medisch Herstelschild Actief');
        $fightResp->assertDontSee('Vrijwillige Isolatie Actief');

        // 2. Theft
        $theftResp = $this->actingAs($char->user)->get(route('theft.index'));
        $theftResp->assertOk();
        $theftResp->assertSee('Medisch Herstelschild Actief');
        $theftResp->assertDontSee('Vrijwillige Isolatie Actief');

        // 3. Hitman
        $hitmanResp = $this->actingAs($char->user)->get(route('hitman.index'));
        $hitmanResp->assertOk();
        $hitmanResp->assertSee('Medisch Herstelschild Actief');
        $hitmanResp->assertDontSee('Vrijwillige Isolatie Actief');

        // 4. Voluntary Isolation page
        $isoResp = $this->actingAs($char->user)->get(route('isolation.index'));
        $isoResp->assertOk();
        $isoResp->assertSee('Medisch Herstelschild Actief (Cel niet vergrendeld)');
        $isoResp->assertDontSee('Wil je je celdeur vroegtijdig ontgrendelen om weer aan te vallen of op pad te gaan?');
    }

    public function test_theft_controller_allows_action_under_medical_shield_without_break_isolation(): void
    {
        $char = $this->createCharacter([
            'level' => 15,
            'energy' => 100,
        ]);
        $char->grantMedicalShield(30);
        $char->refresh();

        $victim = $this->createCharacter([
            'level' => 10,
            'cash' => 50000,
            'protection_until' => null,
        ]);

        $resp = $this->actingAs($char->user)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'victim_id' => $victim->id,
        ]);

        // Action is not blocked by voluntary isolation redirect/error
        $this->assertNotEquals(route('isolation.index'), session('error_url'));
        $this->assertStringNotContainsString('Je cel is vergrendeld in Vrijwillige Isolatie', (string) session('error'));
    }

    public function test_theft_controller_still_blocks_when_in_actual_voluntary_isolation(): void
    {
        $char = $this->createCharacter([
            'level' => 15,
            'energy' => 100,
            'protection_until' => now()->addHours(6),
        ]);
        // No medical shield perk, so character is locked in voluntary isolation
        $this->assertTrue($char->isInVoluntaryIsolation());

        $victim = $this->createCharacter(['cash' => 50000]);

        $resp = $this->actingAs($char->user)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'victim_id' => $victim->id,
        ]);

        $resp->assertRedirect();
        $resp->assertSessionHas('error');
        $this->assertStringContainsString('Vrijwillige Isolatie', session('error'));
        $this->assertEquals(route('isolation.index'), session('error_url'));
    }

    public function test_migration_unlocks_legacy_protection_into_medical_shield(): void
    {
        $legacyChar = $this->createCharacter([
            'protection_until' => now()->addHours(8),
        ]);
        // Initially in voluntary isolation because medical_shield_until is missing
        $this->assertTrue($legacyChar->isInVoluntaryIsolation());

        // Create a cooldown that might have been incurred
        CharacterCooldown::create([
            'character_id' => $legacyChar->id,
            'action' => 'isolation_cooldown',
            'cooldown_until' => now()->addHours(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_04_121000_unlock_legacy_isolation_into_medical_shields.php');
        $migration->up();

        $legacyChar->refresh();

        // Still protected against attacks
        $this->assertTrue($legacyChar->hasIsolationProtection());
        // But unlocked as medical shield!
        $this->assertTrue($legacyChar->hasMedicalShield());
        $this->assertFalse($legacyChar->isInVoluntaryIsolation());

        // Cooldown cleared
        $this->assertDatabaseMissing('character_cooldowns', [
            'character_id' => $legacyChar->id,
            'action' => 'isolation_cooldown',
        ]);
    }

    public function test_early_isolation_termination_sets_dynamic_cooldown_matching_remaining_time(): void
    {
        $char = $this->createCharacter([
            'cash' => 500000,
            'protection_until' => now()->addMinutes(6),
        ]);
        $this->assertTrue($char->isInVoluntaryIsolation());

        $this->actingAs($char->user);
        $resp = $this->post(route('isolation.terminate'));
        $resp->assertSessionHas('success');
        $this->assertStringContainsString('6 min afkoeltijd', session('success'));

        $char->refresh();
        $this->assertFalse($char->hasIsolationProtection());
        $this->assertTrue($char->isOnCooldown('isolation_cooldown'));
        // Cooldown should be around 360 seconds (6 minutes), definitely not 7200 seconds (2 hours)
        $this->assertLessThanOrEqual(360, $char->cooldownRemaining('isolation_cooldown'));
        $this->assertGreaterThanOrEqual(300, $char->cooldownRemaining('isolation_cooldown'));
    }

    public function test_early_isolation_termination_caps_cooldown_at_two_hours_for_long_remaining_time(): void
    {
        $char = $this->createCharacter([
            'cash' => 500000,
            'protection_until' => now()->addHours(11),
        ]);
        $this->assertTrue($char->isInVoluntaryIsolation());

        $this->actingAs($char->user);
        $resp = $this->post(route('isolation.terminate'));
        $resp->assertSessionHas('success');
        $this->assertStringContainsString('120 min afkoeltijd', session('success'));

        $char->refresh();
        $this->assertFalse($char->hasIsolationProtection());
        $this->assertTrue($char->isOnCooldown('isolation_cooldown'));
        // Cooldown capped at 2 hours (7200 seconds)
        $this->assertLessThanOrEqual(7200, $char->cooldownRemaining('isolation_cooldown'));
        $this->assertGreaterThan(7000, $char->cooldownRemaining('isolation_cooldown'));
    }
}

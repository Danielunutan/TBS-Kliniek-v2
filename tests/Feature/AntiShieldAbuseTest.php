<?php

namespace Tests\Feature;

use App\Models\BloodFeud;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntiShieldAbuseTest extends TestCase
{
    use RefreshDatabase;

    public function test_pickpocket_breaks_medical_shield(): void
    {
        $user = User::factory()->create();
        $char = Character::factory()->create([
            'user_id' => $user->id,
            'energy' => 50,
            'cash' => 1000,
        ]);

        // Grant 60 min medical shield
        $char->grantMedicalShield(60);
        $char->save();
        $this->assertTrue($char->hasMedicalShield());

        $response = $this->actingAs($user)->post(route('pickpocket.steal'), [
            'action_id' => 1,
            'technique' => 'schaduw',
        ]);

        $char->refresh();
        $this->assertFalse($char->hasMedicalShield());
    }

    public function test_blood_feud_retaliation_does_not_grant_post_coma_shield_to_aggressor(): void
    {
        $userA = User::factory()->create();
        $charA = Character::factory()->create([
            'user_id' => $userA->id,
            'level' => 10,
            'bullets' => 200,
            'energy' => 50,
            'current_location' => 'Administratie',
        ]);

        $userB = User::factory()->create();
        $charB = Character::factory()->create([
            'user_id' => $userB->id,
            'level' => 10,
            'current_location' => 'Administratie',
            'health' => 100,
        ]);

        // Create an active BloodFeud where B is attacker and A is victim (so A has legitimate retaliation on B)
        BloodFeud::create([
            'attacker_id' => $charB->id,
            'victim_id' => $charA->id,
            'expires_at' => now()->addDays(3),
            'is_avenged' => false,
        ]);

        // A liquidates B via Blood Feud
        $response = $this->actingAs($userA)->post(route('hitman.shoot'), [
            'target_id' => $charB->id,
            'bullets' => 50,
        ]);

        $response->assertSessionHas('success');
        $charB->refresh();

        // Char B should be in coma (10 min)
        $this->assertTrue($charB->isComatose());
        // Protection until should be at most 11 minutes (coma), NOT 70 minutes (coma + 60 min shield)
        $this->assertLessThanOrEqual(
            now()->addMinutes(Character::LIQUIDATION_IC_COMA_MINUTES + 1)->timestamp,
            $charB->protection_until->timestamp
        );
    }
}

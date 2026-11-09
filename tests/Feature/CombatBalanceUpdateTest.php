<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CombatBalanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_syndicate_grades_catalog_has_capped_bullet_requirements(): void
    {
        $catalog = Character::syndicateGradesCatalog();

        $this->assertEquals(450, $catalog[1]['min_bullets']);
        $this->assertEquals(10, $catalog[30]['min_bullets']);
        $this->assertLessThanOrEqual(450, $catalog[1]['min_bullets']);

        foreach ($catalog as $grade) {
            $this->assertLessThanOrEqual(450, $grade['min_bullets']);
            $this->assertGreaterThan(0, $grade['min_bullets']);
        }
    }

    public function test_liquidation_leaves_bank_fully_untouched_and_only_loots_cash(): void
    {
        $attackerUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $attackerUser->character;
        $attacker->update([
            'cash' => 0,
            'bullets' => 1000,
            'strength' => 500,
            'level' => 30,
            'energy' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'protection_until' => null,
            'perks' => ['syndicate_grade_override' => 25],
        ]);

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update([
            'cash' => 50000,
            'bank' => 5000000,
            'level' => 30,
            'defense' => 50,
            'vest_until' => null,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'protection_until' => null,
            'health' => 100,
            'coma_until' => null,
        ]);

        $this->actingAs($attackerUser);

        $response = $this->post(route('hitman.shoot'), [
            'target_id' => $defender->id,
            'bullets' => 100,
        ]);

        $response->assertSessionHas('success');

        $defender->refresh();
        $attacker->refresh();

        // 5 million bank is 100% intact!
        $this->assertEquals(5000000, $defender->bank);
        // 45% of 50,000 cash = 22,500 stolen
        $this->assertEquals(27500, $defender->cash);
        $this->assertEquals(22500, $attacker->cash);

        // Defender enters 10 min coma and receives 70 min total protection (10 min coma + 60 min medical shield)
        $this->assertTrue($defender->isComatose());
        $this->assertTrue($defender->hasMedicalShield());
    }

    public function test_symmetric_level_restriction_prevents_low_level_attacking_high_level(): void
    {
        $attackerUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $attackerUser->character;
        $attacker->update([
            'level' => 5,
            'bullets' => 100,
            'energy' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'protection_until' => null,
        ]);

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update([
            'level' => 50,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'protection_until' => null,
        ]);

        $this->actingAs($attackerUser);

        $response = $this->post(route('hitman.shoot'), [
            'target_id' => $defender->id,
            'bullets' => 50,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('levelverschil', session('error'));
    }

    public function test_attack_with_medical_shield_requires_confirmation_to_break(): void
    {
        $attackerUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $attackerUser->character;
        $attacker->update([
            'level' => 30,
            'energy' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'protection_until' => Carbon::now()->addMinutes(45),
            'perks' => ['medical_shield_until' => Carbon::now()->addMinutes(45)->timestamp],
        ]);

        $this->assertTrue($attacker->hasMedicalShield());

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update([
            'level' => 30,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'protection_until' => null,
        ]);

        $this->actingAs($attackerUser);

        // Without confirmation: blocked with warning
        $response = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
            'confirm_break_isolation' => 0,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Medisch Infuus-Schild', session('error'));

        // With confirmation: succeeds and breaks shield
        $responseConfirm = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
            'confirm_break_isolation' => 1,
        ]);

        $responseConfirm->assertSessionHas('arena_battle');
        $attacker->refresh();
        $this->assertFalse($attacker->hasMedicalShield());
    }
}

<?php

namespace Tests\Feature;

use App\Http\Controllers\BattlePassController;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BattlePassTest extends TestCase
{
    use RefreshDatabase;

    public function test_battle_pass_has_60_levels_for_3_month_season(): void
    {
        $config = BattlePassController::getPassConfig();

        $this->assertCount(60, $config);
        $this->assertArrayHasKey(1, $config);
        $this->assertArrayHasKey(60, $config);

        // Verify masks are scarce (not too many masks)
        $freeMasks = 0;
        $vip3Masks = 0;
        foreach ($config as $lvl) {
            $freeMasks += $lvl['tracks']['free']['rewards']['masks'] ?? 0;
            $vip3Masks += $lvl['tracks']['vip3']['rewards']['masks'] ?? 0;
        }

        $this->assertLessThanOrEqual(3, $freeMasks);
        $this->assertLessThanOrEqual(20, $vip3Masks);
    }

    public function test_player_can_view_battle_pass_and_claim_strength_xp_vault_tokens_and_cash(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'PassTester',
            'level' => 5,
            'xp' => 0,
            'cash' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 10,
            'vault_tokens' => 0,
            'respect' => 0,
            'stars' => 0,
            'masks' => 0,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get(route('battlepass.index'));
        $response->assertStatus(200);
        $response->assertSee('TBS Kliniek Battle Pass');
        $response->assertSee('60 Levels');

        // Claim Level 2 Free (gives cash + strength + xp)
        $claimRes = $this->actingAs($user)->post(route('battlepass.claim'), [
            'level' => 2,
            'track' => 'free',
        ]);
        $claimRes->assertRedirect();

        $character->refresh();
        $this->assertGreaterThan(10, $character->strength);
        $this->assertGreaterThan(100, $character->cash);
        $this->assertContains('lvl_2_free', $character->perks['battle_pass_claims'] ?? []);

        // Claim Level 3 Free (gives vault_tokens / Kluiscodex)
        $this->actingAs($user)->post(route('battlepass.claim'), [
            'level' => 3,
            'track' => 'free',
        ]);
        $character->refresh();
        $this->assertGreaterThanOrEqual(1, $character->vault_tokens);
    }

    public function test_player_can_claim_level_up_to_60(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'BossPassTester',
            'level' => 60,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'is_vip' => true,
            'vip_until' => now()->addDays(30),
            'perks' => ['supporter_tier' => 3],
            'is_onboarded' => true,
        ]);

        $res = $this->actingAs($user)->post(route('battlepass.claim'), [
            'level' => 60,
            'track' => 'vip3',
        ]);

        $res->assertRedirect();
        $character->refresh();
        $this->assertContains('lvl_60_vip3', $character->perks['battle_pass_claims'] ?? []);
    }
}

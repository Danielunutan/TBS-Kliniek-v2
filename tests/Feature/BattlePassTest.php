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

    public function test_battle_pass_has_150_levels_and_proper_vip_rank_titles(): void
    {
        $config = BattlePassController::getPassConfig();

        $this->assertCount(150, $config);
        $this->assertArrayHasKey(1, $config);
        $this->assertArrayHasKey(150, $config);

        // Verify masks are scarce (not too many masks across 150 levels)
        $freeMasks = 0;
        $vip3Masks = 0;
        foreach ($config as $lvl) {
            $freeMasks += $lvl['tracks']['free']['rewards']['masks'] ?? 0;
            $vip3Masks += $lvl['tracks']['vip3']['rewards']['masks'] ?? 0;
        }

        $this->assertLessThanOrEqual(5, $freeMasks);
        $this->assertLessThanOrEqual(25, $vip3Masks);
    }

    public function test_player_can_view_battle_pass_with_vip_shop_buttons_and_claim_rewards(): void
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

        $this->assertSame(5, $character->getUnclaimedBattlePassCount());

        // Check that dashboard renders the unclaimed Battle Pass popup
        $dashResponse = $this->actingAs($user)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Ongeclaimde Battle Pass Buit!');

        $response = $this->actingAs($user)->get(route('battlepass.index'));
        $response->assertStatus(200);
        $response->assertSee('TBS Kliniek Battle Pass');
        $response->assertSee('150 Niveaus');
        $response->assertSee('TBS V.I.P. Lid (VIP 1)');
        $response->assertSee('Gangster Elite V.I.P. (VIP 2)');
        $response->assertSee('Kliniek Baas V.I.P. (VIP 3)');
        $response->assertSee('VIP Donaties &amp; Perks', false);

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

        // Claim Level 3 Free via fast AJAX JSON header (gives vault_tokens / Codes)
        $fastRes = $this->actingAs($user)
            ->withHeader('X-BP-Fast', '1')
            ->postJson(route('battlepass.claim'), [
                'level' => 3,
                'track' => 'free',
            ]);
        $fastRes->assertOk()->assertJsonPath('success', true);
        $character->refresh();
        $this->assertGreaterThanOrEqual(1, $character->vault_tokens);
    }

    public function test_player_can_claim_level_up_to_150(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'BossPassTester',
            'level' => 150,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'is_vip' => true,
            'vip_expires_at' => now()->addDays(30),
            'perks' => ['supporter_tier' => 3],
            'is_onboarded' => true,
        ]);

        $res = $this->actingAs($user)->post(route('battlepass.claim'), [
            'level' => 150,
            'track' => 'vip3',
        ]);

        $res->assertRedirect();
        $character->refresh();
        $this->assertContains('lvl_150_vip3', $character->perks['battle_pass_claims'] ?? []);
    }
}

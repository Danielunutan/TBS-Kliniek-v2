<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaultSeasonCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_redeem_season_starter_codes(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SeizoenBoef',
            'cash' => 250,
            'bank' => 1000,
            'stars' => 10,
            'vault_tokens' => 5,
            'respect' => 10,
            'masks' => 0,
            'level' => 1,
            'xp' => 0,
            'claimed_level_rewards' => [],
        ]);

        // Redeem SEIZOEN2
        $response = $this->actingAs($user)->post(route('vault.redeem'), [
            'codes' => 'SEIZOEN2',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(1000 + 2500, $character->bank);
        $this->assertEquals(10 + 1, $character->stars);
        $this->assertEquals(5 + 3, $character->vault_tokens);
        $this->assertEquals(2, $character->masks);
        $this->assertEquals(10 + 15, $character->respect);
        $this->assertContains('season2_code_SEIZOEN2', $character->claimed_level_rewards);

        // Cannot redeem SEIZOEN2 a second time
        $response2 = $this->actingAs($user)->post(route('vault.redeem'), [
            'codes' => 'SEIZOEN2',
        ]);
        $response2->assertSessionHas('error');
    }
}

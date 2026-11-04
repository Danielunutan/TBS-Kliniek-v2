<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\SocialRewardClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityHubAndFollowRewardsTest extends TestCase
{
    use RefreshDatabase;

    private function createPlayerWithCharacter(): array
    {
        $user = User::factory()->create([
            'email' => 'patiënt_'.uniqid().'@tbs-kliniek.nl',
            'role' => 'user',
        ]);

        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Boef_'.uniqid(),
            'cash' => 10000,
            'bank' => 5000,
            'respect' => 100,
            'energy' => 50,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
            'last_active_at' => now(),
        ]);

        return [$user, $character];
    }

    public function test_guest_can_view_community_hub(): void
    {
        $response = $this->get(route('community.index'));

        $response->assertStatus(200);
        $response->assertSee('Community Hub & Beloningen');
        $response->assertSee('Officiële Discord');
        $response->assertSee('YouTube Kanaal');
        $response->assertSee('TikTok Kanaal');
        $response->assertSee('Instagram Profiel');
        $response->assertSee('Twitter / X');
        $response->assertSee('Hyves (TBS Kliniek)');
        $response->assertSee('Log in om te Claimen');
    }

    public function test_guest_cannot_claim_reward(): void
    {
        $response = $this->post(route('community.claim', 'discord'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_player_can_claim_platform_reward(): void
    {
        [$user, $character] = $this->createPlayerWithCharacter();

        $initialCash = $character->cash;
        $initialRespect = $character->respect;
        $initialEnergy = $character->energy;

        $response = $this->actingAs($user)->post(route('community.claim', 'discord'));

        $response->assertRedirect(route('community.index'));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals($initialCash + 25000, $character->cash);
        $this->assertEquals($initialRespect + 25, $character->respect);
        $this->assertEquals($initialEnergy + 15, $character->energy);

        $this->assertDatabaseHas('social_reward_claims', [
            'user_id' => $user->id,
            'character_id' => $character->id,
            'platform' => 'discord',
            'reward_cash' => 25000,
        ]);
    }

    public function test_player_cannot_claim_same_platform_twice(): void
    {
        [$user, $character] = $this->createPlayerWithCharacter();

        // First claim succeeds
        $this->actingAs($user)->post(route('community.claim', 'youtube'));
        $character->refresh();
        $cashAfterFirst = $character->cash;

        // Second claim attempt fails
        $response = $this->actingAs($user)->post(route('community.claim', 'youtube'));
        $response->assertRedirect(route('community.index'));
        $response->assertSessionHas('error');

        $character->refresh();
        $this->assertEquals($cashAfterFirst, $character->cash);
        $this->assertEquals(1, SocialRewardClaim::where('user_id', $user->id)->where('platform', 'youtube')->count());
    }

    public function test_player_can_claim_multiple_different_platforms(): void
    {
        [$user, $character] = $this->createPlayerWithCharacter();

        $this->actingAs($user)->post(route('community.claim', 'tiktok'));
        $this->actingAs($user)->post(route('community.claim', 'instagram'));
        $this->actingAs($user)->post(route('community.claim', 'hyves'));

        $this->assertEquals(3, SocialRewardClaim::where('user_id', $user->id)->count());
    }

    public function test_authenticated_player_can_claim_platform_reward_via_json(): void
    {
        [$user, $character] = $this->createPlayerWithCharacter();

        $response = $this->actingAs($user)->postJson(route('community.claim', 'youtube'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('platform', 'youtube');

        $character->refresh();
        $this->assertEquals(35000, $character->cash);
        $this->assertEquals(125, $character->respect);
        $this->assertEquals(65, $character->energy);
        $this->assertTrue(SocialRewardClaim::where('user_id', $user->id)->where('platform', 'youtube')->exists());
    }

    public function test_player_with_push_subscription_can_claim_push_reward(): void
    {
        [$user, $character] = $this->createPlayerWithCharacter();
        $endpoint = 'https://push.example.test/community';
        $user->pushSubscriptions()->create(['endpoint' => $endpoint, 'endpoint_hash' => hash('sha256', $endpoint)]);

        $response = $this->actingAs($user)->postJson(route('community.claim', 'push'), [
            'is_standalone' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('platform', 'push');

        $character->refresh();
        $this->assertEquals(35000, $character->cash);
        $this->assertEquals(125, $character->respect);
        $this->assertEquals(70, $character->energy);
        $this->assertTrue(SocialRewardClaim::where('user_id', $user->id)->where('platform', 'push')->exists());
    }

    public function test_push_reward_rejected_when_not_subscribed_and_not_standalone(): void
    {
        [$user, $character] = $this->createPlayerWithCharacter();

        $response = $this->actingAs($user)->postJson(route('community.claim', 'push'), [
            'is_standalone' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false);

        $this->assertFalse(SocialRewardClaim::where('user_id', $user->id)->where('platform', 'push')->exists());
    }

    public function test_footer_does_not_contain_old_ssl_and_avg_pills(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('SSL 256-bit');
        $response->assertDontSee('SSL Beveiligd');
        $response->assertSee('Community Hub &amp; Beloningen', false);
    }
}

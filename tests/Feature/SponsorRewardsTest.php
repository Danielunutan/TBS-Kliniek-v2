<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Models\VaultCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SponsorRewardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createTestUser(): array
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SponsorHero',
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'respect' => 10,
            'masks' => 2,
            'ad_mask_progress' => 0,
            'ad_vault_progress' => 0,
            'is_onboarded' => true,
        ]);

        return [$user, $character];
    }

    protected function simulateAdWatch(User $user, Character $character, string $rewardType): TestResponse
    {
        $token = Str::random(32);
        $adData = [
            'token' => $token,
            'started_at' => now()->subSeconds(20)->timestamp,
            'character_id' => $character->id,
            'reward_type' => $rewardType,
            'robin_reward_id' => null,
        ];

        Cache::put("tbs_ad_token_{$token}", $adData, now()->addMinutes(30));
        Cache::put("tbs_ad_char_{$character->id}", $adData, now()->addMinutes(30));

        return $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);
    }

    public function test_sponsor_page_shows_respect_and_removes_cooldown_reset(): void
    {
        [$user, $character] = $this->createTestUser();

        $response = $this->actingAs($user)->get(route('sponsor.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Reset Alle Cooldowns');
        $response->assertSee('+1 Respect');
        $response->assertSee('3 Ads per Masker');
        $response->assertSee('Geheime Kluiscode Voucher');
    }

    public function test_respect_reward_grants_one_respect(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialRespect = $character->respect;

        $response = $this->simulateAdWatch($user, $character, 'respect');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $character->refresh();
        $this->assertEquals($initialRespect + 1, $character->respect);
    }

    public function test_mask_reward_requires_three_ads(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialMasks = $character->masks;

        // Ad 1: progress -> 1, masks unchanged
        $res1 = $this->simulateAdWatch($user, $character, 'mask');
        $res1->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->ad_mask_progress);
        $this->assertEquals($initialMasks, $character->masks);

        // Ad 2: progress -> 2, masks unchanged
        $res2 = $this->simulateAdWatch($user, $character, 'mask');
        $res2->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->ad_mask_progress);
        $this->assertEquals($initialMasks, $character->masks);

        // Ad 3: progress -> 0, masks -> +1!
        $res3 = $this->simulateAdWatch($user, $character, 'mask');
        $res3->assertStatus(200);
        $character->refresh();
        $this->assertEquals(0, $character->ad_mask_progress);
        $this->assertEquals($initialMasks + 1, $character->masks);
    }

    public function test_vault_code_reward_requires_three_ads(): void
    {
        [$user, $character] = $this->createTestUser();

        // Ad 1: progress -> 1
        $this->simulateAdWatch($user, $character, 'vault_code');
        $character->refresh();
        $this->assertEquals(1, $character->ad_vault_progress);
        $this->assertEquals(0, VaultCode::where('character_id', $character->id)->count());

        // Ad 2: progress -> 2
        $this->simulateAdWatch($user, $character, 'vault_code');
        $character->refresh();
        $this->assertEquals(2, $character->ad_vault_progress);
        $this->assertEquals(0, VaultCode::where('character_id', $character->id)->count());

        // Ad 3: progress -> 0, VaultCode created!
        $this->simulateAdWatch($user, $character, 'vault_code');
        $character->refresh();
        $this->assertEquals(0, $character->ad_vault_progress);
        $this->assertEquals(1, VaultCode::where('character_id', $character->id)->count());

        $vaultCode = VaultCode::where('character_id', $character->id)->first();
        $this->assertStringStartsWith('SPONSOR-', $vaultCode->code);
    }
}

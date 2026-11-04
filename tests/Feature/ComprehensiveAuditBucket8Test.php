<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GuestbookEntry;
use App\Models\SocialRewardClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ComprehensiveAuditBucket8Test extends TestCase
{
    use RefreshDatabase;

    private function createPlayer(array $characterAttrs = []): array
    {
        $user = User::factory()->create([
            'email' => 'patient_'.uniqid().'@tbs-kliniek.nl',
            'role' => 'user',
        ]);

        $defaultAttrs = [
            'user_id' => $user->id,
            'name' => 'Boef_'.uniqid(),
            'cash' => 20000,
            'bank' => 10000,
            'respect' => 150,
            'energy' => 60,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 2,
            'xp' => 10,
            'is_vip' => false,
            'vip_expires_at' => null,
            'last_active_at' => now(),
        ];

        $character = Character::create(array_merge($defaultAttrs, $characterAttrs));

        return [$user, $character];
    }

    public function test_character_and_user_correctly_detect_lifetime_vip_by_year_2099(): void
    {
        [$user, $character] = $this->createPlayer([
            'is_vip' => true,
            'vip_expires_at' => Carbon::parse('2099-12-31 23:59:59'),
        ]);

        $this->assertTrue($character->isLifetimeVip());
        $this->assertTrue($character->isVip());
        $this->assertTrue($user->isLifetimeVip());
        $this->assertTrue($user->isVip());
    }

    public function test_character_and_user_correctly_detect_lifetime_vip_by_tier_4(): void
    {
        [$user, $character] = $this->createPlayer([
            'is_vip' => false,
            'vip_expires_at' => null,
            'perks' => ['supporter_tier' => 4],
        ]);

        $this->assertTrue($character->isLifetimeVip());
        $this->assertTrue($character->isVip());
        $this->assertTrue($user->isLifetimeVip());
        $this->assertTrue($user->isVip());
    }

    public function test_character_detects_lifetime_vip_when_flag_is_true_without_expiration(): void
    {
        [$user, $character] = $this->createPlayer([
            'is_vip' => true,
            'vip_expires_at' => null,
        ]);

        $this->assertTrue($character->isLifetimeVip());
        $this->assertTrue($character->isVip());
        $this->assertTrue($user->isVip());
    }

    public function test_lifetime_vip_expiry_is_not_overwritten_by_discord_reward(): void
    {
        $farFuture = Carbon::parse('2099-12-31 23:59:59');
        [$user, $character] = $this->createPlayer([
            'is_vip' => true,
            'vip_expires_at' => $farFuture,
        ]);

        $result = $character->claimDiscordReward();
        $this->assertTrue($result);

        $character->refresh();
        $this->assertTrue($character->isLifetimeVip());
        $this->assertEquals($farFuture->format('Y-m-d H:i:s'), $character->vip_expires_at->format('Y-m-d H:i:s'));
    }

    public function test_normal_vip_expiry_is_extended_by_discord_reward(): void
    {
        $initialExpiry = now()->addDays(5);
        [$user, $character] = $this->createPlayer([
            'is_vip' => true,
            'vip_expires_at' => $initialExpiry,
        ]);

        $result = $character->claimDiscordReward();
        $this->assertTrue($result);

        $character->refresh();
        $this->assertFalse($character->isLifetimeVip());
        $this->assertTrue($character->vip_expires_at->gt(now()->addDays(7)));
    }

    public function test_avatar_url_sanitizes_malicious_protocols_to_prevent_xss(): void
    {
        [$user, $character] = $this->createPlayer([
            'avatar' => 'javascript:alert(1)',
        ]);

        $this->assertStringNotContainsString('javascript:', $character->avatar_url);
        $this->assertEquals(asset('assets/characters/avatar_male_1.jpg'), $character->avatar_url);

        $character->avatar = 'data:text/html,<script>alert(1)</script>';
        $this->assertStringNotContainsString('data:', $character->avatar_url);

        $character->avatar = 'vbscript:msgbox("test")';
        $this->assertStringNotContainsString('vbscript:', $character->avatar_url);

        // Safe relative path works
        $character->avatar = 'assets/characters/henkie.jpg';
        $this->assertEquals(asset('assets/characters/henkie.jpg'), $character->avatar_url);
    }

    public function test_community_claim_fails_validation_for_invalid_platform_json(): void
    {
        [$user, $character] = $this->createPlayer();

        $response = $this->actingAs($user)->postJson(route('community.claim', 'non_existent_platform'));

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Ongeldig platform geselecteerd.');
    }

    public function test_community_claim_blocked_when_player_is_comatose(): void
    {
        [$user, $character] = $this->createPlayer([
            'coma_until' => now()->addMinutes(30),
            'health' => 0,
        ]);

        $response = $this->actingAs($user)->postJson(route('community.claim', 'youtube'));

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een Forensisch Reanimatie-Serum (Epinefrine-Ampul) toe.');

        $this->assertFalse(SocialRewardClaim::where('user_id', $user->id)->where('platform', 'youtube')->exists());
    }

    public function test_community_claim_blocked_when_player_needs_revive_serum(): void
    {
        [$user, $character] = $this->createPlayer([
            'perks' => ['liquidation_streak_deaths' => 10],
        ]);

        $this->assertTrue($character->needsReviveSerum());

        $response = $this->actingAs($user)->postJson(route('community.claim', 'youtube'));

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een Forensisch Reanimatie-Serum (Epinefrine-Ampul) toe.');

        $this->assertFalse(SocialRewardClaim::where('user_id', $user->id)->where('platform', 'youtube')->exists());
    }

    public function test_community_claim_blocked_when_player_is_imprisoned(): void
    {
        [$user, $character] = $this->createPlayer([
            'prison_until' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($user)->postJson(route('community.claim', 'youtube'));

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Vanuit de isoleercel kun je geen community beloningen claimen!');

        $this->assertFalse(SocialRewardClaim::where('user_id', $user->id)->where('platform', 'youtube')->exists());
    }

    public function test_push_subscription_claim_grants_expected_rewards(): void
    {
        [$user, $character] = $this->createPlayer();
        $endpoint = 'https://push.example.test/audit';
        $user->pushSubscriptions()->create(['endpoint' => $endpoint, 'endpoint_hash' => hash('sha256', $endpoint)]);

        $initialCash = $character->cash;
        $initialEnergy = $character->energy;
        $initialRespect = $character->respect;

        $response = $this->actingAs($user)->postJson(route('community.claim', 'push'), [
            'is_standalone' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('platform', 'push');

        $character->refresh();
        $this->assertEquals($initialCash + 25000, $character->cash);
        $this->assertEquals($initialEnergy + 20, $character->energy);
        $this->assertEquals($initialRespect + 25, $character->respect);

        $this->assertTrue(SocialRewardClaim::where('user_id', $user->id)->where('platform', 'push')->exists());
    }

    public function test_profile_view_renders_dark_mode_contrast_and_escapes_guestbook(): void
    {
        [$user1, $character1] = $this->createPlayer();
        [$user2, $character2] = $this->createPlayer();

        // Create guestbook entry with potential HTML to check escaping
        GuestbookEntry::create([
            'profile_character_id' => $character1->id,
            'author_character_id' => $character2->id,
            'message' => '<b>Mooie cel broer!</b> <script>alert("xss")</script>',
        ]);

        $response = $this->actingAs($user1)->get(route('profile.show', $character1->name));

        $response->assertOk();
        // Check message is escaped properly
        $response->assertSee('&lt;b&gt;Mooie cel broer!&lt;/b&gt;', false);
        $response->assertDontSee('<script>alert("xss")</script>', false);
        // Check dark mode classes present
        $response->assertSee('dark:text-slate-300');
    }

    public function test_migration_down_methods_execute_safely(): void
    {
        $migration1 = require database_path('migrations/2026_11_03_091915_change_characters_vip_expires_at_to_datetime_and_grant_lifetime_vip_to_syze.php');
        $migration1->down();

        $migration2 = require database_path('migrations/2026_11_03_093624_change_expiration_timestamps_to_datetime.php');
        $migration2->down();

        $this->assertTrue(true);
    }
}

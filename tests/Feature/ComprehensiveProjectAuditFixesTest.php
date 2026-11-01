<?php

namespace Tests\Feature;

use App\Http\Controllers\ConnectFourController;
use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\CinemaRoom;
use App\Models\NewsArticle;
use App\Models\User;
use App\Support\ConnectFourEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ComprehensiveProjectAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private function createOnboardedCharacter(array $characterAttributes = [], array $userAttributes = []): Character
    {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'is_onboarded' => true,
        ], $userAttributes));

        $perks = $characterAttributes['perks'] ?? [];
        $perks['onboarding_completed'] = true;
        $characterAttributes['perks'] = $perks;

        return Character::factory()->create(array_merge([
            'user_id' => $user->id,
            'level' => 10,
            'cash' => 100000,
            'bank' => 100000,
            'health' => 100,
            'energy' => 100,
            'is_onboarded' => true,
        ], $characterAttributes));
    }

    /**
     * Verify that ConnectFour startMatch prevents resetting an active match after moves exist.
     */
    public function test_connect_four_start_match_blocks_reset_after_moves_made(): void
    {
        $character = $this->createOnboardedCharacter();
        $board = array_fill(0, 6, array_fill(0, 7, 0));
        $board[5][3] = ConnectFourEngine::PLAYER;

        Cache::put(ConnectFourController::matchCacheKey($character->id), [
            'board' => $board,
            'finished' => false,
            'started_at' => now()->timestamp,
        ], now()->addMinutes(30));

        $response = $this->actingAs($character->user)
            ->postJson(route('connect_four.match.start'));

        $response->assertOk()
            ->assertJsonPath('resumed', true);

        $cached = Cache::get(ConnectFourController::matchCacheKey($character->id));
        $this->assertSame(ConnectFourEngine::PLAYER, $cached['board'][5][3]);
    }

    /**
     * Verify that BankController setPin blocks overwriting an existing PIN when the ATM session is locked.
     */
    public function test_bank_set_pin_requires_unlocked_atm_or_current_pin_when_pin_already_set(): void
    {
        $character = $this->createOnboardedCharacter();
        $character->setBankPin('1234');

        $response = $this->actingAs($character->user)
            ->post(route('bank.pin.set'), [
                'pin' => '9999',
                'pin_confirmation' => '9999',
            ]);

        $response->assertSessionHas('error');
        $character->refresh();
        $this->assertTrue($character->verifyBankPin('1234'));
        $this->assertFalse($character->verifyBankPin('9999'));
    }

    /**
     * Verify that GangController create grants the welcome VIP bonus only once per character.
     */
    public function test_gang_creation_grants_welcome_vip_bonus_only_once(): void
    {
        $character = $this->createOnboardedCharacter([
            'cash' => 100000,
            'is_vip' => false,
            'vip_expires_at' => null,
        ]);

        $this->actingAs($character->user)->post(route('gangs.create'), [
            'name' => 'Alpha Ward',
            'tag' => 'ALPH',
            'description' => 'Eerste gang voor test.',
        ])->assertRedirect();

        $character->refresh();
        $this->assertTrue((bool) $character->is_vip);
        $this->assertTrue((bool) ($character->perks['gang_welcome_vip_claimed'] ?? false));
        $firstExpiry = $character->vip_expires_at;
        $this->assertNotNull($firstExpiry);

        $this->actingAs($character->user)->post(route('gangs.disband'))->assertRedirect();

        $character->update([
            'is_vip' => false,
            'vip_expires_at' => now()->subDay(),
        ]);

        $this->actingAs($character->user)->post(route('gangs.create'), [
            'name' => 'Beta Ward',
            'tag' => 'BETA',
            'description' => 'Tweede gang voor test.',
        ])->assertRedirect();

        $character->refresh();
        $this->assertFalse((bool) $character->is_vip);
    }

    /**
     * Verify that AdminController updatePlayer prevents moderators from escalating VIP status.
     */
    public function test_moderator_cannot_escalate_player_vip_status(): void
    {
        $moderator = $this->createOnboardedCharacter([], [
            'role' => 'moderator',
            'is_staff' => true,
        ]);

        $target = $this->createOnboardedCharacter([
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 0,
            'respect' => 50,
            'vip_points' => 0,
            'is_vip' => false,
            'vip_expires_at' => null,
        ]);

        $this->actingAs($moderator->user)
            ->post(route('admin.player.update', $target), [
                'cash' => 5000,
                'bank' => 10000,
                'stars' => 0,
                'respect' => 50,
                'vip_points' => 0,
                'health' => 100,
                'energy' => 100,
                'is_vip' => 1,
                'vip_tier' => 4,
                'vip_action' => 'no_change',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $target->refresh();
        $this->assertFalse((bool) $target->is_vip);
        $this->assertNull($target->vip_expires_at);
    }

    /**
     * Verify that BuyMeACoffeeWebhookController does not revoke VIP for an unlinked player mentioned in support_message.
     */
    public function test_bmc_cancellation_webhook_does_not_revoke_arbitrary_character_from_support_message(): void
    {
        config(['services.buymeacoffee.webhook_secret' => 'test_bmc_secret']);

        $victim = $this->createOnboardedCharacter([
            'name' => 'TargetPatient',
            'is_vip' => true,
            'vip_expires_at' => now()->addDays(20),
            'perks' => [
                'onboarding_completed' => true,
                'supporter_tier' => 2,
            ],
        ]);

        $attackerCharacter = $this->createOnboardedCharacter([
            'name' => 'AttackerPatient',
            'is_vip' => true,
            'vip_expires_at' => now()->addDays(10),
        ]);

        BuyMeACoffeeOrder::create([
            'external_id' => 'sub_999111',
            'event_type' => 'membership.started',
            'supporter_name' => 'Attacker',
            'supporter_email' => 'attacker@example.com',
            'character_id' => $attackerCharacter->id,
            'character_name_input' => 'AttackerPatient',
            'package_key' => 'vip_1',
            'package_title' => '1 Maand VIP',
            'amount_eur' => 5.00,
            'currency' => 'EUR',
            'status' => 'rewarded',
            'rewarded_at' => now()->subDay(),
        ]);

        $payload = json_encode([
            'type' => 'membership.cancelled',
            'data' => [
                'id' => 'sub_999111',
                'supporter_name' => 'Attacker',
                'supporter_email' => 'attacker@example.com',
                'support_note' => 'TargetPatient',
            ],
        ]);

        $signature = hash_hmac('sha256', $payload, 'test_bmc_secret');

        $this->call(
            'POST',
            route('webhooks.buymeacoffee'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_SIGNATURE_SHA256' => $signature,
            ],
            $payload
        )->assertOk();

        $victim->refresh();
        $this->assertTrue((bool) $victim->is_vip);
        $this->assertTrue($victim->vip_expires_at->isFuture());
    }

    /**
     * Verify that CinemaController rejects external GIF URLs not in GIF_GALLERY.
     */
    public function test_cinema_chat_rejects_external_gif_url(): void
    {
        $character = $this->createOnboardedCharacter();
        $room = CinemaRoom::create([
            'title' => 'Kliniek Bioscoop',
            'slug' => 'kliniek-bioscoop',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'youtube_title' => 'Test Video',
            'status' => 'active',
        ]);

        $response = $this->actingAs($character->user)
            ->postJson(route('cinema.send-message', $room), [
                'gif_url' => 'https://evil.example.com/tracker.gif',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Verify that ChatController rejects arbitrary non-gang channel names.
     */
    public function test_chat_controller_rejects_invalid_channel_names(): void
    {
        $character = $this->createOnboardedCharacter();

        $this->actingAs($character->user)
            ->getJson(route('chat.public.messages', ['channel' => 'secret_admin_room']))
            ->assertStatus(422);

        $this->actingAs($character->user)
            ->postJson(route('chat.public.send'), [
                'channel' => 'secret_admin_room',
                'message' => 'Test bericht',
            ])
            ->assertStatus(422);
    }

    /**
     * Verify that OnboardingController complete rejects arbitrary avatar paths.
     */
    public function test_onboarding_complete_rejects_invalid_avatar_path(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_onboarded' => false,
        ]);
        Character::factory()->create([
            'user_id' => $user->id,
            'is_onboarded' => false,
            'perks' => ['onboarding_completed' => false],
        ]);

        $response = $this->actingAs($user)
            ->post(route('onboarding.complete'), [
                'username' => 'nieuwe_patient',
                'display_name' => 'Nieuwe Patient',
                'birthdate' => '1995-05-15',
                'avatar' => 'https://evil.example.com/payload.svg',
                'profile_color' => 'purple',
                'specialty' => 'hossel',
            ]);

        $response->assertSessionHasErrors('avatar');
    }

    /**
     * Verify that NewsArticle sanitizeHtml strips script tag contents and protocol-relative hrefs.
     */
    public function test_news_article_sanitize_html_removes_script_body_and_protocol_relative_urls(): void
    {
        $dirtyHtml = '<p>Welkom</p><script>alert("xss")</script><a href="//evil.example.com/phish" onclick="evil()">Klik</a>';
        $sanitized = NewsArticle::sanitizeHtml($dirtyHtml);

        $this->assertStringNotContainsString('alert("xss")', $sanitized);
        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('//evil.example.com', $sanitized);
        $this->assertStringNotContainsString('onclick', $sanitized);
        $this->assertStringContainsString('<p>Welkom</p>', $sanitized);
    }

    /**
     * Verify that Character hides sensitive attributes when serialized to JSON/array.
     */
    public function test_character_serialization_hides_sensitive_columns(): void
    {
        $character = $this->createOnboardedCharacter([
            'weather_lat' => 52.3676,
            'weather_lon' => 4.9041,
            'perks' => [
                'onboarding_completed' => true,
                'custom_name_color' => 'red; background:url(javascript:alert(1))',
            ],
        ]);

        $serialized = $character->toArray();

        $this->assertArrayNotHasKey('weather_lat', $serialized);
        $this->assertArrayNotHasKey('weather_lon', $serialized);
        $this->assertArrayNotHasKey('perks', $serialized);
        $this->assertNull($character->getCustomNameColor());
    }
}

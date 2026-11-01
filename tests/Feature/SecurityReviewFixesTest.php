<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityReviewFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacterFor(User $user, array $attributes = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Rozemarijn Bakker',
            'cash' => 1000,
            'bank' => 500,
            'respect' => 100,
            'energy' => 40,
            'max_energy' => 100,
            'health' => 60,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
            'last_energy_refresh' => now(),
        ], $attributes));
    }

    protected function configureDiscordOauth(): void
    {
        config([
            'services.discord.client_id' => 'discord-test-client-id',
            'services.discord.client_secret' => 'discord-test-client-secret',
        ]);
    }

    /**
     * Fake the Discord token exchange and the Discord profile response for one login.
     */
    protected function fakeDiscordProfile(array $profile): void
    {
        Http::fake([
            'discord.com/api/oauth2/token' => Http::response(['access_token' => 'discord-test-access-token']),
            'discord.com/api/users/@me' => Http::response($profile),
        ]);
    }

    public function test_news_sanitizer_removes_event_handlers_and_encoded_javascript_urls(): void
    {
        $sanitized = NewsArticle::sanitizeHtml(
            '<p><img src=x //onerror=alert(document.cookie)></p>'
            .'<p><a href="&#106;avascript:alert(1)">Lees meer</a></p>'
            .'<p><a href="javascript&colon;alert(2)">Verder</a></p>'
        );

        $this->assertStringNotContainsString('onerror', $sanitized);
        $this->assertStringNotContainsString('javascript', strtolower($sanitized));
        $this->assertStringContainsString('href="#"', $sanitized);
    }

    public function test_news_sanitizer_keeps_safe_links_and_formatting(): void
    {
        $sanitized = NewsArticle::sanitizeHtml(
            '<p><strong>Nieuw</strong> bij de <a href="https://tbs-kliniek.nl/faq" onclick="steal()">FAQ</a></p>'
        );

        $this->assertStringContainsString('<strong>Nieuw</strong>', $sanitized);
        $this->assertStringContainsString('href="https://tbs-kliniek.nl/faq"', $sanitized);
        $this->assertStringNotContainsString('onclick', $sanitized);
    }

    public function test_discord_login_rejects_state_that_does_not_match_the_session(): void
    {
        $this->configureDiscordOauth();
        Http::fake();

        $response = $this->withSession(['oauth2state' => 'state-started-by-this-browser'])
            ->get('/discord/callback?code=attacker-code&state=state-from-attacker');

        $response->assertRedirect(route('login'));
        Http::assertNothingSent();
        $this->assertGuest();
    }

    public function test_discord_login_does_not_match_account_by_unverified_email(): void
    {
        $this->configureDiscordOauth();
        $victim = User::factory()->create(['email' => 'marloes.jansen@example.nl']);
        $this->fakeDiscordProfile([
            'id' => '818273645190182912',
            'username' => 'mjansen_gaming',
            'global_name' => 'Marloes',
            'email' => 'marloes.jansen@example.nl',
            'verified' => false,
        ]);

        $this->withSession(['oauth2state' => 'state-started-by-this-browser'])
            ->get('/discord/callback?code=attacker-code&state=state-started-by-this-browser');

        $this->assertNull($victim->fresh()->discord_id);
        $this->assertNotSame($victim->id, Auth::id());
    }

    public function test_discord_login_matches_account_by_verified_email(): void
    {
        $this->configureDiscordOauth();
        $player = User::factory()->create(['email' => 'stefan.visser@example.nl']);
        $this->fakeDiscordProfile([
            'id' => '718273645190182911',
            'username' => 'svisser',
            'global_name' => 'Stefan',
            'email' => 'stefan.visser@example.nl',
            'verified' => true,
        ]);

        $this->withSession(['oauth2state' => 'state-started-by-this-browser'])
            ->get('/discord/callback?code=player-code&state=state-started-by-this-browser');

        $this->assertSame($player->id, Auth::id());
    }

    public function test_shop_fill_max_cannot_bypass_the_cooldown_for_shake_items(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacterFor($user, [
            'perks' => ['last_used_eiwitshake_bodybuilder' => now()->toIso8601String()],
        ]);
        $item = Item::where('slug', 'eiwitshake-bodybuilder')->firstOrFail();
        $charItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $item->id,
            'quantity' => 5,
        ]);

        $this->actingAs($user)->post(route('shop.consume', $charItem->id), ['fill_max' => 1]);

        $this->assertEquals(5, $charItem->fresh()->quantity);
    }

    public function test_sponsor_claim_rejects_a_client_token_that_the_server_did_not_verify(): void
    {
        config(['services.robinads.enabled' => true]);
        Http::fake(['robinsads.com/*' => Http::response(['status' => 'pending'])]);
        $user = User::factory()->create();
        $character = $this->createCharacterFor($user, ['cash' => 1000]);

        $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'daily_cash']);
        $response = $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'reward_type' => 'daily_cash',
            'robin_token' => 'client-generated-token',
        ]);

        $response->assertJsonPath('success', false);
        $this->assertEquals(1000, $character->fresh()->cash);
    }

    public function test_avatar_upload_saves_the_extension_of_the_detected_content(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacterFor($user);

        // A valid 1x1 GIF sent with an HTML file name and an image/gif MIME type. The stored file must keep the image extension.
        $gifPath = tempnam(sys_get_temp_dir(), 'avatar');
        file_put_contents($gifPath, base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));
        $disguisedGif = new UploadedFile($gifPath, 'profiel.html', 'image/gif', null, true);

        $this->actingAs($user)->post(route('profile.avatar'), ['custom_avatar' => $disguisedGif]);

        $avatarPath = $character->fresh()->avatar;
        try {
            $this->assertStringStartsWith('uploads/avatars/', (string) $avatarPath);
            $this->assertStringEndsWith('.gif', (string) $avatarPath);
        } finally {
            if ($avatarPath) {
                @unlink(public_path($avatarPath));
            }
        }
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\TurnstileService;
use App\Services\VpnDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class AccessControlRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_a_reserved_staff_email_does_not_grant_privileges(): void
    {
        $vpn = $this->createStub(VpnDetectionService::class);
        $vpn->method('check')->willReturn(['is_vpn' => false, 'provider' => null, 'country' => 'NL']);
        $this->app->instance(VpnDetectionService::class, $vpn);

        $this->post(route('register.post'), [
            'username' => 'NieuwePatient',
            'email' => 'demo@tbskliniek.nl',
            'password' => 'patient-password',
            'password_confirmation' => 'patient-password',
            'terms' => '1',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'demo@tbskliniek.nl')->firstOrFail();
        $this->assertFalse($user->isStaff());
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_a_reserved_owner_address_without_an_owner_role_cannot_access_owner_routes(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'admin@tbskliniek.nl',
            'role' => 'user',
            'is_staff' => false,
        ]);

        $this->assertFalse($user->isOwner());
        $this->assertFalse($user->isStaff());
        $this->actingAs($user)->get(route('admin.mail.index'))->assertForbidden();
    }

    public function test_announcement_command_does_not_promote_an_existing_regular_account(): void
    {
        $user = User::factory()->create([
            'email' => 'directie@tbs-kliniek.nl',
            'role' => 'user',
            'is_staff' => false,
        ]);

        $this->artisan('chat:directie-tiktok-announcement')->assertFailed();

        $this->assertFalse($user->fresh()->isStaff());
        $this->assertNull($user->fresh()->character);
    }

    public function test_announcement_command_creates_its_own_admin_account_when_missing(): void
    {
        $this->artisan('chat:directie-tiktok-announcement')->assertSuccessful();

        $user = User::where('email', 'directie@tbs-kliniek.nl')->firstOrFail();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(ChatMessage::where('character_id', $user->character->id)->exists());
    }

    public function test_discord_unverified_email_cannot_match_a_synthetic_email_of_an_existing_account(): void
    {
        config([
            'services.discord.client_id' => 'test-client',
            'services.discord.client_secret' => 'test-secret',
        ]);
        $victim = User::factory()->create(['email' => 'kliniekpatient@discord.tbskliniek.nl']);
        Http::fake([
            'discord.com/api/oauth2/token' => Http::response(['access_token' => 'test-token']),
            'discord.com/api/users/@me' => Http::response([
                'id' => '718273645190182911',
                'username' => 'kliniekpatient',
                'email' => null,
                'verified' => false,
            ]),
        ]);

        $this->withSession(['oauth2state' => 'browser-state'])
            ->get('/discord/callback?code=discord-code&state=browser-state');

        $this->assertNotSame($victim->id, auth()->id());
    }

    public function test_discord_login_accepts_an_already_linked_account_without_a_verified_email(): void
    {
        config([
            'services.discord.client_id' => 'test-client',
            'services.discord.client_secret' => 'test-secret',
        ]);
        $user = User::factory()->create(['discord_id' => '718273645190182911']);
        Character::factory()->create(['user_id' => $user->id, 'is_onboarded' => true]);
        Http::fake([
            'discord.com/api/oauth2/token' => Http::response(['access_token' => 'test-token']),
            'discord.com/api/users/@me' => Http::response([
                'id' => '718273645190182911',
                'username' => 'kliniekpatient',
                'verified' => false,
            ]),
        ]);

        $this->withSession(['oauth2state' => 'browser-state'])
            ->get('/discord/callback?code=discord-code&state=browser-state');

        $this->assertAuthenticatedAs($user);
    }

    public function test_discord_login_rejects_an_email_already_linked_to_another_discord_account(): void
    {
        config([
            'services.discord.client_id' => 'test-client',
            'services.discord.client_secret' => 'test-secret',
        ]);
        $user = User::factory()->create([
            'email' => 'patient@example.invalid',
            'discord_id' => '718273645190182911',
        ]);
        Http::fake([
            'discord.com/api/oauth2/token' => Http::response(['access_token' => 'test-token']),
            'discord.com/api/users/@me' => Http::response([
                'id' => '718273645190182912',
                'username' => 'anderepatient',
                'email' => 'patient@example.invalid',
                'verified' => true,
            ]),
        ]);

        $this->withSession(['oauth2state' => 'browser-state'])
            ->get('/discord/callback?code=discord-code&state=browser-state');

        $this->assertGuest();
        $this->assertSame('718273645190182911', $user->fresh()->discord_id);
    }

    public function test_database_installer_does_not_accept_secrets_in_query_strings(): void
    {
        $process = new Process([
            PHP_BINARY,
            '-r',
            '$_SERVER["REQUEST_METHOD"] = "GET"; $_GET["key"] = "installer-test-key"; require $argv[1];',
            public_path('migrate-db.php'),
        ], base_path(), [
            'APP_ENV' => 'testing',
            'DB_SETUP_KEY' => 'installer-test-key',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
        ]);
        $process->mustRun();

        $this->assertStringContainsString('Toegang Geweigerd', $process->getOutput());
        $this->assertStringNotContainsString('installer-test-key', $process->getOutput());
    }

    public function test_database_installer_accepts_posted_key_without_echoing_it(): void
    {
        $process = new Process([
            PHP_BINARY,
            '-r',
            '$_SERVER["REQUEST_METHOD"] = "POST"; $_POST["key"] = "installer-test-key"; require $argv[1];',
            public_path('migrate-db.php'),
        ], base_path(), [
            'APP_ENV' => 'testing',
            'DB_SETUP_KEY' => 'installer-test-key',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
        ]);
        $process->mustRun();

        $this->assertStringContainsString('Database Verbindingsstatus', $process->getOutput());
        $this->assertStringNotContainsString('installer-test-key', $process->getOutput());
    }

    public function test_missing_turnstile_secret_does_not_bypass_verification(): void
    {
        config(['services.turnstile.secret_key' => '']);
        Http::fake();

        $this->assertFalse(app(TurnstileService::class)->verify('unverified-response'));
        Http::assertNothingSent();
    }

    public function test_turnstile_requires_a_boolean_success_response(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => 'false'])]);

        $this->assertFalse(app(TurnstileService::class)->verify('unverified-response'));
    }

    public function test_turnstile_hard_mode_rejects_replay_malformed_and_mismatched_context(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret']);
        $service = app(TurnstileService::class);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'action' => 'luchtplaats_attack',
                'cdata' => TurnstileService::generateCdata('luchtplaats', 42),
                'challenge_ts' => now()->toIso8601String(),
            ], 200),
        ]);

        // 1. Rejects oversized (>2048 chars) or whitespace-injected tokens without network call
        $this->assertFalse($service->verify(str_repeat('a', 2049)));
        $this->assertFalse($service->verify("valid_token\ninjected"));
        Http::assertNothingSent();

        // 2. Single-use token replay protection blocks reuse of an already verified token
        request()->attributes->set('turnstile_context', [
            'action' => 'luchtplaats_attack',
            'cdata' => TurnstileService::generateCdata('luchtplaats', 42),
        ]);

        $this->assertTrue($service->verify('one-time-token-123'));
        $this->assertFalse($service->verify('one-time-token-123'));

        // 3. Action mismatch fails closed
        request()->attributes->set('turnstile_context', [
            'action' => 'discord_shield_verify',
            'cdata' => TurnstileService::generateCdata('luchtplaats', 42),
        ]);
        $this->assertFalse($service->verify('action-mismatch-token'));

        // 4. cData mismatch (cross-account token harvesting) fails closed
        request()->attributes->set('turnstile_context', [
            'action' => 'luchtplaats_attack',
            'cdata' => TurnstileService::generateCdata('luchtplaats', 999),
        ]);
        $this->assertFalse($service->verify('cdata-mismatch-token'));

        request()->attributes->remove('turnstile_context');
    }

    public function test_unimplemented_two_factor_authentication_cannot_be_enabled(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        Character::factory()->create(['user_id' => $user->id, 'is_onboarded' => true]);

        $this->actingAs($user)->post(route('settings.2fa.toggle'))
            ->assertSessionHas('error');

        $this->assertFalse($user->fresh()->two_factor_enabled);
        $this->actingAs($user)->get(route('settings.account'))
            ->assertOk()
            ->assertSee('Twee-factor-authenticatie is momenteel niet beschikbaar')
            ->assertDontSee('Jouw account is extra beveiligd.');
    }

    public function test_previously_enabled_two_factor_flag_is_not_presented_as_protection(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => true]);
        Character::factory()->create(['user_id' => $user->id, 'is_onboarded' => true]);

        $this->actingAs($user)->get(route('settings.account'))
            ->assertOk()
            ->assertSee('Twee-factor-authenticatie is momenteel niet beschikbaar')
            ->assertDontSee('Jouw account is extra beveiligd.');
    }

    public function test_public_maintenance_bypass_cannot_access_the_site(): void
    {
        $user = User::factory()->create();
        $lock = CheckMaintenanceMode::lockFilePath();
        $this->assertFileDoesNotExist($lock, 'An existing maintenance lock must not be overwritten by this test.');
        file_put_contents($lock, '{}');

        try {
            $this->actingAs($user)->get('/dashboard?bypass=daniel')->assertStatus(503);
            $this->withCookie('tbs_maintenance_bypass', 'daniel-tbs-secret-access')
                ->get('/dashboard')
                ->assertStatus(503);
        } finally {
            unlink($lock);
        }
    }
}

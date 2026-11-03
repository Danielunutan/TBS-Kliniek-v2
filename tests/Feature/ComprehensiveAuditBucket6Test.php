<?php

namespace Tests\Feature;

use App\Http\Controllers\DiscordShieldController;
use App\Models\AuditLog;
use App\Models\Character;
use App\Models\DiscordShieldLog;
use App\Models\DiscordShieldSetting;
use App\Models\DiscordVerification;
use App\Models\Report;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\DiscordShieldService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ComprehensiveAuditBucket6Test extends TestCase
{
    use RefreshDatabase;

    private function createPlayer(array $userAttrs = [], array $charAttrs = []): array
    {
        $user = User::factory()->create(array_merge([
            'role' => 'user',
            'is_onboarded' => true,
        ], $userAttrs));

        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler'.rand(1000, 9999),
            'cash' => 5000,
            'bank' => 25000,
            'level' => 5,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'stars' => 10,
        ], $charAttrs));

        return [$user, $character];
    }

    public function test_non_staff_cannot_access_audit_log_index_export_or_sync(): void
    {
        [$regularUser] = $this->createPlayer(['role' => 'user', 'is_staff' => false]);

        $this->actingAs($regularUser)
            ->get(route('admin.audit_log.index'))
            ->assertStatus(403);

        $this->actingAs($regularUser)
            ->get(route('admin.audit_log.export'))
            ->assertStatus(403);

        $this->actingAs($regularUser)
            ->post(route('admin.audit_log.sync'))
            ->assertStatus(403);
    }

    public function test_staff_can_view_audit_log_dashboard_and_clusters(): void
    {
        [$adminUser, $adminChar] = $this->createPlayer(['role' => 'admin', 'is_staff' => true]);

        AuditLogService::log(
            action: 'bank_transfer',
            category: 'economy',
            description: 'Test verdachte geldtransactie',
            character: $adminChar,
            user: $adminUser,
            amount: 5000000,
            details: ['test_signal' => true]
        );

        $response = $this->actingAs($adminUser)
            ->get(route('admin.audit_log.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Log');
        $response->assertSee('Gedeelde IP &amp; Multi-Account Detectie', false);
    }

    public function test_csv_export_escapes_formula_injection_with_leading_whitespace(): void
    {
        [$adminUser, $adminChar] = $this->createPlayer(['role' => 'admin', 'is_staff' => true]);

        // Inject malicious formula descriptions and details
        AuditLog::create([
            'user_id' => $adminUser->id,
            'character_id' => $adminChar->id,
            'action' => 'exploit_attempt',
            'category' => 'security',
            'description' => ' =cmd|/C calc.exe!A0',
            'amount' => 100,
            'risk_level' => 'hoog',
            'risk_score' => 99,
            'risk_reason' => '+2+5+cmd|',
            'risk_flags' => ['-DDE("calc")'],
            'ip_address' => '1.2.3.4',
            'details' => ['payload' => '@SUM(1+1)'],
        ]);

        $response = $this->actingAs($adminUser)
            ->get(route('admin.audit_log.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Capture streamed response content
        ob_start();
        $response->sendContent();
        $csvContent = (string) ob_get_clean();

        // Must prepend single quote to neutralize formula injection
        $this->assertStringContainsString("' =cmd|/C calc.exe!A0", $csvContent);
        $this->assertStringContainsString("'+2+5+cmd|", $csvContent);
        $this->assertStringContainsString("'-DDE(\"\"calc\"\")", $csvContent);
    }

    public function test_audit_log_target_actions_do_not_contaminate_target_ip(): void
    {
        [$adminUser] = $this->createPlayer(['role' => 'admin', 'is_staff' => true]);
        [$targetUser, $targetChar] = $this->createPlayer(['role' => 'user']);

        // Acting as admin with admin IP
        $this->actingAs($adminUser);

        $log = AuditLogService::log(
            action: 'admin_update_player',
            category: 'admin',
            description: "Spelersprofiel {$targetChar->name} gewijzigd door beheerder",
            character: $targetChar,
            user: $targetUser,
            details: ['cash' => 9999]
        );

        // Target IP must be null, not the admin's IP
        $this->assertNull($log->ip_address);

        $banLog = AuditLogService::log(
            action: 'user_ban',
            category: 'moderation',
            description: "Account {$targetUser->name} verbannen",
            character: $targetChar,
            user: $targetUser,
            details: ['reason' => 'Cheating']
        );

        $this->assertNull($banLog->ip_address);
    }

    public function test_multi_account_confidence_calculation_between_users(): void
    {
        $createdAt = Carbon::now()->subHours(2);

        [$userA, $charA] = $this->createPlayer([
            'registration_ip' => '192.168.1.50',
            'last_login_ip' => '192.168.1.50',
            'created_at' => $createdAt,
        ]);

        [$userB, $charB] = $this->createPlayer([
            'registration_ip' => '192.168.1.50',
            'last_login_ip' => '192.168.1.50',
            'created_at' => $createdAt->copy()->addMinutes(15),
        ]);

        // Add shared discord verification fingerprint
        DiscordVerification::create([
            'discord_id' => '123456789012345678',
            'username' => 'UserA_Discord',
            'user_id' => $userA->id,
            'character_id' => $charA->id,
            'ip_address' => '192.168.1.50',
            'fingerprint_hash' => 'shared_device_fingerprint_hash_abc123',
            'status' => 'verified',
        ]);

        DiscordVerification::create([
            'discord_id' => '987654321098765432',
            'username' => 'UserB_Discord',
            'user_id' => $userB->id,
            'character_id' => $charB->id,
            'ip_address' => '192.168.1.50',
            'fingerprint_hash' => 'shared_device_fingerprint_hash_abc123',
            'status' => 'verified',
        ]);

        $confidence = AuditLogService::calculateMultiAccountConfidence($userA, $userB);

        $this->assertGreaterThanOrEqual(70, $confidence['score']);
        $this->assertEquals('hoog', $confidence['level']);
        $this->assertNotEmpty($confidence['matched_signals']);
    }

    public function test_ip_multi_account_cluster_confidence_calculation(): void
    {
        $testIp = '203.0.113.88';

        [$userA] = $this->createPlayer(['registration_ip' => $testIp, 'last_login_ip' => $testIp]);
        [$userB] = $this->createPlayer(['registration_ip' => $testIp, 'last_login_ip' => $testIp]);

        $clusterConfidence = AuditLogService::calculateIpMultiAccountConfidence($testIp);

        $this->assertGreaterThanOrEqual(40, $clusterConfidence['score']);
        $this->assertContains($clusterConfidence['level'], ['verdacht', 'hoog']);
        $this->assertEquals(2, $clusterConfidence['user_count']);

        // A single user IP returns score 0 and level laag
        $singleConfidence = AuditLogService::calculateIpMultiAccountConfidence('198.51.100.99');
        $this->assertEquals(0, $singleConfidence['score']);
        $this->assertEquals('laag', $singleConfidence['level']);
    }

    public function test_non_admin_cannot_bypass_admin_settings_or_orders(): void
    {
        [$regularUser] = $this->createPlayer(['role' => 'user']);
        [$moderatorUser] = $this->createPlayer(['role' => 'moderator', 'is_staff' => true]);

        // Regular user blocked with 403
        $this->actingAs($regularUser)
            ->post(route('admin.gameplay.update'), ['hospital_multiplier' => 2.0])
            ->assertStatus(403);

        // Moderator blocked from admin gameplay settings with error
        $this->actingAs($moderatorUser)
            ->post(route('admin.gameplay.update'), ['hospital_multiplier' => 2.0])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Moderator blocked from BuyMeACoffee manual order creation
        $this->actingAs($moderatorUser)
            ->post(route('admin.buymeacoffee.manual'), [
                'supporter_name' => 'Tester',
                'character_id' => $regularUser->character?->id,
                'amount' => 10,
                'membership_tier' => 'vip_1_month',
                'is_membership' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_admin_can_update_player_with_transaction_and_audit_logging(): void
    {
        [$adminUser] = $this->createPlayer(['role' => 'admin', 'is_staff' => true]);
        [$targetUser, $targetChar] = $this->createPlayer(['role' => 'user'], ['cash' => 100, 'bank' => 200]);

        $response = $this->actingAs($adminUser)
            ->post(route('admin.player.update', $targetChar), [
                'cash' => 50000,
                'bank' => 100000,
                'stars' => 10,
                'respect' => 75,
                'vip_points' => 0,
                'health' => 100,
                'energy' => 100,
                'role' => 'user',
            ]);

        $response->assertRedirect();

        $targetChar->refresh();
        $this->assertEquals(50000, $targetChar->cash);
        $this->assertEquals(100000, $targetChar->bank);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_update_player',
            'character_id' => $targetChar->id,
            'category' => 'admin',
        ]);
    }

    public function test_moderation_report_deletion_is_restricted_to_admins_and_audited(): void
    {
        [$adminUser] = $this->createPlayer(['role' => 'admin', 'is_staff' => true]);
        [$modUser] = $this->createPlayer(['role' => 'moderator', 'is_staff' => true]);
        [$reporterUser, $reporterChar] = $this->createPlayer();
        [$reportedUser, $reportedChar] = $this->createPlayer();

        $report = Report::create([
            'reporter_id' => $reporterChar->id,
            'reported_type' => 'character',
            'reported_id' => $reportedChar->id,
            'reason' => 'Spam in algemene chat',
            'status' => 'pending',
        ]);

        // Moderator can resolve or dismiss, but CANNOT permanently delete
        $this->actingAs($modUser)
            ->delete(route('admin.moderation.report.destroy', $report))
            ->assertStatus(403);

        // Admin CAN delete
        $this->actingAs($adminUser)
            ->delete(route('admin.moderation.report.destroy', $report))
            ->assertRedirect();

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report_deleted',
            'category' => 'moderation',
        ]);
    }

    public function test_discord_shield_client_ip_resolution_safeguards(): void
    {
        $controller = new DiscordShieldController(app(DiscordShieldService::class));

        // Valid Cloudflare IP
        $request1 = Request::create('/discord/verifieer/submit', 'POST', [], [], [], [
            'HTTP_CF_CONNECTING_IP' => '198.51.100.12',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $this->assertEquals('198.51.100.12', $controller->resolveClientIp($request1));

        // Malicious spoofed string in CF header falls back to X-Forwarded-For or remote IP
        $request2 = Request::create('/discord/verifieer/submit', 'POST', [], [], [], [
            'HTTP_CF_CONNECTING_IP' => '<script>alert(1)</script>',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.15, 10.0.0.1',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $this->assertEquals('203.0.113.15', $controller->resolveClientIp($request2));

        // Complete spoofing falls back to valid IP
        $request3 = Request::create('/discord/verifieer/submit', 'POST', [], [], [], [
            'HTTP_CF_CONNECTING_IP' => 'evil-payload',
            'HTTP_X_FORWARDED_FOR' => 'another-evil-payload',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $this->assertEquals('127.0.0.1', $controller->resolveClientIp($request3));
    }

    public function test_discord_shield_rate_limiter_blocks_spam(): void
    {
        $discordId = '112233445566778899';
        $ip = '198.51.100.77';

        session(['discord_shield_user' => [
            'id' => $discordId,
            'username' => 'TestBotSpammer',
            'global_name' => 'Test Bot',
        ]]);

        DiscordShieldSetting::set('turnstile_enabled', '0');
        DiscordShieldSetting::set('block_vpns', '0');
        DiscordShieldSetting::set('block_alts', '0');

        $throttleKey = 'discord_shield_process:'.$ip.':'.$discordId;
        RateLimiter::clear($throttleKey);

        // Perform 5 verification attempts
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey, 60);
        }

        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson(route('discord.process'), [
                'fingerprint_hash' => 'hash_test_1234567890abcdef',
            ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('Te veel verificatiepogingen', $response->json('message'));
    }

    public function test_discord_shield_anti_alt_blocking_and_permanent_cookie(): void
    {
        $cookieUid = 'perm-cookie-uuid-test-999';
        $altFingerprint = 'hardware_fp_alt_test_1234567890';

        // Existing verified user
        DiscordVerification::create([
            'discord_id' => '111111111111111111',
            'username' => 'OriginalPlayer',
            'ip_address' => '198.51.100.5',
            'fingerprint_hash' => $altFingerprint,
            'fingerprint_raw' => ['cookie_uid' => $cookieUid],
            'status' => 'verified',
        ]);

        // Alt user attempting verification
        session(['discord_shield_user' => [
            'id' => '222222222222222222',
            'username' => 'AltPlayer',
            'global_name' => 'Alt Player',
        ]]);

        DiscordShieldSetting::set('turnstile_enabled', '0');
        DiscordShieldSetting::set('block_vpns', '0');
        DiscordShieldSetting::set('block_alts', '1');

        $response = $this->withCookie('tbs_perm_uid', $cookieUid)
            ->postJson(route('discord.process'), [
                'fingerprint_hash' => $altFingerprint,
                'fingerprint_raw' => ['cookie_uid' => $cookieUid],
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'alt_detected' => true,
        ]);

        $this->assertDatabaseHas('discord_shield_logs', [
            'event' => 'alt_detected',
            'discord_id' => '222222222222222222',
        ]);
    }

    public function test_discord_shield_admin_webhook_ssrf_prevention(): void
    {
        [$adminUser] = $this->createPlayer(['role' => 'admin', 'is_staff' => true]);

        // Attempting to inject SSRF URL to internal AWS metadata or internal services
        $response1 = $this->actingAs($adminUser)
            ->post(route('admin.discord_shield.settings'), [
                'webhook_url' => 'http://169.254.169.254/latest/meta-data/',
            ]);
        $response1->assertSessionHas('error');

        $response2 = $this->actingAs($adminUser)
            ->post(route('admin.discord_shield.settings'), [
                'webhook_url' => 'https://malicious-site.com/steal-data',
            ]);
        $response2->assertSessionHas('error');

        // Valid Discord webhook passes
        $response3 = $this->actingAs($adminUser)
            ->post(route('admin.discord_shield.settings'), [
                'webhook_url' => 'https://discord.com/api/webhooks/123456789/abcdefghijklmnopqrstuvwxyz',
            ]);
        $response3->assertSessionHasNoErrors();
    }

    public function test_discord_shield_blacklist_validation_enforces_clean_types(): void
    {
        [$adminUser] = $this->createPlayer(['role' => 'admin', 'is_staff' => true]);

        // Invalid IP address in blacklist
        $response1 = $this->actingAs($adminUser)
            ->post(route('admin.discord_shield.ban'), [
                'type' => 'ip_address',
                'value' => 'invalid-ip-string',
                'reason' => 'Testing validation',
            ]);
        $response1->assertSessionHas('error');

        // Non-snowflake discord ID
        $response2 = $this->actingAs($adminUser)
            ->post(route('admin.discord_shield.ban'), [
                'type' => 'discord_id',
                'value' => 'not-a-number',
                'reason' => 'Testing validation',
            ]);
        $response2->assertSessionHas('error');

        // Short fingerprint
        $response3 = $this->actingAs($adminUser)
            ->post(route('admin.discord_shield.ban'), [
                'type' => 'fingerprint',
                'value' => 'short',
                'reason' => 'Testing validation',
            ]);
        $response3->assertSessionHas('error');

        // Valid IP blacklist
        $response4 = $this->actingAs($adminUser)
            ->post(route('admin.discord_shield.ban'), [
                'type' => 'ip_address',
                'value' => '198.51.100.99',
                'reason' => 'Legitimate ban reason',
            ]);
        $response4->assertSessionHasNoErrors();
        $this->assertDatabaseHas('discord_shield_bans', [
            'type' => 'ip_address',
            'value' => '198.51.100.99',
        ]);
    }

    public function test_discord_shield_log_rejects_invalid_ip_injection(): void
    {
        $log = DiscordShieldLog::log('test_event', 'Log description', [
            'discord_id' => '123456789',
            'username' => 'TestUser',
            'ip_address' => 'malicious_non_ip_string',
        ]);

        $this->assertNull($log->ip_address);

        $validLog = DiscordShieldLog::log('test_event', 'Valid log', [
            'discord_id' => '123456789',
            'username' => 'TestUser',
            'ip_address' => '203.0.113.195',
        ]);

        $this->assertEquals('203.0.113.195', $validLog->ip_address);
    }
}

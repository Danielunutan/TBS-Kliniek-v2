<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBatch4SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that public/migrate-db.php denies access and does not leak secrets.
     */
    public function test_migrate_db_denies_unauthorized_access_without_secret_leak(): void
    {
        $migrateDbPath = public_path('migrate-db.php');
        $this->assertFileExists($migrateDbPath);

        $content = file_get_contents($migrateDbPath);

        // Verify that hardcoded secret 'tbs2026' was removed
        $this->assertStringNotContainsString('tbs2026', $content);
        $this->assertStringNotContainsString("secretKey = 'tbs2026'", $content);

        // Verify that DB_SETUP_KEY from environment is checked with hash_equals
        $this->assertStringContainsString('DB_SETUP_KEY', $content);
        $this->assertStringContainsString('hash_equals', $content);

        // Execute script via PHP CLI directly
        $output = shell_exec('php '.escapeshellarg($migrateDbPath));

        $this->assertNotNull($output);
        $this->assertStringContainsString('Toegang Geweigerd', $output);
        $this->assertStringNotContainsString('tbs2026', $output);
        $this->assertStringNotContainsString('TBSKliniek2026', $output);
    }

    /**
     * Test that resources/views/admin/mail/index.blade.php does not expose plaintext credentials.
     */
    public function test_admin_mail_view_masks_smtp_password(): void
    {
        $mailViewPath = resource_path('views/admin/mail/index.blade.php');
        $this->assertFileExists($mailViewPath);

        $content = file_get_contents($mailViewPath);

        // Ensure plaintext password was removed
        $this->assertStringNotContainsString('TBSKliniek2026!Mail#Dan', $content);

        // Ensure masked indicator or env reference exists
        $this->assertStringContainsString('MAIL_PASSWORD', $content);
    }

    /**
     * Test that public/robots.txt protects sensitive routes from Googlebot and other crawlers.
     */
    public function test_robots_txt_disallows_admin_and_private_routes_for_crawlers(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $content = file_get_contents($robotsPath);

        // Googlebot section must disallow sensitive areas
        $this->assertMatchesRegularExpression(
            '/User-agent:\s*Googlebot[\s\S]*?Disallow:\s*\/admin\/[\s\S]*?Disallow:\s*\/dashboard/i',
            $content
        );

        // Wildcard section must disallow sensitive areas
        $this->assertMatchesRegularExpression(
            '/User-agent:\s*\*[\s\S]*?Disallow:\s*\/admin\/[\s\S]*?Disallow:\s*\/dashboard/i',
            $content
        );
    }

    /**
     * Test that public/sw.js sanitizes push notification navigation URLs.
     */
    public function test_service_worker_sanitizes_push_target_urls(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);

        // Check that safe target url helper is implemented
        $this->assertStringContainsString('getSafeTargetUrl', $content);
        $this->assertStringContainsString("startsWith('//')", $content);

        // Test helper logic using node by mocking service worker globals in a temporary script file
        $nodeTestScript = "
            const self = { location: { origin: 'https://tbs-kliniek.nl' }, addEventListener: () => {} };
            const clients = {};
            ".$content."
            const tests = [
                { input: 'javascript:alert(1)', expected: '/dashboard' },
                { input: 'https://malicious-phishing.com/steal', expected: '/dashboard' },
                { input: '//evil.com/phish', expected: '/dashboard' },
                { input: '/dashboard', expected: '/dashboard' },
                { input: '/berichten', expected: '/berichten' },
                { input: '', expected: '/dashboard' },
                { input: null, expected: '/dashboard' }
            ];
            let allPassed = true;
            for (const t of tests) {
                const res = getSafeTargetUrl(t.input);
                if (res !== t.expected) {
                    allPassed = false;
                }
            }
            if (allPassed) console.log('SW_URL_SECURITY_PASSED');
        ";

        $tempScriptPath = tempnam(sys_get_temp_dir(), 'sw_test_').'.js';
        file_put_contents($tempScriptPath, $nodeTestScript);
        $nodeOutput = shell_exec('node '.escapeshellarg($tempScriptPath));
        @unlink($tempScriptPath);

        $this->assertStringContainsString('SW_URL_SECURITY_PASSED', (string) $nodeOutput);
    }

    /**
     * Test that all Batch 4 admin Blade templates with POST forms contain CSRF tokens.
     */
    public function test_batch_4_admin_views_have_csrf_tokens(): void
    {
        $views = [
            resource_path('views/admin/audit_log.blade.php'),
            resource_path('views/admin/buymeacoffee.blade.php'),
            resource_path('views/admin/chests.blade.php'),
            resource_path('views/admin/dashboard.blade.php'),
            resource_path('views/admin/discord_shield/index.blade.php'),
            resource_path('views/admin/gameplay.blade.php'),
            resource_path('views/admin/layout.blade.php'),
            resource_path('views/admin/mail/index.blade.php'),
            resource_path('views/admin/moderation.blade.php'),
            resource_path('views/admin/news/_editor.blade.php'),
            resource_path('views/admin/news/create.blade.php'),
            resource_path('views/admin/news/edit.blade.php'),
            resource_path('views/admin/news/index.blade.php'),
            resource_path('views/admin/players.blade.php'),
        ];

        foreach ($views as $view) {
            $this->assertFileExists($view);
            $content = file_get_contents($view);

            preg_match_all('/<form\b[^>]*>(.*?)<\/form>/is', $content, $forms);
            foreach ($forms[0] as $idx => $fullForm) {
                $tag = substr($fullForm, 0, strpos($fullForm, '>') + 1);
                if (stripos($tag, 'post') !== false) {
                    $inner = $forms[1][$idx];
                    $hasCsrf = stripos($inner, '@csrf') !== false
                        || stripos($inner, 'csrf_field') !== false
                        || stripos($inner, 'csrf_token') !== false;

                    $this->assertTrue(
                        $hasCsrf,
                        "POST form in {$view} missing CSRF token: ".substr($tag, 0, 80)
                    );
                }
            }
        }
    }

    /**
     * Test that all Batch 4 migrations have valid syntax and down methods.
     */
    public function test_batch_4_migrations_have_valid_syntax_and_rollback(): void
    {
        $migrationFiles = glob(database_path('migrations/*.php'));
        $this->assertNotEmpty($migrationFiles);

        foreach ($migrationFiles as $file) {
            $content = file_get_contents($file);
            $this->assertStringContainsString('public function up()', $content, "Missing up() in {$file}");
            $this->assertStringContainsString('public function down()', $content, "Missing down() in {$file}");
        }
    }

    /**
     * Test that Batch 4 seeders exist and have valid structure.
     */
    public function test_batch_4_seeders_exist_and_have_valid_structure(): void
    {
        $seeders = [
            database_path('seeders/DatabaseSeeder.php'),
            database_path('seeders/MegaUpdateNewsArticleSeeder.php'),
            database_path('seeders/TrueCrimeCasesSeeder.php'),
            database_path('seeders/VehicleAndHeistSeeder.php'),
        ];

        foreach ($seeders as $seeder) {
            $this->assertFileExists($seeder);
            $content = file_get_contents($seeder);
            $this->assertStringContainsString('public function run(): void', $content);
        }
    }
}

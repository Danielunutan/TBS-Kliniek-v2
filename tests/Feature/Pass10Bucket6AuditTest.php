<?php

namespace Tests\Feature;

use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\DiscordShieldBan;
use App\Models\DiscordVerification;
use App\Models\GameSetting;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AuditLogService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MegaUpdateNewsArticleSeeder;
use Database\Seeders\TrueCrimeCasesSeeder;
use Database\Seeders\VehicleAndHeistSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket6AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Character $adminCharacter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'owner',
            'is_onboarded' => true,
        ]);

        $this->adminCharacter = Character::factory()->create([
            'user_id' => $this->adminUser->id,
            'name' => 'DirecteurDaniël',
            'cash' => 5000000,
            'bank' => 20000000,
            'stars' => 500,
            'level' => 30,
            'respect' => 10000,
        ]);
    }

    public function test_admin_audit_log_renders_filters_and_synchronizes(): void
    {
        $targetUser = User::factory()->create(['role' => 'user']);
        $targetChar = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'VerdachtePatiënt',
            'level' => 5,
        ]);

        AuditLogService::log(
            action: 'bank_transfer',
            category: 'economy',
            description: 'Verdachte geldoverboeking van €1.500.000',
            character: $targetChar,
            user: $targetUser,
            amount: 1500000,
            details: ['reason' => 'mogelijke witwaspraktijk']
        );

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit_log.index', ['risk' => 'all']));

        $response->assertStatus(200);
        $response->assertSee('Audit Log');
        $response->assertSee('Verdachte geldoverboeking');
        $response->assertSee('1.500.000');
        $response->assertSee('VerdachtePatiënt');

        // Risk filter check
        $filterResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.audit_log.index', ['risk' => 'normaal']));
        $filterResponse->assertStatus(200);

        // Sync route test
        $syncResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.audit_log.sync'));
        $syncResponse->assertRedirect();
        $syncResponse->assertSessionHas('success');
    }

    public function test_admin_buymeacoffee_page_and_manual_linking(): void
    {
        $unmatchedOrder = BuyMeACoffeeOrder::create([
            'bmac_order_id' => 'BMC-TEST-001',
            'supporter_name' => 'VrijgevigeHenk',
            'supporter_email' => 'henk@donatie.nl',
            'amount' => 14.00,
            'currency' => 'EUR',
            'status' => 'unmatched',
            'is_membership' => true,
            'membership_tier' => 'TBS Gangster Elite',
            'message' => 'Veel succes met TBS Kliniek!',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.buymeacoffee.index'));

        $response->assertStatus(200);
        $response->assertSee('Buy Me a Coffee');
        $response->assertSee('VrijgevigeHenk');
        $response->assertSee('€14,00');

        // Link order to admin character
        $linkResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.buymeacoffee.link', $unmatchedOrder), [
                'character_id' => $this->adminCharacter->id,
                'membership_tier' => 'TBS Gangster Elite',
                'grant_rewards' => '1',
            ]);

        $linkResponse->assertRedirect();
        $this->assertEquals('manually_linked', $unmatchedOrder->fresh()->status);
        $this->assertEquals($this->adminCharacter->id, $unmatchedOrder->fresh()->character_id);

        // Manual donation registration
        $manualResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.buymeacoffee.manual'), [
                'supporter_name' => 'Anonieme Donateur',
                'supporter_email' => 'anoniem@tbs.nl',
                'amount' => 6.00,
                'is_membership' => '1',
                'membership_tier' => 'TBS V.I.P. Lid',
                'character_id' => $this->adminCharacter->id,
                'message' => 'Handmatige betaling via overboeking',
            ]);

        $manualResponse->assertRedirect();
        $this->assertDatabaseHas('buymeacoffee_orders', [
            'supporter_name' => 'Anonieme Donateur',
            'amount' => 6.00,
            'character_id' => $this->adminCharacter->id,
        ]);
    }

    public function test_admin_chests_view_and_settings_update(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.chests'));

        $response->assertStatus(200);
        $response->assertSee('Kisten & Smokkeltassen Beheer', false);

        $updateResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.chests.update'), [
                'case_xxl_price_cash' => 55000,
                'case_xxl_price_stars' => 110,
                'case_xxl_min_cash' => 28000,
                'case_xxl_max_cash' => 160000,
                'case_xxl_stars_reward' => 30,
                'bag_sell_reward' => 2600,
                'bag_open_min_cash' => 1300,
                'bag_open_max_cash' => 3900,
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');
        $this->assertEquals('55000', (string) GameSetting::get('case_xxl_price_cash'));
        $this->assertEquals('110', (string) GameSetting::get('case_xxl_price_stars'));
    }

    public function test_admin_dashboard_metrics_and_recent_players(): void
    {
        $playerUser = User::factory()->create(['name' => 'NieuweSpeler']);
        $playerChar = Character::factory()->create([
            'user_id' => $playerUser->id,
            'name' => 'Sjaakie',
            'cash' => 25000,
            'bank' => 75000,
            'level' => 3,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Medewerkers Paneel');
        $response->assertSee('Sjaakie');
        $response->assertSee('Recent Ingeschreven Patiënten');
        $response->assertSee('Geld Circulatie');
    }

    public function test_admin_discord_shield_view_and_ban_management(): void
    {
        DiscordVerification::create([
            'discord_id' => '123456789012345678',
            'username' => 'DiscordGebruiker#1234',
            'avatar_url' => 'https://cdn.discordapp.com/embed/avatars/0.png',
            'ip_address' => '127.0.0.1',
            'fingerprint_hash' => 'hash_test_12345678',
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.discord_shield.index'));

        $response->assertStatus(200);
        $response->assertSee('TBS Discord Shield');
        $response->assertSee('DiscordGebruiker#1234');

        // Add a hardware ban
        $banResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.discord_shield.ban'), [
                'type' => 'ip_address',
                'value' => '192.168.100.50',
                'reason' => 'Ban ontduiking via alt',
            ]);

        $banResponse->assertRedirect();
        $ban = DiscordShieldBan::where('value', '192.168.100.50')->first();
        $this->assertNotNull($ban);
        $this->assertEquals('ip_address', $ban->type);

        // Delete / unban
        $unbanResponse = $this->actingAs($this->adminUser)
            ->delete(route('admin.discord_shield.unban', $ban->id));
        $unbanResponse->assertRedirect();
        $this->assertDatabaseMissing('discord_shield_bans', ['id' => $ban->id]);
    }

    public function test_admin_gameplay_view_and_winrates_update(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.gameplay'));

        $response->assertStatus(200);
        $response->assertSee('Winkansen & Balans Instellingen', false);

        $updateResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.gameplay.update'), [
                'win_rate_casino' => 48,
                'win_rate_theft' => 60,
                'win_rate_hustle' => 65,
                'win_rate_pickpocket' => 50,
                'win_rate_lockers' => 55,
                'attack_energy_cost' => 20,
                'daily_attacks_limit' => 120,
                'vip_hourly_points' => 6,
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');
        $this->assertEquals('48', (string) GameSetting::get('win_rate_casino'));
        $this->assertEquals('60', (string) GameSetting::get('win_rate_theft'));
        $this->assertEquals('120', (string) GameSetting::get('daily_attacks_limit'));
    }

    public function test_admin_moderation_view_and_quick_sanctions(): void
    {
        $offenderUser = User::factory()->create([
            'name' => 'OvertrederUser',
            'is_banned' => false,
        ]);
        $offenderChar = Character::factory()->create([
            'user_id' => $offenderUser->id,
            'name' => 'StoutPatiëntje',
        ]);

        $report = Report::create([
            'reporter_id' => $this->adminCharacter->id,
            'reported_id' => $offenderUser->id,
            'reported_type' => 'user',
            'reported_name' => 'StoutPatiëntje',
            'reason' => 'Ongepast taalgebruik in pleinchat',
            'details' => 'Schold de hele zaalwacht uit',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.moderation.index', ['tab' => 'reports']));

        $response->assertStatus(200);
        $response->assertSee('StoutPatiëntje');
        $response->assertSee('Ongepast taalgebruik in pleinchat');

        // Quick warn
        $warnResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.moderation.warn', $offenderUser), [
                'reason' => 'Eerste waarschuwing voor schelden',
            ]);

        $warnResponse->assertRedirect();
        $this->assertDatabaseHas('user_warnings', [
            'user_id' => $offenderUser->id,
            'reason' => 'Eerste waarschuwing voor schelden',
        ]);

        // Resolve report
        $resolveResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.moderation.report.resolve', $report));
        $resolveResponse->assertRedirect();
        $this->assertEquals('resolved', $report->fresh()->status);

        // Quick ban
        $banResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.moderation.ban', $offenderUser), [
                'duration' => '1_day',
                'reason' => 'Herhaaldelijk wangedrag',
            ]);

        $banResponse->assertRedirect();
        $this->assertTrue($offenderUser->fresh()->is_banned);
        $this->assertNotNull($offenderUser->fresh()->banned_until);
    }

    public function test_admin_layout_badges_and_navigation_counts(): void
    {
        SupportTicket::create([
            'user_id' => $this->adminUser->id,
            'character_id' => $this->adminCharacter->id,
            'ticket_number' => 'TCK-10001',
            'category' => 'general',
            'subject' => 'Vraag over regels',
            'status' => 'open',
            'priority' => 'normal',
        ]);

        Report::create([
            'reporter_id' => $this->adminCharacter->id,
            'reported_id' => $this->adminCharacter->id,
            'reported_type' => 'user',
            'reported_name' => 'ZaalwachtTest',
            'reason' => 'Test melding',
            'status' => 'pending',
        ]);

        BuyMeACoffeeOrder::create([
            'bmac_order_id' => 'BMC-BADGE-001',
            'supporter_name' => 'SupporterBadge',
            'amount' => 6.00,
            'currency' => 'EUR',
            'status' => 'unmatched',
        ]);

        AuditLogService::log(
            action: 'suspicious_exploit',
            category: 'security',
            description: 'Uitzonderlijk hoog risico exploit poging',
            character: $this->adminCharacter,
            user: $this->adminUser,
            amount: 9999999,
        );

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Support Tickets');
        $response->assertSee('Buy Me a Coffee');
        $response->assertSee('Audit Log');
    }

    public function test_seeders_idempotency_and_safe_execution(): void
    {
        // Run DatabaseSeeder twice to confirm idempotency
        $databaseSeeder = new DatabaseSeeder;
        $databaseSeeder->run();
        $databaseSeeder->run();

        // Run MegaUpdateNewsArticleSeeder
        $megaUpdateSeeder = new MegaUpdateNewsArticleSeeder;
        $megaUpdateSeeder->run();
        $megaUpdateSeeder->run();

        // Run TrueCrimeCasesSeeder (capped at 500)
        $trueCrimeSeeder = new TrueCrimeCasesSeeder;
        $trueCrimeSeeder->run();
        $trueCrimeSeeder->run();

        // Run VehicleAndHeistSeeder
        $vehicleHeistSeeder = new VehicleAndHeistSeeder;
        $vehicleHeistSeeder->run();
        $vehicleHeistSeeder->run();

        $this->assertDatabaseHas('news_articles', [
            'slug' => 'mega-update-doelen-battles-bosses-liquidaties-koffers',
        ]);
        $this->assertDatabaseHas('vehicles', [
            'slug' => 'scooter-zip-70cc',
        ]);
        $this->assertDatabaseHas('heists', [
            'slug' => 'bende-heist-dnb-goudconvooi',
        ]);
        $this->assertDatabaseHas('true_crime_cases', [
            'case_number' => 1,
        ]);
    }
}

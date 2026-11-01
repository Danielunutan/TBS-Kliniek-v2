<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Character;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_audit_log_dashboard(): void
    {
        $staffUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $character = Character::create([
            'user_id' => $staffUser->id,
            'name' => 'AdminSpeler',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 15,
        ]);

        AuditLogService::log(
            action: 'bank_transfer',
            category: 'economy',
            description: 'Verdachte geldoverboeking van €15.000.000 naar speler #99',
            character: $character,
            user: $staffUser,
            amount: 15000000,
            details: ['target_id' => 99]
        );

        $response = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Log');
        $response->assertSee('Verdachte geldoverboeking');
        $response->assertSee('15.000.000');
        $response->assertSee('Hoog');
    }

    public function test_audit_log_risk_filter_works(): void
    {
        $staffUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $character = Character::create([
            'user_id' => $staffUser->id,
            'name' => 'TestPlayer',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 5,
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'shop_buy',
            'category' => 'shop',
            'description' => 'Frikandelbroodje gekocht',
            'amount' => 15,
            'risk_level' => 'normaal',
            'risk_score' => 5,
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'bank_transaction',
            'category' => 'economy',
            'description' => 'Massale overboeking',
            'amount' => 20000000,
            'risk_level' => 'hoog',
            'risk_score' => 95,
            'risk_reason' => 'Extreem hoog bedrag',
        ]);

        $response = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['risk' => 'hoog']));

        $response->assertStatus(200);
        $response->assertSee('Massale overboeking');
        $response->assertDontSee('Frikandelbroodje gekocht');
    }

    public function test_regular_player_cannot_access_audit_log(): void
    {
        $regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.audit_log.index'));

        $response->assertStatus(403);
    }

    public function test_audit_log_filters_by_category_and_date_range(): void
    {
        $staffUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $character = Character::factory()->create(['user_id' => $staffUser->id]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'bank_deposit',
            'category' => 'economy',
            'description' => 'Vandaag bankstorting',
            'amount' => 50000,
            'risk_level' => 'normaal',
            'risk_score' => 10,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'theft_pickpocket',
            'category' => 'crime',
            'description' => 'Zakkenrollen van 5 dagen terug',
            'amount' => 1200,
            'risk_level' => 'verdacht',
            'risk_score' => 45,
            'created_at' => now()->subDays(5),
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'shop_purchase',
            'category' => 'shop',
            'description' => 'Winkel aankoop van 20 dagen terug',
            'amount' => 300,
            'risk_level' => 'normaal',
            'risk_score' => 5,
            'created_at' => now()->subDays(20),
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'arm_wrestle_win',
            'category' => 'combat',
            'description' => 'Gevecht van 50 dagen terug',
            'amount' => 10000,
            'risk_level' => 'hoog',
            'risk_score' => 85,
            'created_at' => now()->subDays(50),
        ]);

        $catResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['category' => 'crime']));
        $catResponse->assertOk();
        $catResponse->assertSee('Zakkenrollen van 5 dagen terug');
        $catResponse->assertDontSee('Vandaag bankstorting');

        $todayResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['date' => 'today']));
        $todayResponse->assertOk();
        $todayResponse->assertSee('Vandaag bankstorting');
        $todayResponse->assertDontSee('Zakkenrollen van 5 dagen terug');

        $weekResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['date' => 'week']));
        $weekResponse->assertOk();
        $weekResponse->assertSee('Vandaag bankstorting');
        $weekResponse->assertSee('Zakkenrollen van 5 dagen terug');
        $weekResponse->assertDontSee('Winkel aankoop van 20 dagen terug');

        $monthResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['date' => 'month']));
        $monthResponse->assertOk();
        $monthResponse->assertSee('Winkel aankoop van 20 dagen terug');
        $monthResponse->assertDontSee('Gevecht van 50 dagen terug');
    }

    public function test_audit_log_search_filters_by_player_ip_description_and_risk_reason(): void
    {
        $staffUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $targetChar = Character::factory()->create(['user_id' => $staffUser->id, 'name' => 'SpionPatiënt']);
        $otherChar = Character::factory()->create(['user_id' => $staffUser->id, 'name' => 'WillekeurigeSpeler']);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $targetChar->id,
            'action' => 'target_action',
            'category' => 'moderation',
            'description' => 'Unieke actie omschrijving alpha',
            'ip_address' => '198.51.100.42',
            'risk_level' => 'hoog',
            'risk_score' => 90,
            'risk_reason' => 'Verdacht spoofing patroon',
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $otherChar->id,
            'action' => 'other_action',
            'category' => 'economy',
            'description' => 'Reguliere banktransactie beta',
            'ip_address' => '203.0.113.99',
            'risk_level' => 'normaal',
            'risk_score' => 5,
            'risk_reason' => null,
        ]);

        $searchPlayer = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['search' => 'SpionPatiënt']));
        $searchPlayer->assertOk();
        $searchPlayer->assertSee('Unieke actie omschrijving alpha');
        $searchPlayer->assertDontSee('Reguliere banktransactie beta');

        $searchIp = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['search' => '198.51.100.42']));
        $searchIp->assertOk();
        $searchIp->assertSee('Unieke actie omschrijving alpha');
        $searchIp->assertDontSee('Reguliere banktransactie beta');

        $searchDesc = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['search' => 'omschrijving alpha']));
        $searchDesc->assertOk();
        $searchDesc->assertSee('Unieke actie omschrijving alpha');
        $searchDesc->assertDontSee('Reguliere banktransactie beta');

        $searchReason = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['search' => 'spoofing patroon']));
        $searchReason->assertOk();
        $searchReason->assertSee('Unieke actie omschrijving alpha');
        $searchReason->assertDontSee('Reguliere banktransactie beta');
    }

    public function test_audit_log_filters_by_direct_ip_and_character_id(): void
    {
        $staffUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $charOne = Character::factory()->create(['user_id' => $staffUser->id, 'name' => 'VerdachteEen']);
        $charTwo = Character::factory()->create(['user_id' => $staffUser->id, 'name' => 'VerdachteTwee']);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $charOne->id,
            'action' => 'ip_action_one',
            'category' => 'economy',
            'description' => 'Log van IP One',
            'ip_address' => '192.0.2.11',
            'risk_level' => 'normaal',
            'risk_score' => 10,
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $charTwo->id,
            'action' => 'ip_action_two',
            'category' => 'economy',
            'description' => 'Log van IP Two',
            'ip_address' => '192.0.2.22',
            'risk_level' => 'normaal',
            'risk_score' => 10,
        ]);

        $ipResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['ip' => '192.0.2.11']));
        $ipResponse->assertOk();
        $ipResponse->assertSee('Log van IP One');
        $ipResponse->assertDontSee('Log van IP Two');

        $charResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['character_id' => $charTwo->id]));
        $charResponse->assertOk();
        $charResponse->assertSee('Log van IP Two');
        $charResponse->assertDontSee('Log van IP One');
    }

    public function test_audit_log_sorts_by_highest_risk_highest_amount_and_oldest(): void
    {
        $staffUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $character = Character::factory()->create(['user_id' => $staffUser->id]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'action_low',
            'category' => 'economy',
            'description' => 'Laag risico en klein bedrag',
            'amount' => 50,
            'risk_level' => 'normaal',
            'risk_score' => 5,
            'created_at' => now()->subDays(3),
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'action_high_risk',
            'category' => 'security',
            'description' => 'Hoog risico incident',
            'amount' => 5000,
            'risk_level' => 'hoog',
            'risk_score' => 99,
            'created_at' => now()->subDays(2),
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'character_id' => $character->id,
            'action' => 'action_high_amount',
            'category' => 'economy',
            'description' => 'Groot geldbedrag overboeking',
            'amount' => 50000000,
            'risk_level' => 'verdacht',
            'risk_score' => 60,
            'created_at' => now()->subDay(),
        ]);

        $riskSortResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['sort' => 'highest_risk']));
        $riskSortResponse->assertOk();
        $riskHtml = $riskSortResponse->getContent();
        $this->assertTrue(strpos($riskHtml, 'Hoog risico incident') < strpos($riskHtml, 'Groot geldbedrag overboeking'));

        $amountSortResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['sort' => 'highest_amount']));
        $amountSortResponse->assertOk();
        $amountHtml = $amountSortResponse->getContent();
        $this->assertTrue(strpos($amountHtml, 'Groot geldbedrag overboeking') < strpos($amountHtml, 'Hoog risico incident'));

        $oldestSortResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index', ['sort' => 'oldest']));
        $oldestSortResponse->assertOk();
        $oldestHtml = $oldestSortResponse->getContent();
        $this->assertTrue(strpos($oldestHtml, 'Laag risico en klein bedrag') < strpos($oldestHtml, 'Groot geldbedrag overboeking'));
    }

    public function test_audit_log_detects_and_renders_shared_ip_multi_accounts_and_watchlist(): void
    {
        $staffUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);

        $user1 = User::factory()->create(['name' => 'AccountAlpha']);
        $char1 = Character::factory()->create(['user_id' => $user1->id, 'name' => 'SpelerAlpha']);

        $user2 = User::factory()->create(['name' => 'AccountBeta']);
        $char2 = Character::factory()->create(['user_id' => $user2->id, 'name' => 'SpelerBeta']);

        AuditLog::create([
            'user_id' => $user1->id,
            'character_id' => $char1->id,
            'action' => 'login',
            'category' => 'security',
            'description' => 'Inloggen Alpha',
            'ip_address' => '192.0.2.100',
            'risk_level' => 'hoog',
            'risk_score' => 95,
        ]);

        AuditLog::create([
            'user_id' => $user2->id,
            'character_id' => $char2->id,
            'action' => 'login',
            'category' => 'security',
            'description' => 'Inloggen Beta',
            'ip_address' => '192.0.2.100',
            'risk_level' => 'verdacht',
            'risk_score' => 55,
        ]);

        $response = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.index'));

        $response->assertOk();
        $response->assertSee('192.0.2.100');
        $response->assertSee('SpelerAlpha');
        $response->assertSee('SpelerBeta');
        $response->assertSee('Top Risico Patiënten');
    }

    public function test_audit_log_export_authorization_filters_and_formula_injection_defense(): void
    {
        $staffUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $regularUser = User::factory()->create(['role' => 'user', 'is_staff' => false]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'action' => '+calc_exec',
            'category' => 'security',
            'description' => "=cmd|' /C calc'!A0",
            'risk_reason' => '@SUM(1+1)*cmd',
            'risk_level' => 'hoog',
            'risk_score' => 90,
        ]);

        AuditLog::create([
            'user_id' => $staffUser->id,
            'action' => 'regular_canteen_buy',
            'category' => 'shop',
            'description' => 'Appel gekocht in kantine',
            'risk_level' => 'normaal',
            'risk_score' => 5,
        ]);

        $userResponse = $this->actingAs($regularUser)->get(route('admin.audit_log.export'));
        $userResponse->assertStatus(403);

        $exportResponse = $this->actingAs($staffUser)
            ->get(route('admin.audit_log.export', ['risk' => 'hoog']));

        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', (string) $exportResponse->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', (string) $exportResponse->headers->get('content-disposition'));

        $csv = $exportResponse->streamedContent();
        $this->assertStringContainsString("'=cmd|", $csv);
        $this->assertStringContainsString("'@SUM", $csv);
        $this->assertStringContainsString("'+calc_exec", $csv);
        $this->assertStringNotContainsString('Appel gekocht in kantine', $csv);
    }

    public function test_audit_log_backfill_history_is_idempotent_and_avoids_n_plus_one(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $character = Character::factory()->create(['user_id' => $user->id, 'name' => 'BackfillSpeler']);

        for ($i = 0; $i < 15; $i++) {
            DB::table('bank_transactions')->insert([
                'character_id' => $character->id,
                'type' => 'deposit',
                'source' => 'bank',
                'amount' => 500000 + $i,
                'balance_after' => 1500000 + $i,
                'description' => 'Test backfill banktransactie '.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $statsFirst = AuditLogService::backfillHistory(50);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertGreaterThanOrEqual(15, $statsFirst['total_synced']);
        $this->assertLessThan(35, count($queries));

        $countAfterFirst = AuditLog::count();

        $statsSecond = AuditLogService::backfillHistory(50);
        $this->assertSame(0, $statsSecond['total_synced']);
        $this->assertSame($countAfterFirst, AuditLog::count());
    }
}

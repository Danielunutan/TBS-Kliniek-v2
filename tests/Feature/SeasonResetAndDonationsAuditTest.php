<?php

namespace Tests\Feature;

use App\Http\Controllers\FaqController;
use App\Models\AuditLog;
use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\Gang;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeasonResetAndDonationsAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_season_reset_clears_seasonal_tables_and_resets_character_and_gang_stats(): void
    {
        $user = User::factory()->create();
        $gang = Gang::create([
            'name' => 'Syndicaat Alpha',
            'tag' => 'ALP',
            'specialty' => 'violence',
            'leader_id' => null,
            'treasury' => 5000000,
            'protected_treasury' => 2500000,
            'respect' => 50000,
            'masks' => 25,
            'bling_shops' => 10,
            'attack_power' => 1500,
            'protection_points' => 3000,
            'gang_stars' => 100,
        ]);

        $character = Character::create([
            'user_id' => $user->id,
            'gang_id' => $gang->id,
            'name' => 'Scarface',
            'level' => 45,
            'xp' => 150000,
            'total_xp' => 250000,
            'cash' => 15000000,
            'bank' => 90000000,
            'respect' => 8500,
            'strength' => 500,
            'defense' => 500,
            'stamina' => 500,
            'cell_upgrades' => ['bed' => 3, 'door' => 2],
            'stars' => 250,
            'bullets' => 1000,
            'masks' => 15,
            'xxl_cases' => 5,
        ]);

        $gang->update(['leader_id' => $character->id]);

        // Insert seasonal rows
        if (Schema::hasTable('arm_wrestle_matches')) {
            DB::table('arm_wrestle_matches')->insert([
                'challenger_id' => $character->id,
                'bet_amount' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('character_arm_wrestle_stats')) {
            DB::table('character_arm_wrestle_stats')->insert([
                'character_id' => $character->id,
                'wins' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert news articles: one pinned, one winner, one season 2, one old normal
        $pinnedArticle = NewsArticle::create([
            'title' => 'Belangrijke Mededeling',
            'slug' => 'belangrijke-mededeling',
            'content' => 'Vastgepind bericht',
            'is_pinned' => true,
            'is_published' => true,
        ]);

        $winnerArticle = NewsArticle::create([
            'title' => 'Winnaar van de Seizoensloterij',
            'slug' => 'winnaar-seizoensloterij',
            'content' => 'Gefeliciteerd aan de winnaar!',
            'is_pinned' => false,
            'is_published' => true,
        ]);

        $sprintArticle = NewsArticle::create([
            'title' => 'Frisse Start Seizoen 2',
            'slug' => 'frisse-start-seizoen-2-sprint-30-dagen',
            'content' => 'Seizoen 2 sprint informatie',
            'is_pinned' => false,
            'is_published' => true,
        ]);

        $oldArticle = NewsArticle::create([
            'title' => 'Oud Regulier Bericht',
            'slug' => 'oud-regulier-bericht',
            'content' => 'Gewoon nieuwsbericht uit het verleden',
            'is_pinned' => false,
            'is_published' => true,
        ]);

        // Run season reset command
        $this->artisan('tbs:season-reset', ['--force' => true])->assertSuccessful();

        // Verify character reset
        $character->refresh();
        $this->assertEquals(1, $character->level);
        $this->assertEquals(0, $character->xp);
        $this->assertEquals(250, $character->cash);
        $this->assertEquals(1000, $character->bank);
        $this->assertEquals(10, $character->respect);
        $this->assertEquals(10, $character->strength);
        $this->assertEquals(50, $character->bullets);
        $this->assertEquals(0, $character->masks);
        $this->assertEquals(250, $character->stars); // Stars preserved
        $this->assertNull($character->cell_upgrades);
        $this->assertNotNull($character->last_gang_wage_at);

        // Verify gang reset
        $gang->refresh();
        $this->assertEquals(0, $gang->treasury);
        $this->assertEquals(0, $gang->protected_treasury);
        $this->assertEquals(0, $gang->respect);
        $this->assertEquals(0, $gang->masks);
        $this->assertEquals(0, $gang->bling_shops);
        $this->assertEquals(500, $gang->attack_power);
        $this->assertEquals(1000, $gang->protection_points);
        $this->assertEquals(0, $gang->gang_stars);

        // Verify seasonal tables cleared
        if (Schema::hasTable('arm_wrestle_matches')) {
            $this->assertEquals(0, DB::table('arm_wrestle_matches')->count());
        }
        if (Schema::hasTable('character_arm_wrestle_stats')) {
            $this->assertEquals(0, DB::table('character_arm_wrestle_stats')->count());
        }

        // Verify news articles preserved vs deleted
        $this->assertDatabaseHas('news_articles', ['id' => $pinnedArticle->id]);
        $this->assertDatabaseHas('news_articles', ['id' => $winnerArticle->id]);
        $this->assertDatabaseHas('news_articles', ['id' => $sprintArticle->id]);
        $this->assertDatabaseMissing('news_articles', ['id' => $oldArticle->id]);
    }

    public function test_regrant_donations_is_idempotent_and_skips_cancelled_or_refunded_orders(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Donateur1',
            'stars' => 10,
            'masks' => 0,
            'bullets' => 50,
            'xxl_cases' => 0,
            'level' => 1,
            'cash' => 250,
            'bank' => 1000,
        ]);

        // Order 1: Valid Baas membership
        $validBaasOrder = BuyMeACoffeeOrder::create([
            'character_id' => $character->id,
            'bmac_order_id' => 'BMAC_ORD_001',
            'amount' => 15.00,
            'currency' => 'EUR',
            'status' => 'completed',
            'is_membership' => true,
            'membership_tier' => 'TBS Kliniek Baas',
            'customer_name' => 'Donateur1',
            'customer_email' => 'donateur@example.com',
        ]);

        // Order 2: Refunded order (must be skipped)
        $refundedOrder = BuyMeACoffeeOrder::create([
            'character_id' => $character->id,
            'bmac_order_id' => 'BMAC_ORD_REFUND',
            'amount' => 50.00,
            'currency' => 'EUR',
            'status' => 'refunded',
            'is_membership' => true,
            'membership_tier' => 'VIP Lifetime Baas',
            'customer_name' => 'Donateur1',
            'customer_email' => 'donateur@example.com',
        ]);

        // First run
        $this->artisan('tbs:regrant-donations')->assertSuccessful();

        $character->refresh();
        // Baas tier grants +600 stars, +12 masks, +3 cases, +500 bullets
        $this->assertEquals(610, $character->stars);
        $this->assertEquals(12, $character->masks);
        $this->assertEquals(3, $character->xxl_cases);
        $this->assertEquals(550, $character->bullets);
        $this->assertFalse((bool) $character->is_vip); // Monthly VIP not manually extended per f051dfe
        $this->assertNull($character->vip_expires_at);
        $this->assertEquals(3, $character->perks['supporter_tier']);
        $this->assertEquals(20000, $character->perks['hourly_cash_bonus']);
        $this->assertTrue($character->perks['has_custom_name_color']);
        $this->assertTrue($character->perks['has_sparkle_effect']);

        // Verify audit log exists
        $this->assertDatabaseHas('audit_logs', [
            'character_id' => $character->id,
            'action' => 'buymeacoffee_coulance_regrant',
        ]);

        // Second run: must be strictly IDEMPOTENT (no double-grant)
        $this->artisan('tbs:regrant-donations')->assertSuccessful();

        $character->refresh();
        $this->assertEquals(610, $character->stars);
        $this->assertEquals(12, $character->masks);
        $this->assertEquals(3, $character->xxl_cases);
        $this->assertEquals(550, $character->bullets);
    }

    public function test_regrant_donations_honors_vip_rules_for_single_coffee_lifetime_and_provider_subscribers(): void
    {
        // 1. Single coffee donor (no membership)
        $user1 = User::factory()->create();
        $coffeeChar = Character::create([
            'user_id' => $user1->id,
            'name' => 'SingleCoffeeDonor',
            'stars' => 0,
            'masks' => 0,
            'bullets' => 0,
            'xxl_cases' => 0,
            'level' => 1,
            'cash' => 250,
            'bank' => 1000,
        ]);

        BuyMeACoffeeOrder::create([
            'character_id' => $coffeeChar->id,
            'bmac_order_id' => 'BMAC_ORD_COFFEE_1',
            'amount' => 5.00,
            'currency' => 'EUR',
            'status' => 'completed',
            'is_membership' => false,
            'membership_tier' => null,
            'coffee_num' => 1,
            'customer_name' => 'CoffeeDonor',
            'customer_email' => 'coffee@example.com',
        ]);

        // 2. Lifetime VIP donor
        $user2 = User::factory()->create();
        $lifetimeChar = Character::create([
            'user_id' => $user2->id,
            'name' => 'LifetimeDonor',
            'stars' => 0,
            'masks' => 0,
            'bullets' => 0,
            'xxl_cases' => 0,
            'level' => 1,
            'cash' => 250,
            'bank' => 1000,
        ]);

        BuyMeACoffeeOrder::create([
            'character_id' => $lifetimeChar->id,
            'bmac_order_id' => 'BMAC_ORD_LIFETIME',
            'amount' => 50.00,
            'currency' => 'EUR',
            'status' => 'completed',
            'is_membership' => true,
            'membership_tier' => 'VIP Lifetime Baas',
            'customer_name' => 'LifetimeDonor',
            'customer_email' => 'lifetime@example.com',
        ]);

        // 3. Provider subscriber character ID 66
        $user3 = User::factory()->create();
        $providerChar = new Character([
            'user_id' => $user3->id,
            'name' => 'ProviderSubscriber66',
            'stars' => 0,
            'masks' => 0,
            'bullets' => 0,
            'xxl_cases' => 0,
            'level' => 1,
            'cash' => 250,
            'bank' => 1000,
        ]);
        $providerChar->id = 66;
        $providerChar->save();

        BuyMeACoffeeOrder::create([
            'character_id' => $providerChar->id,
            'bmac_order_id' => 'BMAC_ORD_PROVIDER_66',
            'amount' => 15.00,
            'currency' => 'EUR',
            'status' => 'completed',
            'is_membership' => true,
            'membership_tier' => 'TBS Kliniek Baas',
            'customer_name' => 'ProviderSubscriber66',
            'customer_email' => 'sub66@example.com',
        ]);

        $this->artisan('tbs:regrant-donations')->assertSuccessful();

        $coffeeChar->refresh();
        $this->assertFalse((bool) $coffeeChar->is_vip);
        $this->assertNull($coffeeChar->vip_expires_at);
        $this->assertTrue($coffeeChar->perks['is_donator']);
        $this->assertArrayNotHasKey('supporter_tier', $coffeeChar->perks);

        $lifetimeChar->refresh();
        $this->assertTrue($lifetimeChar->is_vip);
        $this->assertNotNull($lifetimeChar->vip_expires_at);
        $this->assertGreaterThan(now()->addYears(90), $lifetimeChar->vip_expires_at);
        $this->assertEquals(4, $lifetimeChar->perks['supporter_tier']);
        $this->assertEquals(50000, $lifetimeChar->perks['hourly_cash_bonus']);

        $providerChar->refresh();
        $this->assertTrue($providerChar->is_vip);
        $this->assertEquals('2026-12-29 22:56:36', $providerChar->vip_expires_at->toDateTimeString());
        $this->assertEquals(3, $providerChar->perks['supporter_tier']);
    }

    public function test_clear_plein_chat_and_cache_poisoning_recovery(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Chatterbox',
            'level' => 5,
            'cash' => 250,
            'bank' => 1000,
        ]);

        // Create 2 messages
        $msg1 = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Hallo allemaal!',
        ]);
        $msg2 = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Tweede bericht.',
        ]);

        // Run clear-plein-chat command
        $this->artisan('tbs:clear-plein-chat')->assertSuccessful();
        $this->assertEquals(0, ChatMessage::count());
        $this->assertNull(Cache::get('chat_last_id_global'));

        // Client polls with stale after_id=9999 from old season
        $responseStale = $this->actingAs($user)->getJson(route('chat.public.messages', [
            'channel' => 'global',
            'after_id' => 9999,
        ]));

        $responseStale->assertOk();
        $responseStale->assertJson([
            'success' => true,
            'messages' => [],
            'last_id' => 0,
        ]);

        // Verify cache was NOT poisoned with 9999
        $cachedId = Cache::get('chat_last_id_global');
        $this->assertTrue($cachedId === null || (int) $cachedId === 0);

        // A new message is posted in Season 2
        $newMsg = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Welkom in Seizoen 2!',
        ]);

        // Client polls again with stale after_id=9999; should reset and receive new message
        $responseRecover = $this->actingAs($user)->getJson(route('chat.public.messages', [
            'channel' => 'global',
            'after_id' => 9999,
        ]));

        $responseRecover->assertOk();
        $responseRecover->assertJson([
            'success' => true,
            'last_id' => (int) $newMsg->id,
        ]);
        $this->assertCount(1, $responseRecover->json('messages'));
        $this->assertEquals('Welkom in Seizoen 2!', $responseRecover->json('messages.0.message'));
    }

    public function test_news_article_renders_alerts_within_html_content(): void
    {
        $raw = '<div class="alert-wrapper">'."\n".'> [!IMPORTANT]'."\n".'> **Speciale Waarschuwing**'."\n".'> Pas goed op je kluisgeld!'."\n".'</div>';
        $article = new NewsArticle(['content' => $raw]);
        $html = $article->formatted_content;

        $this->assertStringNotContainsString('[!IMPORTANT]', $html);
        $this->assertStringContainsString('Belangrijk', $html);
        $this->assertStringContainsString('Speciale Waarschuwing', $html);
        $this->assertStringContainsString('Pas goed op je kluisgeld!', $html);
    }

    public function test_faq_contains_updated_wage_inruil_and_jackpots(): void
    {
        $faqData = FaqController::getFaqData();
        $faqSerialized = json_encode($faqData);

        $this->assertStringContainsString('contant op zak', $faqSerialized);
        $this->assertStringContainsString('Dealer Inruil', $faqSerialized);
        $this->assertStringContainsString('Productie-Jackpot', $faqSerialized);
    }

    public function test_launch_season_one_resets_protected_treasury_and_war_dominance(): void
    {
        $gang = Gang::create([
            'name' => 'Syndicaat Beta',
            'tag' => 'BET',
            'specialty' => 'violence',
            'treasury' => 1000000,
            'protected_treasury' => 500000,
            'war_dominance_until' => now()->addDay(),
        ]);

        $this->artisan('tbs:launch-season-one')->assertSuccessful();

        $gang->refresh();
        $this->assertEquals(0, $gang->treasury);
        $this->assertEquals(0, $gang->protected_treasury);
        $this->assertNull($gang->war_dominance_until);
    }

    public function test_reset_gangs_and_economy_aborts_without_confirmation(): void
    {
        $this->artisan('tbs:reset-gangs-and-economy')
            ->expectsConfirmation('Weet je zeker dat je alle bendes en de economie wilt resetten? Dit kan niet ongedaan worden gemaakt!', 'no')
            ->assertFailed();
    }

    public function test_reset_gangs_and_economy_rolls_back_on_dry_run(): void
    {
        $gang = Gang::create([
            'name' => 'DryRunGang',
            'tag' => 'DRY',
            'specialty' => 'violence',
            'treasury' => 5000000,
        ]);

        $this->artisan('tbs:reset-gangs-and-economy', ['--dry-run' => true])
            ->assertSuccessful();

        $gang->refresh();
        $this->assertEquals(5000000, $gang->treasury);
    }

    public function test_reset_gangs_and_economy_resets_cleanly_on_force_without_deleting_audit_logs(): void
    {
        $gang = Gang::create([
            'name' => 'ResetGang',
            'tag' => 'RSTG',
            'specialty' => 'violence',
            'treasury' => 10000000,
            'protected_treasury' => 2000000,
            'respect' => 50000,
            'attack_power' => 2000,
            'protection_points' => 3000,
        ]);

        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'ActivePlayer',
            'level' => 15,
            'cash' => 500000,
            'bank' => 2000000,
            'respect' => 300,
        ]);

        $auditLog = AuditLog::create([
            'character_id' => $character->id,
            'action' => 'theft',
            'category' => 'security',
            'description' => 'Test robbery audit log',
            'created_at' => now(),
        ]);

        $this->artisan('tbs:reset-gangs-and-economy', ['--force' => true])
            ->assertSuccessful();

        $gang->refresh();
        $this->assertEquals(0, $gang->treasury);
        $this->assertEquals(0, $gang->protected_treasury);
        $this->assertEquals(0, $gang->respect);
        $this->assertEquals(500, $gang->attack_power);
        $this->assertEquals(1000, $gang->protection_points);

        $character->refresh();
        $this->assertEquals(1, $character->level);
        $this->assertEquals(250, $character->cash);
        $this->assertEquals(1000, $character->bank);
        $this->assertEquals(10, $character->respect);
        $this->assertEquals(100, $character->energy);
        $this->assertEquals(100, $character->health);
        $this->assertNotNull($character->last_gang_wage_at);

        // Crucial: audit logs must NOT be deleted
        $this->assertDatabaseHas('audit_logs', ['id' => $auditLog->id]);
    }

    public function test_restart_hustle_challenge_aborts_without_confirmation_and_clears_entries_on_force(): void
    {
        $this->artisan('tbs:restart-hustle-challenge')
            ->expectsConfirmation('Weet je zeker dat je De Grote Hossel Challenge wilt herstarten en alle bestaande inzendingen wilt wissen?', 'no')
            ->assertFailed();

        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Hustler',
            'level' => 5,
            'cash' => 250,
            'bank' => 1000,
        ]);

        DB::table('event_hustle_entries')->insert([
            'character_id' => $character->id,
            'hustle_id' => 1,
            'created_at' => now(),
        ]);

        $this->assertEquals(1, DB::table('event_hustle_entries')->count());

        $this->artisan('tbs:restart-hustle-challenge', ['--force' => true])
            ->assertSuccessful();

        $this->assertEquals(0, DB::table('event_hustle_entries')->count());
    }
}

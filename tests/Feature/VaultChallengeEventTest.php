<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\EventVaultCracking;
use App\Models\NewsArticle;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\EventVaultCrackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class VaultChallengeEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(string $name, int $cash = 1000, int $stars = 10): Character
    {
        $user = User::factory()->create();

        return Character::create([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => $cash,
            'bank' => 500,
            'stars' => $stars,
            'respect' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
        ]);
    }

    public function test_previous_challenge_winner_article_exists_with_definitive_results(): void
    {
        // Run migration to generate articles and data
        Artisan::call('migrate');

        $article = NewsArticle::where('slug', 'winnaar-bekend-de-grote-drugsproductie')->first();
        $this->assertNotNull($article, 'Winner news article must exist.');
        $this->assertEquals('2026-11-02 00:00:00', $article->published_at->format('Y-m-d H:i:s'));
        $this->assertStringContainsString('De Grote Drugsproductie', $article->title);
        $this->assertStringContainsString('5.000.000', $article->content);
        $this->assertStringContainsString('1.000 Sterren', $article->content);

        // Ensure formatted HTML parses Markdown headers, lists, and winner card cleanly
        $formatted = $article->formatted_content;
        $this->assertStringContainsString('<h3', $formatted);
        $this->assertStringContainsString('<ul', $formatted);
        $this->assertStringContainsString('Officiële Kampioen', $formatted);
        $this->assertStringNotContainsString('###', $formatted);
        $this->assertStringNotContainsString('[winner=', $formatted);
    }

    public function test_new_challenge_news_article_exists_and_displays_leaderboard_partial(): void
    {
        Artisan::call('migrate');

        $char = $this->createCharacter('KluisTester');
        $this->actingAs($char->user);

        $article = NewsArticle::where('slug', EventVaultCrackingService::EVENT_SLUG)->first();
        $this->assertNotNull($article, 'New challenge news article must exist.');
        $this->assertTrue($article->is_pinned);
        $this->assertEquals('2026-11-02 00:00:00', $article->published_at->format('Y-m-d H:i:s'));
        $this->assertStringContainsString('2.500 Sterren', $article->title);
        $this->assertStringContainsString('100 Kluiscodes', $article->title);

        $formatted = $article->formatted_content;
        $this->assertStringContainsString('<h3', $formatted);
        $this->assertStringContainsString('<ul', $formatted);
        $this->assertStringNotContainsString('###', $formatted);
        $this->assertStringNotContainsString('[vault_challenge_leaderboard]', $formatted);

        // Advance time to Monday 2 November 00:00:00
        Carbon::setTestNow(Carbon::parse('2026-11-02 00:00:00'));

        $response = $this->get(route('news.show', $article->slug));
        $response->assertStatus(200);
        $response->assertSee('Live Ranglijst - De Grote Kluiscode Challenge');
        $response->assertSee('2.500 Sterren');
        $response->assertSee('100 Kluiscodes');
        $response->assertSee('api/event/vault-challenge/leaderboard');

        Carbon::setTestNow();
    }

    public function test_articles_scheduled_visibility_before_and_after_monday_midnight(): void
    {
        Artisan::call('migrate');

        $char = $this->createCharacter('ReguliereLezer');
        $this->actingAs($char->user);

        // 1. Before launch time (Sunday 1 Nov 23:45)
        Carbon::setTestNow(Carbon::parse('2026-11-01 23:45:00'));

        $responseBefore = $this->get(route('news.index'));
        $responseBefore->assertStatus(200);
        $responseBefore->assertDontSee('De Grote Kluiscode Jacht');
        $responseBefore->assertDontSee('winnaar-bekend-de-grote-drugsproductie');

        // 2. Exactly at launch time (Monday 2 Nov 00:00:00)
        Carbon::setTestNow(Carbon::parse('2026-11-02 00:00:00'));

        $responseAfter = $this->get(route('news.index'));
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('De Grote Kluiscode Jacht');
        $responseAfter->assertSee('Winnaar Bekend: De Grote Drugsproductie');

        Carbon::setTestNow();
    }

    public function test_leaderboard_api_returns_rankings_and_player_position(): void
    {
        $char1 = $this->createCharacter('KrakerAlpha');
        $char2 = $this->createCharacter('KrakerBeta');

        Carbon::setTestNow(Carbon::parse('2026-11-02 14:00:00'));

        EventVaultCracking::create([
            'character_id' => $char1->id,
            'amount' => 15,
            'source' => 'code_redeem',
            'created_at' => now(),
        ]);

        EventVaultCracking::create([
            'character_id' => $char2->id,
            'amount' => 8,
            'source' => 'code_redeem',
            'created_at' => now(),
        ]);

        $this->actingAs($char1->user);

        $response = $this->get(route('api.event.vault_challenge_leaderboard'));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['is_active']);
        $this->assertFalse($data['has_ended']);
        $this->assertEquals(2500, $data['prize_stars']);
        $this->assertEquals(100, $data['prize_vault_codes']);
        $this->assertEquals(2, $data['total_participants']);
        $this->assertEquals(23, $data['total_codes_cracked']);

        $this->assertCount(2, $data['leaderboard']);
        $this->assertEquals($char1->name, $data['leaderboard'][0]['name']);
        $this->assertEquals(1, $data['leaderboard'][0]['rank']);
        $this->assertEquals(15, $data['leaderboard'][0]['total_cracked']);
        $this->assertEquals('15 codes', $data['leaderboard'][0]['total_cracked_formatted']);

        $this->assertNotNull($data['my_rank']);
        $this->assertEquals(1, $data['my_rank']['rank']);

        Carbon::setTestNow();
    }

    public function test_redeeming_vault_codes_records_to_challenge_only_during_active_window(): void
    {
        $char = $this->createCharacter('CodeKraker');
        $this->actingAs($char->user);

        $code = VaultCode::create([
            'code' => 'TBS-TEST1234',
            'reward_cash' => 2000,
            'reward_stars' => 1,
            'reward_tokens' => 2,
            'character_id' => $char->id,
            'is_redeemed' => false,
        ]);

        // 1. Before event window (Sunday 1 Nov 22:00) -> should NOT record
        Carbon::setTestNow(Carbon::parse('2026-11-01 22:00:00'));

        $this->post(route('vault.redeem'), [
            'code_id' => $code->id,
        ]);

        $this->assertDatabaseMissing('event_vault_crackings', [
            'character_id' => $char->id,
        ]);

        // 2. During event window (Monday 2 Nov 10:00) -> should record
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00'));

        $secondCode = VaultCode::create([
            'code' => 'TBS-TEST5678',
            'reward_cash' => 2000,
            'reward_stars' => 1,
            'reward_tokens' => 2,
            'character_id' => $char->id,
            'is_redeemed' => false,
        ]);

        $response = $this->post(route('vault.redeem'), [
            'code_id' => $secondCode->id,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('event_vault_crackings', [
            'character_id' => $char->id,
            'amount' => 1,
            'source' => 'code_redeem',
        ]);

        Carbon::setTestNow();
    }

    public function test_resolve_winner_awards_2500_stars_and_100_vault_codes(): void
    {
        $winnerChar = $this->createCharacter('TopKluisKraker', 1000, 50);
        $loserChar = $this->createCharacter('MinderKluisKraker', 1000, 50);

        EventVaultCracking::create([
            'character_id' => $winnerChar->id,
            'amount' => 45,
            'source' => 'code_redeem',
            'created_at' => Carbon::parse('2026-11-02 12:00:00'),
        ]);

        EventVaultCracking::create([
            'character_id' => $loserChar->id,
            'amount' => 10,
            'source' => 'code_redeem',
            'created_at' => Carbon::parse('2026-11-02 13:00:00'),
        ]);

        // Advance time past Wednesday 4 November 23:59:59
        Carbon::setTestNow(Carbon::parse('2026-11-05 00:01:00'));

        $this->assertTrue(EventVaultCrackingService::hasEnded());

        $result = EventVaultCrackingService::resolveWinner();
        $this->assertNotNull($result);
        $this->assertEquals($winnerChar->id, $result['winner']->id);
        $this->assertEquals(45, $result['total_cracked']);

        $winnerChar->refresh();
        $this->assertEquals(50 + 2500, $winnerChar->stars);

        // Check 100 vault codes awarded
        $awardedCodesCount = VaultCode::where('character_id', $winnerChar->id)
            ->where('code', 'LIKE', 'EVENT-%')
            ->count();
        $this->assertEquals(100, $awardedCodesCount);

        // Notifications and Messages
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $winnerChar->id,
            'type' => EventVaultCrackingService::NOTIFICATION_TYPE,
        ]);

        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $winnerChar->id,
            'subject' => '🏆 HOOFDPRIJS: 2.500 Sterren & 100 Kluiscodes voor De Grote Kluiscode Challenge!',
        ]);

        // Loser should not have received stars or event codes
        $loserChar->refresh();
        $this->assertEquals(50, $loserChar->stars);
        $this->assertEquals(0, VaultCode::where('character_id', $loserChar->id)->count());

        Carbon::setTestNow();
    }

    public function test_artisan_command_resolves_vault_challenge(): void
    {
        $winner = $this->createCharacter('ArtisanKluisWinner', 1000, 10);

        EventVaultCracking::create([
            'character_id' => $winner->id,
            'amount' => 25,
            'source' => 'code_redeem',
            'created_at' => Carbon::parse('2026-11-03 10:00:00'),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-05 00:05:00'));

        $exitCode = Artisan::call('event:resolve-vault-challenge');
        $this->assertEquals(0, $exitCode);

        $winner->refresh();
        $this->assertEquals(10 + 2500, $winner->stars);
        $this->assertEquals(100, VaultCode::where('character_id', $winner->id)->where('code', 'LIKE', 'EVENT-%')->count());

        Carbon::setTestNow();
    }
}

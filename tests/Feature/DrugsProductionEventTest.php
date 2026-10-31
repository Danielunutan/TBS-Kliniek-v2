<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\EventDrugProduction;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\EventDrugsProductionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DrugsProductionEventTest extends TestCase
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

    public function test_news_article_exists_and_displays_leaderboard_partial(): void
    {
        $char = $this->createCharacter('DrugslordTester');
        $this->actingAs($char->user);

        $article = NewsArticle::where('slug', EventDrugsProductionService::EVENT_SLUG)->first();
        $this->assertNotNull($article, 'De Grote Drugsproductie news article must exist.');
        $this->assertStringContainsString('5.000.000', $article->title);

        $response = $this->get(route('news.show', $article->slug));
        $response->assertStatus(200);
        $response->assertSee('Live Ranglijst - De Grote Drugsproductie');
        $response->assertSee('€5.000.000');
        $response->assertSee('1.000 Sterren');
        $response->assertSee('api/event/drugs-production/leaderboard');
    }

    public function test_leaderboard_api_returns_event_data_and_personal_rank(): void
    {
        $char1 = $this->createCharacter('ProducentAlpha');
        $char2 = $this->createCharacter('ProducentBeta');

        // Create production records during event window
        Carbon::setTestNow(Carbon::parse('2026-10-31 12:00:00'));

        EventDrugProduction::create([
            'character_id' => $char1->id,
            'amount' => 50,
            'drug_type' => 'wiet',
            'created_at' => now(),
        ]);

        EventDrugProduction::create([
            'character_id' => $char2->id,
            'amount' => 20,
            'drug_type' => 'ritalin',
            'created_at' => now(),
        ]);

        $this->actingAs($char1->user);

        $response = $this->get(route('api.event.drugs_production_leaderboard'));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['is_active']);
        $this->assertFalse($data['has_ended']);
        $this->assertEquals(5000000, $data['prize_cash']);
        $this->assertEquals(1000, $data['prize_stars']);
        $this->assertEquals(2, $data['total_participants']);
        $this->assertEquals(70, $data['total_drugs_produced']);

        // Check leaderboard ranks
        $this->assertCount(2, $data['leaderboard']);
        $this->assertEquals($char1->name, $data['leaderboard'][0]['name']);
        $this->assertEquals(1, $data['leaderboard'][0]['rank']);
        $this->assertEquals(50, $data['leaderboard'][0]['total_produced']);

        // Check personalized rank
        $this->assertNotNull($data['my_rank']);
        $this->assertEquals(1, $data['my_rank']['rank']);
        $this->assertEquals('50 stuks', $data['my_rank']['total_produced_formatted']);

        Carbon::setTestNow();
    }

    public function test_smuggle_produce_records_to_event_when_active(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 10:00:00'));

        $char = $this->createCharacter('KwekerKarel');
        $this->actingAs($char->user);

        $response = $this->post(route('smuggle.produce'), [
            'type' => 'sigaretten',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('event_drug_productions', [
            'character_id' => $char->id,
            'amount' => 10,
            'drug_type' => 'sigaretten',
        ]);

        Carbon::setTestNow();
    }

    public function test_resolve_winner_awards_prizes_notification_and_message(): void
    {
        $winnerChar = $this->createCharacter('TopProducent', 500, 10);
        $loserChar = $this->createCharacter('MinderProducent', 500, 10);

        // Record production during event
        EventDrugProduction::create([
            'character_id' => $winnerChar->id,
            'amount' => 200,
            'drug_type' => 'wiet',
            'created_at' => Carbon::parse('2026-10-31 14:00:00'),
        ]);

        EventDrugProduction::create([
            'character_id' => $loserChar->id,
            'amount' => 50,
            'drug_type' => 'sigaretten',
            'created_at' => Carbon::parse('2026-10-31 15:00:00'),
        ]);

        // Move time past Sunday 23:59:59
        Carbon::setTestNow(Carbon::parse('2026-11-02 00:01:00'));

        $this->assertTrue(EventDrugsProductionService::hasEnded());

        $result = EventDrugsProductionService::resolveWinner();
        $this->assertNotNull($result);
        $this->assertEquals($winnerChar->id, $result['winner']->id);
        $this->assertEquals(200, $result['total_produced']);

        // Check prizes awarded
        $winnerChar->refresh();
        $this->assertEquals(500 + 5000000, $winnerChar->cash);
        $this->assertEquals(10 + 1000, $winnerChar->stars);

        // Loser should not have received prizes
        $loserChar->refresh();
        $this->assertEquals(500, $loserChar->cash);
        $this->assertEquals(10, $loserChar->stars);

        // Notification and Message
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $winnerChar->id,
            'type' => EventDrugsProductionService::NOTIFICATION_TYPE,
        ]);

        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $winnerChar->id,
            'subject' => '🏆 HOOFDPRIJS: €5.000.000 & 1.000 Sterren voor De Grote Drugsproductie!',
        ]);

        // Verify double resolution is prevented
        $secondResult = EventDrugsProductionService::resolveWinner();
        $this->assertNull($secondResult);

        $winnerChar->refresh();
        $this->assertEquals(500 + 5000000, $winnerChar->cash);
        $this->assertEquals(10 + 1000, $winnerChar->stars);

        Carbon::setTestNow();
    }

    public function test_artisan_command_resolves_drugs_production_event(): void
    {
        $winner = $this->createCharacter('ArtisanWinner', 1000, 20);

        EventDrugProduction::create([
            'character_id' => $winner->id,
            'amount' => 120,
            'drug_type' => 'wiet',
            'created_at' => Carbon::parse('2026-10-31 16:00:00'),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-02 00:05:00'));

        $exitCode = Artisan::call('event:resolve-drugs-production');
        $this->assertEquals(0, $exitCode);

        $winner->refresh();
        $this->assertEquals(1000 + 5000000, $winner->cash);
        $this->assertEquals(20 + 1000, $winner->stars);

        Carbon::setTestNow();
    }

    public function test_wiet_production_and_cooldown_in_smuggle_controller(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 11:00:00'));

        $char = $this->createCharacter('WietKweker', 1000, 100);
        $this->actingAs($char->user);

        // 1. Produce wiet
        $response = $this->post(route('smuggle.produce'), ['type' => 'wiet']);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('event_drug_productions', [
            'character_id' => $char->id,
            'amount' => 8,
            'drug_type' => 'wiet',
        ]);

        $this->assertDatabaseHas('character_items', [
            'character_id' => $char->id,
            'quantity' => 8,
        ]);

        // 2. Cooldown is active
        $this->assertTrue($char->cooldownRemaining('produce_wiet') > 0);

        // 3. Skip cooldown with stars
        $skipResponse = $this->post(route('cooldown.skip'), [
            'action' => 'produce_wiet',
        ]);
        $skipResponse->assertSessionHas('success');
        $this->assertEquals(0, $char->fresh()->cooldownRemaining('produce_wiet'));

        Carbon::setTestNow();
    }

    public function test_auto_resolution_triggers_on_leaderboard_fetch_after_deadline(): void
    {
        $winner = $this->createCharacter('AutoResolvedWinner', 2000, 50);

        EventDrugProduction::create([
            'character_id' => $winner->id,
            'amount' => 300,
            'drug_type' => 'wiet',
            'created_at' => Carbon::parse('2026-10-31 18:00:00'),
        ]);

        // Time past Sunday 23:59:59
        Carbon::setTestNow(Carbon::parse('2026-11-02 00:02:00'));

        $this->actingAs($winner->user);

        // Fetching the leaderboard endpoint should automatically settle the winner
        $response = $this->get(route('api.event.drugs_production_leaderboard'));
        $response->assertStatus(200);

        $winner->refresh();
        $this->assertEquals(2000 + 5000000, $winner->cash);
        $this->assertEquals(50 + 1000, $winner->stars);

        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $winner->id,
            'type' => EventDrugsProductionService::NOTIFICATION_TYPE,
        ]);

        Carbon::setTestNow();
    }

    public function test_article_author_updates_to_daniel_via_migration(): void
    {
        $daniel = $this->createCharacter('Daniël');
        $daniel->user->update(['email' => 'danielunutan@icloud.com']);

        $article = NewsArticle::where('slug', EventDrugsProductionService::EVENT_SLUG)->first();
        $this->assertNotNull($article);

        // Run the update migration
        $migration = require database_path('migrations/2026_10_31_082000_update_drugs_production_article_author_to_daniel.php');
        $migration->up();

        $article->refresh();
        $this->assertEquals($daniel->id, $article->character_id);
        $this->assertStringContainsString('Daniël', $article->content);

        // When viewing the article, it shows Daniel as author
        $this->actingAs($daniel->user);
        $response = $this->get(route('news.show', $article->slug));
        $response->assertStatus(200);
        $response->assertSee('Daniël');
    }
}

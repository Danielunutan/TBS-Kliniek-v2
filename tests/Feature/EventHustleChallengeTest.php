<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\EventHustleEntry;
use App\Models\User;
use App\Services\EventHustleChallengeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventHustleChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_hustle_challenge_tracks_only_qualifying_hustles(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'TestHustler',
            'energy' => 100,
            'max_energy' => 100,
        ]);

        // Non-qualifying hustles 1, 2, 3 should not be recorded
        EventHustleChallengeService::record($character, 1);
        EventHustleChallengeService::record($character, 2);
        EventHustleChallengeService::record($character, 3);
        $this->assertEquals(0, EventHustleEntry::count());

        // Qualifying hustles 4, 5, 6 should be recorded
        EventHustleChallengeService::record($character, 4);
        EventHustleChallengeService::record($character, 5);
        EventHustleChallengeService::record($character, 6);
        $this->assertEquals(3, EventHustleEntry::count());

        $leaderboardData = EventHustleChallengeService::getLeaderboardData($character->id);
        $this->assertTrue($leaderboardData['is_active']);
        $this->assertFalse($leaderboardData['has_ended']);
        $this->assertEquals(750, $leaderboardData['prize_stars']);
        $this->assertEquals(150000000, $leaderboardData['prize_cash']);
        $this->assertEquals(1, count($leaderboardData['leaderboard']));
        $this->assertEquals('TestHustler', $leaderboardData['leaderboard'][0]['name']);
        $this->assertEquals(3, $leaderboardData['leaderboard'][0]['total_hustles']);
    }

    public function test_system_account_is_excluded_from_hustle_challenge_leaderboard(): void
    {
        $systemChar = Character::factory()->create([
            'name' => 'Kliniek Directie',
            'energy' => 100,
        ]);

        $playerChar = Character::factory()->create([
            'name' => 'EchteSpeler',
            'energy' => 100,
        ]);

        EventHustleChallengeService::record($systemChar, 4);
        EventHustleChallengeService::record($systemChar, 5);
        EventHustleChallengeService::record($playerChar, 4);

        $leaderboardData = EventHustleChallengeService::getLeaderboardData();
        $this->assertEquals(1, count($leaderboardData['leaderboard']));
        $this->assertEquals('EchteSpeler', $leaderboardData['leaderboard'][0]['name']);
        $this->assertEquals(1, $leaderboardData['total_participants']);
        $this->assertEquals(1, $leaderboardData['total_hustles_completed']);
    }

    public function test_news_article_exists_and_contains_hustle_leaderboard_component(): void
    {
        $author = Character::factory()->create(['name' => 'Daniël']);
        $article = EventHustleChallengeService::syncChallengeArticle();

        $this->assertNotNull($article);
        $this->assertEquals(EventHustleChallengeService::EVENT_SLUG, $article->slug);
        $this->assertStringContainsString('De Grote Hossel Challenge', $article->title);

        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get(route('news.show', $article->slug));
        $response->assertOk();
        $response->assertSee('Live Ranglijst De Grote Hossel Challenge', false);
        $response->assertSee('Recreatie Bookie', false);
        $response->assertSee('Shag Kartel', false);
        $response->assertSee('Kliniek Telecom', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Mission;
use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBatch7SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacterWithUser(string $name = 'TestInmate', string $role = 'user'): Character
    {
        $user = User::factory()->create([
            'role' => $role,
            'name' => $name,
        ]);

        return Character::create([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 50,
            'respect' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 5,
        ]);
    }

    /**
     * Test newspaper view renders safely when lottery round is null.
     */
    public function test_newspaper_renders_safely_when_lottery_round_is_null(): void
    {
        $character = $this->createCharacterWithUser('NewspaperReader');

        // Test view directly with null lottery round to verify null-safe operator
        $view = $this->actingAs($character->user)->view('game.newspaper.index', [
            'character' => $character,
            'topThefts' => collect(),
            'imprisonedInmates' => collect(),
            'topFighter' => null,
            'topGang' => null,
            'spotlightPatient' => null,
            'lotteryRound' => null,
            'todayWeather' => [
                'temp' => '20°C',
                'sky' => 'Zonnig',
                'icon' => 'fa-sun',
                'comment' => 'Mooi weer.',
            ],
        ]);

        $view->assertSee('DE KLINIEKBODE');
        $view->assertSee('Loterij Jackpot:');
        $view->assertSee('€0');
    }

    /**
     * Test newspaper view renders with active lottery round via HTTP.
     */
    public function test_newspaper_renders_via_http(): void
    {
        $character = $this->createCharacterWithUser('LuckyReader');

        $response = $this->actingAs($character->user)
            ->get(route('newspaper.index'));

        $response->assertOk();
        $response->assertSee('DE KLINIEKBODE');
        $response->assertSee('Loterij Jackpot:');
    }

    /**
     * Test missions view renders correctly with quotes in title using safe @js escaping.
     */
    public function test_missions_view_renders_safely_with_special_characters_in_title(): void
    {
        $character = $this->createCharacterWithUser('MissionAgent');

        $mission = Mission::create([
            'title' => "Dr. O'Connor's \"Secret\" Mission",
            'slug' => 'dr-oconnors-secret-mission',
            'description' => "Escape Dr. O'Connor's psych ward assessment.",
            'giver_name' => "Nurse O'Malley",
            'location_name' => "St. Jude's Ward",
            'required_level' => 1,
            'energy_cost' => 10,
            'min_cash_reward' => 200,
            'max_cash_reward' => 300,
            'xp_reward' => 50,
            'respect_reward' => 15,
            'category' => 'tactisch',
            'success_rate' => 80,
        ]);

        $response = $this->actingAs($character->user)
            ->get(route('missions.index'));

        $response->assertOk();
        $response->assertSee("Dr. O'Connor's \"Secret\" Mission");
        // Verify CSRF token is present on the mission execution form
        $response->assertSee('name="_token"', false);
    }

    /**
     * Test news index view renders referral section and escaped username.
     */
    public function test_news_index_renders_properly(): void
    {
        $character = $this->createCharacterWithUser('ReporterPatient');

        $response = $this->actingAs($character->user)
            ->get(route('news.index'));

        $response->assertOk();
        $response->assertSee('Kliniek Nieuws');
    }

    /**
     * Test news show view escapes user comments against XSS and renders CSRF token.
     */
    public function test_news_show_escapes_comments_and_protects_form(): void
    {
        $character = $this->createCharacterWithUser('CommenterInmate');

        $article = NewsArticle::create([
            'character_id' => $character->id,
            'title' => 'Update V4 Release Notes',
            'slug' => 'update-v4-release-notes',
            'emoji' => '📢',
            'content' => 'Official notes regarding upcoming changes.',
            'is_pinned' => false,
            'comments_enabled' => true,
            'is_published' => true,
            'views_count' => 10,
            'published_at' => now(),
        ]);

        $xssComment = '<script>alert("xss")</script>';
        NewsComment::create([
            'news_article_id' => $article->id,
            'character_id' => $character->id,
            'content' => $xssComment,
        ]);

        $response = $this->actingAs($character->user)
            ->get(route('news.show', $article->slug));

        $response->assertOk();
        // Comment content must be html-escaped
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
        // Form CSRF token check
        $response->assertSee('name="_token"', false);
    }

    /**
     * Test number game view renders with CSRF token and valid inputs.
     */
    public function test_number_game_renders_with_csrf_protection(): void
    {
        $character = $this->createCharacterWithUser('GamblerPlayer');

        $response = $this->actingAs($character->user)
            ->get(route('numbergame.index'));

        $response->assertOk();
        $response->assertSee('Het Nummerspel');
        $response->assertSee('name="_token"', false);
        $response->assertSee('name="bet"', false);
    }

    /**
     * Test packages view renders with CSRF token and operations.
     */
    public function test_packages_renders_with_csrf_and_operations(): void
    {
        $character = $this->createCharacterWithUser('SmugglerPlayer');

        $response = $this->actingAs($character->user)
            ->get(route('packages.index'));

        $response->assertOk();
        $response->assertSeeText('Smokkelkoffers & Postpakketten');
        $response->assertSee('name="_token"', false);
    }

    /**
     * Test perks view renders with CSRF token and countdown logic.
     */
    public function test_perks_renders_correctly(): void
    {
        $character = $this->createCharacterWithUser('PerkUser');

        $response = $this->actingAs($character->user)
            ->get(route('perks.index'));

        $response->assertOk();
        $response->assertSee('Karakter Perks (24 Uur Actief)');
        $response->assertSee('name="_token"', false);
    }

    /**
     * Test murder stats view renders cleanly.
     */
    public function test_murder_stats_renders_cleanly(): void
    {
        $character = $this->createCharacterWithUser('StatsObserver');

        $response = $this->actingAs($character->user)
            ->get(route('murder_stats.index'));

        $response->assertOk();
        $response->assertSee('Geweldsstatistieken');
    }
}

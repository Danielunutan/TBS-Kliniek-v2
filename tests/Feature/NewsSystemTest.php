<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacterWithUser(string $name, string $role = 'user', bool $isStaff = false): Character
    {
        $user = User::factory()->create([
            'role' => $role,
            'is_staff' => $isStaff,
            'is_onboarded' => true,
        ]);

        return Character::create([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 1000,
            'bank' => 500,
            'stars' => 10,
            'respect' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
            'is_onboarded' => true,
        ]);
    }

    public function test_news_index_displays_articles_and_pinned_items(): void
    {
        $char = $this->createCharacterWithUser('NieuwsLezer', 'user');
        $this->actingAs($char->user);

        $response = $this->get(route('news.index'));
        $response->assertStatus(200);
        $response->assertSee('Kliniek Nieuws');
        $response->assertSee('Hyves');
        $response->assertSee('iOS App Update');
        $response->assertSee('Ghost Mode update');
    }

    public function test_dashboard_displays_laatste_nieuws_widget(): void
    {
        $char = $this->createCharacterWithUser('DashboardKijker', 'user');
        $this->actingAs($char->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('TBS Kliniek Berichten');
        $response->assertSee('Hyves');
        $response->assertSee('Alle berichten bekijken');
    }

    public function test_news_show_displays_article_content_and_increases_views(): void
    {
        $char = $this->createCharacterWithUser('ArtikelLezer', 'user');
        $this->actingAs($char->user);

        $article = NewsArticle::where('slug', 'hyves')->firstOrFail();
        $initialViews = $article->views_count;

        $response = $this->get(route('news.show', $article->slug));
        $response->assertStatus(200);
        $response->assertSee('Hyves');
        $response->assertSee('TBS Kliniek zit nu op Hyves');

        $article->refresh();
        $this->assertEquals($initialViews + 1, $article->views_count);
    }

    public function test_user_can_comment_on_open_topic(): void
    {
        $char = $this->createCharacterWithUser('Reageerder', 'user');
        $this->actingAs($char->user);

        $article = NewsArticle::where('slug', 'ios-app-update')->firstOrFail();
        $this->assertTrue($article->comments_enabled);

        $response = $this->post(route('news.comment', $article), [
            'content' => 'Top update! De app werkt nu veel sneller op mijn telefoon.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('news_comments', [
            'news_article_id' => $article->id,
            'character_id' => $char->id,
            'content' => 'Top update! De app werkt nu veel sneller op mijn telefoon.',
        ]);
    }

    public function test_user_cannot_comment_on_closed_topic(): void
    {
        $char = $this->createCharacterWithUser('GeblokkeerdeReageerder', 'user');
        $this->actingAs($char->user);

        $article = NewsArticle::where('slug', 'hyves')->firstOrFail();
        $this->assertFalse($article->comments_enabled);

        $response = $this->post(route('news.comment', $article), [
            'content' => 'Mag ik toch reageren?',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('news_comments', [
            'news_article_id' => $article->id,
            'content' => 'Mag ik toch reageren?',
        ]);
    }

    public function test_moderator_can_delete_comment(): void
    {
        $authorChar = $this->createCharacterWithUser('AuteurComment', 'user');
        $article = NewsArticle::where('slug', 'ios-app-update')->firstOrFail();

        $comment = NewsComment::create([
            'news_article_id' => $article->id,
            'character_id' => $authorChar->id,
            'content' => 'Ongepaste reactie die weg moet',
        ]);

        $modChar = $this->createCharacterWithUser('ModratorNews', 'moderator', true);
        $this->actingAs($modChar->user);

        $response = $this->delete(route('news.comment.destroy', $comment));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('news_comments', ['id' => $comment->id]);
    }

    public function test_admin_can_create_and_manage_news_articles(): void
    {
        $adminChar = $this->createCharacterWithUser('NieuwsAdmin', 'admin', true);
        $this->actingAs($adminChar->user);

        // 1. Create news article with custom BBCode / markdown teksteditor tags
        $response = $this->post(route('admin.news.store'), [
            'title' => 'Grote Zomerupdate Kliniek',
            'emoji' => '🔥',
            'content' => "[h2]Welkom bij de Zomerupdate[/h2]\n\nEr zijn [b]nieuwe features[/b] toegevoegd!\n\n[link=https://tbskliniek.nl]Klik hier voor meer info[/link]\n[img]https://i.imgur.com/example.png[/img]",
            'is_pinned' => 1,
            'comments_enabled' => 1,
            'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $response->assertSessionHas('success');

        $created = NewsArticle::where('title', 'Grote Zomerupdate Kliniek')->firstOrFail();
        $this->assertEquals('grote-zomerupdate-kliniek', $created->slug);
        $this->assertTrue($created->is_pinned);
        $this->assertTrue($created->comments_enabled);

        // Formatted content renders links and tags
        $this->assertStringContainsString('<h2', $created->formatted_content);
        $this->assertStringContainsString('<strong', $created->formatted_content);
        $this->assertStringContainsString('<a href="https://tbskliniek.nl"', $created->formatted_content);
        $this->assertStringContainsString('<img src="https://i.imgur.com/example.png"', $created->formatted_content);

        // 2. Toggle comments to closed topic
        $toggleResponse = $this->post(route('admin.news.toggle-comments', $created));
        $toggleResponse->assertSessionHas('success');
        $created->refresh();
        $this->assertFalse($created->comments_enabled);

        // 3. Delete news article
        $deleteResponse = $this->delete(route('admin.news.destroy', $created));
        $deleteResponse->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseMissing('news_articles', ['id' => $created->id]);
    }
}

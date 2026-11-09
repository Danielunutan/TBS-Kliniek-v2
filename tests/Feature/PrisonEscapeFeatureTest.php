<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrisonEscapeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(array $attrs = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.uniqid(),
            'level' => 5,
            'energy' => 50,
            'max_energy' => 100,
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_reason' => null,
            'prison_escapes' => 0,
            'respect' => 10,
        ], $attrs));
    }

    public function test_cannot_escape_when_not_in_prison(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => false,
            'prison_until' => null,
            'energy' => 50,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('info', 'Je zit momenteel niet in de isoleercel!');
    }

    public function test_escape_fails_when_not_enough_energy(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'energy' => 5,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error');
    }

    public function test_escape_attempt_processes_when_in_prison_with_energy(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'energy' => 50,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $character->refresh();
        $this->assertLessThan(50, $character->energy);

        if ($character->isImprisoned()) {
            $response->assertRedirect(route('prison'));
            $response->assertSessionHas('error');
        } else {
            $response->assertRedirect(route('dashboard'));
            $response->assertSessionHas('success');
            $this->assertFalse($character->is_in_prison);
            $this->assertNull($character->prison_until);
        }
    }

    public function test_escape_attempt_is_limited_to_once_per_sentence(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'prison_escape_attempted' => true,
            'energy' => 50,
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.escape'));

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error', 'Je hebt je ontsnappingspoging voor deze straftijd al verbruikt! Gebruik de stoomwasserij, koop de cipier om of koop jezelf direct vrij met Sterren ⭐.');
        $this->assertEquals(50, $character->fresh()->energy);
    }

    public function test_imprisoned_player_can_reduce_sentence_via_laundry_shift_and_hits_cooldown(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(120),
            'energy' => 50,
            'experience' => 0,
            'perks' => [],
        ]);

        $response = $this->actingAs($character->user)->post(route('prison.reduce'), [
            'action' => 'wasserij',
        ]);

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(40, $character->energy);
        $this->assertLessThanOrEqual(80, $character->remainingPrisonSeconds());
        $this->assertNotEmpty($character->perks['prison_action_cooldown_until'] ?? null);

        // Immediate second attempt should be blocked by cooldown
        $secondResponse = $this->actingAs($character->user)->from(route('prison'))->post(route('prison.reduce'), [
            'action' => 'wasserij',
        ]);
        $secondResponse->assertSessionHas('error');
        $this->assertEquals(40, $character->fresh()->energy);
    }

    public function test_imprisoned_player_can_bribe_guard_or_fail_when_insufficient_funds(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(180),
            'cash' => 500,
            'bank' => 0,
            'perks' => [],
        ]);

        // Insufficient funds (< € 2.500)
        $failResponse = $this->actingAs($character->user)->from(route('prison'))->post(route('prison.reduce'), [
            'action' => 'omkopen',
        ]);
        $failResponse->assertSessionHas('error');
        $this->assertEquals(500, $character->fresh()->cash);

        // Give sufficient funds and retry
        $character->update(['cash' => 5000]);
        $bribeResponse = $this->actingAs($character->user)->post(route('prison.reduce'), [
            'action' => 'omkopen',
        ]);
        $bribeResponse->assertRedirect(route('prison'));
        $character->refresh();
        $this->assertEquals(2500, $character->cash);
        $this->assertNotEmpty($character->perks['prison_action_cooldown_until'] ?? null);
    }

    public function test_free_player_can_choose_breakout_method_stil_or_geweld(): void
    {
        $rescuer = $this->createCharacter([
            'is_in_prison' => false,
            'energy' => 80,
            'intelligence' => 40,
            'strength' => 40,
        ]);

        $inmate = $this->createCharacter([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'prison_reason' => 'Isolatie test',
        ]);

        $response = $this->actingAs($rescuer->user)->from(route('prison'))->post(route('prison.breakout', $inmate->id), [
            'method' => 'geweld',
        ]);

        $rescuer->refresh();
        $this->assertEquals(65, $rescuer->energy);
        $this->assertEquals(1, $rescuer->breakout_attempts);
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public function test_pickpocket_observe_reduces_alertness_and_grants_scout_bonus(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => false,
            'energy' => 50,
        ]);

        $response = $this->actingAs($character->user)
            ->from(route('pickpocket.index'))
            ->withSession([
                "pickpocket_alertness_{$character->id}" => 60,
                "pickpocket_scouted_{$character->id}" => false,
            ])
            ->post(route('pickpocket.observe'));

        $response->assertRedirect(route('pickpocket.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(48, $character->fresh()->energy);
        $this->assertEquals(35, session("pickpocket_alertness_{$character->id}"));
        $this->assertTrue((bool) session("pickpocket_scouted_{$character->id}"));
    }

    public function test_package_open_bag_with_crack_method_and_chest_slot(): void
    {
        $character = $this->createCharacter([
            'is_in_prison' => false,
            'unopened_bags' => 2,
            'bank' => 1000,
        ]);

        $response = $this->actingAs($character->user)->from(route('packages.index'))->post(route('packages.open-bag'), [
            'crack_method' => 'precisie',
            'chest_slot' => 2,
        ]);

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(1, $character->unopened_bags);
        $this->assertGreaterThan(1000, $character->bank);
    }

    public function test_gameplay_overhaul_news_article_migration_creates_detailed_article(): void
    {
        $article = NewsArticle::where('slug', 'grote-gameplay-update-6-vernieuwde-kliniek-systemen')->first();

        $this->assertNotNull($article);
        $this->assertTrue($article->is_published);
        $this->assertNotNull($article->published_at);
        $this->assertStringContainsString('24 Uur Battle', $article->content);
        $this->assertStringContainsString('Dealen & Smokkel', $article->content);
        $this->assertStringContainsString('Koffers & Pakketjes', $article->content);
        $this->assertStringContainsString('Zakkenrollen', $article->content);
        $this->assertStringContainsString('Winkel', $article->content);
        $this->assertStringContainsString('Gevangenis', $article->content);
        $this->assertStringContainsString('Klinische Etmaal-Escalatie', $article->formatted_content);
    }
}

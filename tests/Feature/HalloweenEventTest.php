<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\HalloweenQuestProgress;
use App\Models\Item;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\HalloweenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalloweenEventTest extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = []): array
    {
        $user = User::factory()->create();
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate'.rand(1000, 9999),
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 50,
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 10,
            'pumpkin_seeds' => 200,
        ], $attributes));

        return [$user, $character];
    }

    public function test_player_can_view_autumn_quests_page(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->get(route('halloween.quests'));

        $response->assertOk();
        $response->assertSee('De Gouden Herfstoogst 2026');
        $response->assertSee('Herfstoogst Quests');
        $response->assertSee('Gouden Eikels');
    }

    public function test_player_can_view_autumn_shop_page(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->get(route('halloween.shop'));

        $response->assertOk();
        $response->assertSee('Herfstoogst Winkel');
        $response->assertSee('Vallende Herfstbladeren');
        $response->assertSee('Haalbaar');
    }

    public function test_autumn_progress_records_actions_and_can_be_claimed(): void
    {
        [$user, $character] = $this->createInmate();

        HalloweenService::record($character, 'theft', 3);

        $periodKey = 'daily_'.now()->toDateString();
        $progress = HalloweenQuestProgress::where('character_id', $character->id)
            ->where('quest_key', 'daily_theft_3')
            ->where('period_key', $periodKey)
            ->first();

        $this->assertNotNull($progress);
        $this->assertEquals(3, $progress->current_count);
        $this->assertTrue($progress->isCompleted());
        $this->assertFalse($progress->is_claimed);

        $response = $this->actingAs($user)->post(route('halloween.claim-quest', 'daily_theft_3'));

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(225, $character->pumpkin_seeds); // 200 initial + 25 reward
    }

    public function test_player_can_buy_and_equip_obtainable_autumn_avatar_border(): void
    {
        [$user, $character] = $this->createInmate(['pumpkin_seeds' => 200]);

        $response = $this->actingAs($user)->post(route('halloween.buy'), [
            'item_key' => 'autumn_ember',
        ]);

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(80, $character->pumpkin_seeds); // 200 - 120
        $this->assertTrue($character->hasBorder('autumn_ember'));
        $this->assertEquals('autumn_ember', $character->equipped_border);
        $this->assertStringContainsString('ring-amber-500', $character->getAvatarBorderClasses());

        // Unequip border
        $response = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'none',
        ]);

        $character->refresh();
        $this->assertNull($character->equipped_border);
    }

    public function test_claimed_halloween_items_and_borders_are_kept_permanently_and_labeled_niet_meer_haalbaar(): void
    {
        [$user, $character] = $this->createInmate([
            'pumpkin_seeds' => 500,
            'unlocked_borders' => ['pumpkin_glow', 'gothic_king'],
            'equipped_border' => 'pumpkin_glow',
        ]);

        $voodooItem = Item::firstOrCreate(
            ['slug' => 'halloween_voodoo_doll'],
            [
                'name' => '🕯️ Behekste Voodoo Pop',
                'description' => 'Zeldzame handgemaakte pop uit Halloween 2026.',
                'category' => 'special',
                'rarity' => 'legendary',
                'price_kc' => 2500,
                'energy_restore' => 0,
                'icon' => 'fa-wand-magic-sparkles',
                'is_buyable' => false,
            ]
        );

        $charItem = CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $voodooItem->id,
            'quantity' => 2,
        ]);

        // 1. Player keeps Halloween borders and sees them labeled as "Niet meer haalbaar" on their profile
        $profileResponse = $this->actingAs($user)->get(route('profile.me'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Spooky Pumpkin Glow');
        $profileResponse->assertSee('Magere Hein Kroon');
        $profileResponse->assertSee('Niet meer haalbaar');

        // 2. Player can switch between their claimed Halloween borders at any time
        $equipResponse = $this->actingAs($user)->post(route('halloween.equip-border'), [
            'border_key' => 'gothic_king',
        ]);
        $equipResponse->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals('gothic_king', $character->equipped_border);

        // 3. Player cannot accidentally sell/lose their Halloween collector item in the Depot
        $sellAttempt = $this->actingAs($user)->post(route('depot.sell-item', $charItem), [
            'quantity' => 1,
        ]);
        $sellAttempt->assertSessionHas('error');
        $this->assertDatabaseHas('character_items', [
            'id' => $charItem->id,
            'quantity' => 2,
        ]);

        // 4. New players cannot buy retired Halloween borders or items anymore
        [$newUser, $newCharacter] = $this->createInmate(['pumpkin_seeds' => 500]);
        $buyRetiredBorder = $this->actingAs($newUser)->post(route('halloween.buy'), [
            'item_key' => 'pumpkin_glow',
        ]);
        $buyRetiredBorder->assertSessionHas('error');
        $newCharacter->refresh();
        $this->assertFalse($newCharacter->hasBorder('pumpkin_glow'));
        $this->assertEquals(500, $newCharacter->pumpkin_seeds);
    }

    public function test_profile_borders_overview_page_displays_all_borders_with_haalbaar_and_niet_haalbaar_labels(): void
    {
        [$user, $character] = $this->createInmate([
            'unlocked_borders' => ['pumpkin_glow'],
        ]);

        $response = $this->actingAs($user)->get(route('borders.index', ['sort' => 'status']));

        $response->assertOk();
        $response->assertSee('Alle Profielkaders van het Spel');
        $response->assertSee('Vallende Herfstbladeren');
        $response->assertSee('Bloedrode Oogstmaan');
        $response->assertSee('Gouden Stormeik');
        $response->assertSee('Keizer van de Herfstoogst');
        $response->assertSee('Spooky Pumpkin Glow');
        $response->assertSee('Bloederige Vampier');
        $response->assertSee('Spookhuis Paarse Mist');
        $response->assertSee('Magere Hein Kroon');
        $response->assertSee('Haalbaar');
        $response->assertSee('Niet haalbaar');
        $response->assertSee('Niet meer haalbaar');
    }

    public function test_halloween_completion_and_autumn_event_news_article_is_published_with_statistics(): void
    {
        [$user, $character] = $this->createInmate();

        $article = NewsArticle::where('slug', 'halloween-quest-voltooid-verzamelobjecten-en-start-herfst-event')->first();
        $this->assertNotNull($article);
        $this->assertTrue($article->is_published);
        $this->assertSame('🍂', $article->emoji);
        $this->assertStringContainsString('<ul', $article->formatted_content);
        $this->assertStringContainsString('<li', $article->formatted_content);

        $response = $this->actingAs($user)->get(route('news.show', $article->slug));
        $response->assertOk();
        $response->assertSee('Halloween-Quest Voltooid');
        $response->assertSee('Officiële Statistieken: Halloween Event 2026 in Cijfers');
        $response->assertSee('Totaal Aantal Deelnemers');
        $response->assertSee('Populairste Beloning in de Shop');
        $response->assertSee('Niet meer haalbaar');
        $response->assertSee('De Gouden Herfstoogst');
        $response->assertSee('Gouden Eikels (🌰)');
        $response->assertSee('/profielkaders');

        // Also verify harvest_blessing (+15% combat boost)
        [$boostUser, $boostChar] = $this->createInmate([
            'pumpkin_seeds' => 300,
            'strength' => 100,
            'defense' => 100,
        ]);
        $this->assertSame(100, $boostChar->getEffectiveStrength());
        $this->assertSame(100, $boostChar->getEffectiveDefense());

        $buyBoost = $this->actingAs($boostUser)->post(route('halloween.buy'), [
            'item_key' => 'harvest_blessing',
        ]);
        $buyBoost->assertSessionHas('success');
        $boostChar->refresh();
        $this->assertTrue($boostChar->hasPerk('magere_hein_zegening'));
        $this->assertSame(115, $boostChar->getEffectiveStrength());
        $this->assertSame(115, $boostChar->getEffectiveDefense());
    }
}

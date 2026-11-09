<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\MarketplaceListing;
use App\Models\Mission;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class Pass6Bucket8AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_8_casino_views_disable_play_when_comatose_and_default_rps_choice(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user);

        $rps = $this->get(route('rps.index'));
        $rps->assertOk();
        $rps->assertSee('value="steen" class="peer sr-only" required checked', false);
        $rps->assertSee('disabled', false);

        $numberGame = $this->get(route('numbergame.index'));
        $numberGame->assertOk();
        $numberGame->assertSee('disabled', false);

        $roulette = $this->get(route('roulette.index'));
        $roulette->assertOk();
        $roulette->assertSee('disabled', false);

        $pickpocket = $this->get(route('pickpocket.index'));
        $pickpocket->assertOk();
        $pickpocket->assertSee('disabled', false);

        $perks = $this->get(route('perks.index'));
        $perks->assertOk();
        $perks->assertSee('disabled', false);

        $profile = $this->get(route('profile', $character->name));
        $profile->assertOk();
    }

    public function test_bucket_8_infirmary_and_informants_disable_actions_when_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'stars' => 25,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user);

        $infirmary = $this->get(route('infirmary.index'));
        $infirmary->assertOk();
        $infirmary->assertSee('disabled', false);

        $informants = $this->get(route('informants.index'));
        $informants->assertOk();
        $informants->assertSee('disabled', false);
    }

    public function test_bucket_8_isolation_lockers_and_lottery_disable_when_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 500000,
            'stars' => 100,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user);

        $isolation = $this->get(route('isolation.index'));
        $isolation->assertOk();
        $isolation->assertSee('disabled', false);

        $lockers = $this->get(route('lockers.index'));
        $lockers->assertOk();
        $lockers->assertSee('disabled', false);

        $lottery = $this->get(route('lottery.index'));
        $lottery->assertOk();
        $lottery->assertSee('disabled', false);
    }

    public function test_bucket_8_marketplace_memory_messages_missions_packages_disable_when_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'level' => 1,
            'cash' => 500000,
            'stars' => 200,
            'energy' => 100,
            'unopened_bags' => 5,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $otherUser = User::factory()->create(['is_onboarded' => true]);
        $otherChar = Character::factory()->create([
            'user_id' => $otherUser->id,
            'level' => 1,
        ]);

        MarketplaceListing::create([
            'seller_id' => $otherChar->id,
            'item_type' => 'other',
            'title' => 'Test Item',
            'amount' => 1,
            'price' => 100,
            'currency' => 'cash',
            'is_sold' => false,
        ]);

        Mission::create([
            'title' => 'Smokkel Missie',
            'slug' => 'smokkel-missie',
            'description' => 'Test missie',
            'energy_cost' => 10,
            'success_rate' => 90,
            'required_level' => 1,
            'min_cash_reward' => 100,
            'max_cash_reward' => 200,
            'xp_reward' => 10,
        ]);

        $this->actingAs($user);

        $marketplace = $this->get(route('marketplace.index'));
        $marketplace->assertOk();
        $marketplace->assertSee('disabled', false);

        $memory = $this->get(route('memory.index'));
        $memory->assertOk();
        $memory->assertSee('disabled', false);

        $messagesHtml = view('game.messages', [
            'character' => $character,
            'unreadCount' => 0,
            'filter' => 'all',
            'messages' => new LengthAwarePaginator([], 0, 15),
            'friends' => collect([$otherChar]),
            'otherPlayers' => collect([$otherChar]),
        ])->render();
        $this->assertStringContainsString('Brief Versturen', $messagesHtml);
        $this->assertStringContainsString('disabled', $messagesHtml);

        $missions = $this->get(route('missions.index'));
        $missions->assertOk();
        $missions->assertSee('disabled', false);

        $packages = $this->get(route('packages.index'));
        $packages->assertOk();
        $packages->assertSee('disabled', false);

        $article = NewsArticle::create([
            'character_id' => $otherChar->id,
            'title' => 'Kliniek Opening',
            'slug' => 'kliniek-opening',
            'content' => 'Test nieuws artikel inhoud.',
            'comments_enabled' => true,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $newsRes = $this->get(route('news.show', $article->slug));
        $newsRes->assertOk();
        $newsRes->assertSee('disabled', false);
    }

    public function test_bucket_8_prison_profile_radio_and_account_views(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 5000,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $victimUser = User::factory()->create(['is_onboarded' => true]);
        $victim = Character::factory()->create([
            'user_id' => $victimUser->id,
            'cash' => 2500,
        ]);

        $this->actingAs($user);

        $prison = $this->get(route('prison'));
        $prison->assertOk();

        $radio = $this->get(route('radio.index'));
        $radio->assertRedirect(route('dashboard'));

        $profile = $this->get(route('profile', $victim->name));
        $profile->assertOk();
        $profile->assertSee('Beroof Patiënt');
        $profile->assertSee('disabled', false);

        $account = $this->get(route('settings.account'));
        $account->assertOk();

        $members = $this->get(route('members.index'));
        $members->assertOk();

        $leaderboard = $this->get(route('leaderboard.index'));
        $leaderboard->assertOk();
    }

    public function test_kliniek_directie_is_excluded_from_leaderboard_and_rankings(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'Kliniek Directie',
            'level' => 99,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 5000000,
            'bank' => 5000000,
            'respect' => 999999,
            'is_onboarded' => true,
        ]);

        $normalUser = User::factory()->create();
        $normalChar = Character::create([
            'user_id' => $normalUser->id,
            'name' => 'NormaleSpeler',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'bank' => 1000,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $this->actingAs($normalUser);

        // Leaderboard test
        $response = $this->get(route('leaderboard.index', ['type' => 'level']));
        $response->assertOk();
        $response->assertDontSee('Kliniek Directie');
        $response->assertSee('NormaleSpeler');

        // Character::getTopRankings test
        $top = Character::getTopRankings(10);
        $this->assertFalse($top->contains('name', 'Kliniek Directie'));

        // Fight list test
        $fightResponse = $this->get(route('fight.index'));
        $fightResponse->assertOk();
        $fightResponse->assertDontSee('Kliniek Directie');
    }
}

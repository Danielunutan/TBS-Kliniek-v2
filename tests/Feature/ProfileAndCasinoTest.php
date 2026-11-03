<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAndCasinoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 10,
            'respect' => 10,
            'title' => 'Modelpatiënt',
        ], $attrs));
    }

    public function test_highlow_cannot_exploit_king_and_enforces_cooldown(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, ['cash' => 50000]);

        // Access index: card should never be 1 (Ace) or 13 (King)
        $this->actingAs($user)->get(route('highlow.index'))->assertStatus(200);
        $card = session('highlow_card');
        $this->assertGreaterThanOrEqual(2, $card);
        $this->assertLessThanOrEqual(12, $card);

        // Play a round
        $response = $this->actingAs($user)->post(route('highlow.play'), [
            'choice' => 'lager',
            'bet' => 100,
        ]);
        $response->assertSessionHasNoErrors();

        // Immediate next bet should be rejected due to 6s cooldown
        $spamResponse = $this->actingAs($user)->post(route('highlow.play'), [
            'choice' => 'hoger',
            'bet' => 100,
        ]);
        $spamResponse->assertSessionHas('error');
    }

    public function test_user_can_give_respect_once_per_24_hours(): void
    {
        $user1 = User::factory()->create();
        $char1 = $this->createCharacter($user1);

        $user2 = User::factory()->create();
        $char2 = $this->createCharacter($user2, ['respect' => 20]);

        // Char1 gives respect to Char2
        $response = $this->actingAs($user1)->post(route('profile.respect', $char2->name));
        $response->assertSessionHas('success');

        $char2->refresh();
        $this->assertEquals(21, $char2->respect);

        // Trying again within 24 hours should be blocked
        $secondResponse = $this->actingAs($user1)->post(route('profile.respect', $char2->name));
        $secondResponse->assertSessionHas('error');

        // Cannot give respect to self
        $selfResponse = $this->actingAs($user2)->post(route('profile.respect', $char2->name));
        $selfResponse->assertSessionHas('error');
    }

    public function test_user_can_select_unlocked_title(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, ['level' => 1, 'fights_won' => 0]);

        // 'Modelpatiënt' is unlocked by default
        $response = $this->actingAs($user)->post(route('profile.title'), [
            'title' => 'Modelpatiënt',
        ]);
        $response->assertSessionHas('success');

        // 'Gevaarlijk Onvoorspelbaar' requires level 5 -> should fail for level 1
        $lockedResponse = $this->actingAs($user)->post(route('profile.title'), [
            'title' => 'Gevaarlijk Onvoorspelbaar',
        ]);
        $lockedResponse->assertSessionHas('error');

        // Upgrade character to level 5
        $character->level = 5;
        $character->save();
        $user->refresh();

        $unlockedResponse = $this->actingAs($user)->post(route('profile.title'), [
            'title' => 'Gevaarlijk Onvoorspelbaar',
        ]);
        $unlockedResponse->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals('Gevaarlijk Onvoorspelbaar', $character->title);
    }

    public function test_profile_displays_psychiatric_report_and_medals(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 3,
            'fights_won' => 6,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show', $character->name));
        $response->assertStatus(200);
        $response->assertSee('Psychiatrisch Onderzoeksverslag');
        $response->assertSee('Dr. H. van Beek');
        $response->assertSee('Dossier Medailles');
        $response->assertSee('Luchtplaats Kampioen');
        $response->assertSee('Inrichting & Dossier');
        $response->assertSee('Luchtplaats & Gevechten');
        $response->assertSee('Misdaad & Boevenpad');
        $response->assertSee('Zwart Geld & Financiën');
        $response->assertSee('Broederschap & Netwerk');
        $response->assertSee('Missies & Discipline');

        $badges = $character->getBadges();
        $this->assertCount(24, $badges);
        $this->assertCount(6, Character::getBadgeSets());
    }

    public function test_profile_customization_modal_defaults_to_name_style_and_status_has_closing_quote(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'status_message' => 'Vers binnengebracht via Discord!',
        ]);

        $response = $this->actingAs($user)->get(route('profile.me'));
        $response->assertStatus(200);

        // Verification 1: Modal defaults to 'name_style' (Naam en glow)
        $response->assertSee("activeCustomTab: 'name_style'", false);
        $response->assertSee("activeCustomTab = 'name_style'", false);

        // Verification 2: Status is wrapped in opening and closing purple quotes
        $response->assertSee('<i class="fa-solid fa-quote-left text-purple-400 mr-2 opacity-75"></i>', false);
        $response->assertSee('<span>Vers binnengebracht via Discord!</span>', false);
        $response->assertSee('<i class="fa-solid fa-quote-right text-purple-400 ml-2 opacity-75"></i>', false);
    }
}

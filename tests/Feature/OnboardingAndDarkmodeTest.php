<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingAndDarkmodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_onboarded_user_can_access_game_dashboard(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::create([
            'user_id' => $user->id,
            'name' => 'KliniekBoef',
            'username' => 'kliniekboef',
            'display_name' => 'Kliniek Boef',
            'is_onboarded' => true,
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 500,
            'bank' => 1000,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_non_onboarded_user_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->notOnboarded()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('onboarding.index'));
    }

    public function test_check_username_endpoint_validates_alphanumeric_and_uniqueness(): void
    {
        $user = User::factory()->create();

        // Valid username
        $response = $this->actingAs($user)->getJson(route('onboarding.check-username', ['username' => 'scarface_99']));
        $response->assertStatus(200)
            ->assertJson(['available' => true]);

        // Invalid Unicode / weird characters
        $response = $this->actingAs($user)->getJson(route('onboarding.check-username', ['username' => '𝕯𝖆𝖓𝖎𝖊𝖑']));
        $response->assertStatus(200)
            ->assertJson(['available' => false]);

        // Spaces not allowed
        $response = $this->actingAs($user)->getJson(route('onboarding.check-username', ['username' => 'henk de beuker']));
        $response->assertStatus(200)
            ->assertJson(['available' => false]);

        // Too short (< 3 chars)
        $response = $this->actingAs($user)->getJson(route('onboarding.check-username', ['username' => 'ab']));
        $response->assertStatus(200)
            ->assertJson(['available' => false]);
    }

    public function test_onboarding_blocks_under_16_years_old(): void
    {
        $user = User::factory()->notOnboarded()->create();

        $underageBirthdate = now()->subYears(15)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('onboarding.complete'), [
            'username' => 'nieuweling123',
            'display_name' => 'Nieuweling',
            'birthdate' => $underageBirthdate,
            'gender' => 'male',
            'specialty' => 'hossel',
            'profile_color' => 'purple',
        ]);

        $response->assertSessionHasErrors('birthdate');
        $user->refresh();
        $this->assertFalse((bool) $user->is_onboarded);
    }

    public function test_onboarding_completes_for_valid_16_plus_user(): void
    {
        $user = User::factory()->notOnboarded()->create();

        $validBirthdate = now()->subYears(18)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('onboarding.complete'), [
            'username' => 'bajesklant_77',
            'display_name' => 'Bajes Klant 👑',
            'birthdate' => $validBirthdate,
            'avatar' => 'assets/characters/avatar_male_1.jpg',
            'gender' => 'male',
            'bio' => 'Op zoek naar frikandelbroodjes en sigaretten.',
            'specialty' => 'vecht',
            'profile_color' => 'rose',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertTrue((bool) $user->is_onboarded);

        $character = $user->character;
        $this->assertNotNull($character);
        $this->assertEquals('bajesklant_77', $character->username);
        $this->assertEquals('Bajes Klant 👑', $character->display_name);
        $this->assertEquals('rose', $character->profile_color);
        $this->assertTrue((bool) $character->is_onboarded);
    }
}

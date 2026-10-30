<?php

namespace Tests\Feature;

use App\Models\GameSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAndVipV1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_vip_shop_displays_correctly(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('vip.index'));
        $response->assertStatus(200);
        $response->assertSee('VIP Punten Inwisselen');
        $response->assertSee('discord.gg/tbs');
        $response->assertSee('100% Gratis');
        $response->assertSee('5 VIP Punten');
    }

    public function test_staff_admin_panel_accessible_by_staff(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $user->is_staff = true;
        $user->save();

        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Medewerkers Paneel');
        $response->assertSee('Geregistreerd');

        $responsePlayers = $this->get(route('admin.players'));
        $responsePlayers->assertStatus(200);
        $responsePlayers->assertSee('PATIËNT');

        $responseGameplay = $this->get(route('admin.gameplay'));
        $responseGameplay->assertStatus(200);
        $responseGameplay->assertSee('Winkansen');

        $responseChests = $this->get(route('admin.chests'));
        $responseChests->assertStatus(200);
        $responseChests->assertSee('Kisten');

        $responseVault = $this->get(route('admin.vault'));
        $responseVault->assertStatus(200);
        $responseVault->assertSee('Centrale Kliniekkluiskraak');
    }

    public function test_staff_can_update_gameplay_win_rates(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $user->is_staff = true;
        $user->save();

        $this->actingAs($user);

        $response = $this->post(route('admin.gameplay.update'), [
            'win_rate_casino' => 55,
            'win_rate_theft' => 70,
            'win_rate_hustle' => 75,
            'win_rate_pickpocket' => 50,
            'win_rate_lockers' => 45,
            'attack_energy_cost' => 20,
            'daily_attacks_limit' => 100,
            'vip_hourly_points' => 5,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(55, GameSetting::get('win_rate_casino'));
    }

    public function test_account_settings_username_limit_enforced(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $this->actingAs($user);

        // 1. Initial name change succeeds
        $response = $this->post(route('settings.username.update'), [
            'name' => 'NieuweBaas',
        ]);
        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals('NieuweBaas', $character->name);
        $this->assertNotNull($character->username_changed_at);

        // 2. Second immediate change is blocked (6-month rule)
        $secondResponse = $this->post(route('settings.username.update'), [
            'name' => 'SnelleWissel',
        ]);
        $secondResponse->assertSessionHas('error');
        $this->assertStringContainsString('max 1x per 6 maanden', session('error'));
    }

    public function test_faq_page_loads_with_search(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('faq'));
        $response->assertStatus(200);
        $response->assertSee('Kliniek Kennisbank');
        $response->assertSee('Wat is het doel van TBS Kliniek?');
    }
}

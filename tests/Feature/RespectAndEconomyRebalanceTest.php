<?php

namespace Tests\Feature;

use App\Models\Gang;
use App\Models\User;
use App\Services\TheftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RespectAndEconomyRebalanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_character_tracks_total_and_daily_respect(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $character->update([
            'respect' => 1500,
            'today_respect' => 0.00,
            'today_respect_date' => Carbon::now()->toDateString(),
        ]);

        $this->assertEquals('1.50K', $character->formatted_respect);
        $this->assertEquals('0.00', $character->formatted_today_respect);

        // Add 68.3 respect
        $character->addRespect(68.30);
        $character->save();

        $character->refresh();
        $this->assertEquals(1568, $character->respect);
        $this->assertEquals(68.30, $character->today_respect_score);
        $this->assertEquals('68.30', $character->formatted_today_respect);

        // Simulate day rollover
        $character->today_respect_date = Carbon::now()->subDays(1)->toDateString();
        $character->save();
        $character->refresh();

        // Yesterday's date means today_respect_score reads 0
        $this->assertEquals(0.00, $character->today_respect_score);
        $this->assertEquals('0.00', $character->formatted_today_respect);

        // Earning respect on new day resets today_respect to the new earnings
        $character->addRespect(25.00);
        $character->save();
        $character->refresh();

        $this->assertEquals(25.00, $character->today_respect_score);
        $this->assertEquals('25.00', $character->formatted_today_respect);
        $this->assertEquals(Carbon::now()->toDateString(), $character->today_respect_date);
    }

    public function test_navbar_displays_respect_and_vandaag(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $character->update([
            'respect' => 6730,
            'today_respect' => 68.30,
            'today_respect_date' => Carbon::now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Respect:');
        $response->assertSee('6.73K');
        $response->assertSee('Vandaag:');
        $response->assertSee('68.30');
    }

    public function test_theft_tiers_provide_increased_cash_and_award_respect(): void
    {
        $tiers = TheftService::TIERS;

        $this->assertGreaterThanOrEqual(150, $tiers['pocket']['min_cash']);
        $this->assertGreaterThanOrEqual(10000, $tiers['canteen_vault']['min_cash']);
        $this->assertGreaterThanOrEqual(2, $tiers['pocket']['respect']);
        $this->assertGreaterThanOrEqual(20, $tiers['canteen_vault']['respect']);
    }

    public function test_hustle_payouts_are_rebalanced(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('hustle.index'));
        $response->assertStatus(200);
        $response->assertSee('Bewaker afleiden');
        $response->assertSee('€350');
    }

    public function test_smokkelkiosk_costs_50k_and_upgrades_gang_wage(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        $gang = Gang::create([
            'name' => 'Syndicaat 50K',
            'tag' => 'S50K',
            'specialty' => 'Kantine Controle',
            'treasury' => 100000,
            'bling_shops' => 0,
            'leader_character_id' => $character->id,
        ]);

        $character->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
            'cash' => 20000,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('gangs.shop-buy'), [
            'item_type' => 'bling_shops',
            'quantity' => 1,
        ]);

        $response->assertSessionHas('success');
        $gang->refresh();
        $this->assertEquals(1, $gang->bling_shops);
        $this->assertEquals(50000, $gang->treasury); // 100.000 - 50.000
    }
}

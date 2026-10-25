<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\DailyBattle;
use App\Models\DailyBattleScore;
use App\Models\User;
use App\Services\DailyBattleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyBattleAndXxlCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_battle_page_loads_with_all_categories_and_countdown(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('battle.index'));
        $response->assertStatus(200);
        $response->assertSee('DE 24 UUR');
        $response->assertSee('MEESTE RESPECT');
        $response->assertSee('MEESTE HOSSELS');
        $response->assertSee('BETALINGEN');
        $response->assertSee('PATIËNTEN UITGEBROKEN');
        $response->assertSee('DRUGS GEMAAKT');
        $response->assertSee('DRUGS VERKOCHT');
        $response->assertSee('MEESTE MOORDEN');
        $response->assertSee('XXL Koffers');
    }

    public function test_recording_battle_scores_and_category_details_endpoint(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $this->actingAs($user);

        /** @var DailyBattleService $battleService */
        $battleService = app(DailyBattleService::class);
        $battleService->record($char, 'slay', 45);
        $battleService->record($char, 'hustles', 12);
        $battleService->record($char, 'payments', 15000);

        $score = DailyBattleScore::where('battle_date', Carbon::today()->toDateString())
            ->where('character_id', $char->id)
            ->first();

        $this->assertNotNull($score);
        $this->assertEquals(45, $score->score_slay);
        $this->assertEquals(12, $score->score_hustles);
        $this->assertEquals(15000, $score->score_payments);

        // Test category details JSON endpoint
        $detailsResponse = $this->getJson(route('battle.category', 'slay'));
        $detailsResponse->assertStatus(200);
        $detailsResponse->assertJsonStructure(['category', 'scores']);
    }

    public function test_automatic_battle_settle_awards_prizes(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'stars' => 10,
            'xxl_cases' => 0,
            'masks' => 0,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();
        $battle = DailyBattle::create([
            'battle_date' => $yesterday,
            'title' => 'Gisteren Battle 🏆',
            'is_settled' => false,
        ]);

        // Place our character as #1 in payments (reward is 3 XXL koffers)
        DailyBattleScore::create([
            'battle_date' => $yesterday,
            'character_id' => $char->id,
            'score_payments' => 999999,
            'score_slay' => 500, // #1 in slay gives 250 stars
        ]);

        /** @var DailyBattleService $battleService */
        $battleService = app(DailyBattleService::class);
        $battleService->checkAndSettle();

        $battle->refresh();
        $this->assertTrue($battle->is_settled);
        $this->assertNotNull($battle->settled_at);

        $char->refresh();
        // #1 in payments gave 3 XXL koffers
        $this->assertGreaterThanOrEqual(3, $char->xxl_cases);
        // #1 in slay gave 100 stars (max 100 sterren)
        $this->assertGreaterThanOrEqual(110, $char->stars);
    }

    public function test_artisan_command_battle_settle(): void
    {
        $this->artisan('battle:settle')
            ->expectsOutput('Controleren op onafgeronde 24 Uur Battles...')
            ->expectsOutput('24 Uur Battles succesvol afgehandeld en prijzen toegekend!')
            ->assertSuccessful();
    }

    public function test_buy_and_open_xxl_koffer(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'stars' => 120,
            'xxl_cases' => 0,
            'bank' => 1000,
            'masks' => 2,
        ]);

        $this->actingAs($user);

        // 1. Buy XXL koffer for 100 stars
        $buyResponse = $this->post(route('packages.buy-xxl'));
        $buyResponse->assertSessionHas('success');

        $char->refresh();
        $this->assertEquals(1, $char->xxl_cases);
        $this->assertEquals(20, $char->stars); // 120 - 100

        // 2. Open the XXL koffer for huge prizes
        $openResponse = $this->post(route('packages.open-xxl'));
        $openResponse->assertSessionHas('success');

        $char->refresh();
        $this->assertEquals(0, $char->xxl_cases);
        $this->assertGreaterThan(25000, $char->bank);
        $this->assertGreaterThan(20, $char->stars); // gained 10-25 stars
        $this->assertGreaterThan(2, $char->masks); // gained 5-12 masks
    }

    public function test_open_regular_bags_and_multiple_xxl_cases(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'unopened_bags' => 10,
            'xxl_cases' => 5,
            'bank' => 0,
        ]);

        $this->actingAs($user);

        // Open 5 regular bags in a row
        for ($i = 0; $i < 5; $i++) {
            $resp = $this->post(route('packages.open-bag'));
            $resp->assertSessionHas('success');
        }

        $char->refresh();
        $this->assertEquals(5, $char->unopened_bags);
        $this->assertGreaterThan(0, $char->bank);

        // Crack 3 XXL cases in a row
        for ($i = 0; $i < 3; $i++) {
            $resp = $this->post(route('packages.open-xxl'));
            $resp->assertSessionHas('success');
        }

        $char->refresh();
        $this->assertEquals(2, $char->xxl_cases);
    }
}

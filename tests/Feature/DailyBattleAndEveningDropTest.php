<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\DailyBattleScore;
use App\Models\GameMessage;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\DailyBattleService;
use App\Services\EveningVaultDropService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyBattleAndEveningDropTest extends TestCase
{
    use RefreshDatabase;

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
            'last_active_at' => now(),
        ], $attrs));
    }

    public function test_daily_battle_does_not_seed_mock_atmosphere_scores(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        // Before viewing, no battle scores exist
        $this->assertEquals(0, DailyBattleScore::whereDate('battle_date', Carbon::today())->count());

        $response = $this->actingAs($user)->get(route('battle.index'));
        $response->assertStatus(200);

        // Still 0 mock scores! It must not fake any top 3 scores
        $this->assertEquals(0, DailyBattleScore::whereDate('battle_date', Carbon::today())->count());

        // Perform an action: record 5 hustles
        app(DailyBattleService::class)->record($char, 'hustles', 5);

        // Now exactly 1 real record exists
        $this->assertEquals(1, DailyBattleScore::whereDate('battle_date', Carbon::today())->count());
        $score = DailyBattleScore::where('character_id', $char->id)->first();
        $this->assertEquals(5, $score->score_hustles);
    }

    public function test_evening_drop_window_detection(): void
    {
        /** @var EveningVaultDropService $dropService */
        $dropService = app(EveningVaultDropService::class);
        $tz = 'Europe/Amsterdam';

        // 21:59:59 -> inactive
        $this->assertFalse($dropService->isDropWindowActive(Carbon::parse('2026-10-13 21:59:59', $tz)));

        // 22:00:00 -> active
        $this->assertTrue($dropService->isDropWindowActive(Carbon::parse('2026-10-13 22:00:00', $tz)));

        // 22:15:00 -> active
        $this->assertTrue($dropService->isDropWindowActive(Carbon::parse('2026-10-13 22:15:00', $tz)));

        // 22:30:00 -> active
        $this->assertTrue($dropService->isDropWindowActive(Carbon::parse('2026-10-13 22:30:00', $tz)));

        // 22:30:01 -> inactive
        $this->assertFalse($dropService->isDropWindowActive(Carbon::parse('2026-10-13 22:30:01', $tz)));
    }

    public function test_evening_vault_drop_awards_exactly_three_codes_and_game_message(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        /** @var EveningVaultDropService $dropService */
        $dropService = app(EveningVaultDropService::class);

        $codes = $dropService->award($char, '2026-10-13');

        $this->assertCount(3, $codes);
        $this->assertEquals(3, VaultCode::where('character_id', $char->id)->count());

        // Game message received
        $msg = GameMessage::where('recipient_id', $char->id)->where('type', 'reward')->first();
        $this->assertNotNull($msg);
        $this->assertStringContainsString('22:00 Avondronde', $msg->subject);

        // Cannot be awarded twice on the same day
        $secondAttempt = $dropService->award($char, '2026-10-13');
        $this->assertEmpty($secondAttempt);
        $this->assertEquals(3, VaultCode::where('character_id', $char->id)->count());
    }

    public function test_artisan_command_awards_online_players_at_2200(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user, ['last_active_at' => now()]);

        $this->artisan('vault:evening-drop --force')
            ->assertSuccessful();

        $this->assertEquals(3, VaultCode::where('character_id', $char->id)->count());
    }
}

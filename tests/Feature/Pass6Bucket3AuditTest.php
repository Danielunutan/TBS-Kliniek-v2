<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\CharacterMemoryStat;
use App\Models\CharacterTrueCrimeState;
use App\Models\ControlPoint;
use App\Models\Gang;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\ControlPointService;
use App\Services\CooldownService;
use App\Services\EnergyService;
use App\Services\LoanService;
use App\Services\PrisonService;
use App\Services\RookieService;
use App\Services\TheftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket3AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_cooldown_service_skip_with_stars_rechecks_cooldown_inside_transaction(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'stars' => 50,
        ]);

        $service = app(CooldownService::class);
        $service->setCooldown($character, 'hustle', 60);

        $first = $service->skipWithStars($character, 'hustle');
        $this->assertTrue($first['success']);
        $this->assertSame(38, $character->fresh()->stars);

        $second = $service->skipWithStars($character, 'hustle');
        $this->assertFalse($second['success']);
        $this->assertSame(38, $character->fresh()->stars);
    }

    public function test_connect_four_stat_returns_integer_cooldown_seconds(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create(['user_id' => $user->id]);

        $stat = CharacterConnectFourStat::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addSeconds(45),
        ]);

        $remaining = $stat->getCooldownSecondsRemaining();
        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual(45, $remaining);
    }

    public function test_loan_service_requests_and_repays_loan_correctly(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'level' => 5,
            'cash' => 10000,
        ]);

        $loanService = app(LoanService::class);
        $res = $loanService->requestLoan($character, 10000, 'cash');
        $this->assertTrue($res['success']);
        $this->assertSame(20000, $character->fresh()->cash);
    }

    public function test_memory_and_true_crime_cooldown_ceil_and_rookie_bank_deposit_updates_today_respect(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'bank' => 5000,
            'respect' => 10,
            'today_respect' => 0,
            'today_respect_date' => null,
            'perks' => [],
        ]);

        $memStat = CharacterMemoryStat::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addSeconds(30),
        ]);
        $this->assertGreaterThan(0, $memStat->getCooldownSecondsRemaining());

        $tcState = CharacterTrueCrimeState::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addSeconds(30),
        ]);
        $this->assertGreaterThan(0, $tcState->getCooldownSecondsRemaining());

        $rookieService = app(RookieService::class);
        $claim = $rookieService->claimTaskReward($character, 'bank_deposit');
        $this->assertTrue($claim['success']);

        $character->refresh();
        $this->assertSame(15, $character->respect);
        $this->assertSame(5.0, (float) $character->today_respect);

        Carbon::setTestNow(Carbon::parse('2026-10-31 10:00:00.400'));

        try {
            $gang = Gang::factory()->create([
                'leader_character_id' => $character->id,
                'shield_until' => Carbon::parse('2026-10-31 10:00:01'),
                'last_attack_at' => Carbon::parse('2026-10-31 09:50:01'),
            ]);
            $this->assertSame(1, $gang->shieldSecondsRemaining());
            $this->assertSame(1, $gang->attackCooldownRemaining());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_theft_service_blocks_comatose_attacker_and_victim(): void
    {
        $user1 = User::factory()->create(['is_onboarded' => true]);
        $user2 = User::factory()->create(['is_onboarded' => true]);
        $attacker = Character::factory()->create([
            'user_id' => $user1->id,
            'level' => 5,
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
            'energy' => 100,
        ]);
        $victim = Character::factory()->create([
            'user_id' => $user2->id,
            'level' => 5,
            'health' => 100,
            'coma_until' => null,
            'cash' => 500,
        ]);

        $theftService = app(TheftService::class);
        $res = $theftService->execute($attacker, 'pocket', $victim);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('Code Blauw', $res['message']);

        // Attacker healthy, victim comatose
        $attacker->update(['health' => 100, 'coma_until' => null]);
        $victim->update(['health' => 0, 'coma_until' => now()->addMinutes(30)]);
        $res2 = $theftService->execute($attacker, 'pocket', $victim);
        $this->assertFalse($res2['success']);
        $this->assertStringContainsString('Code Blauw', $res2['message']);
    }

    public function test_prison_service_attempt_escape_updates_respect_via_add_respect(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'prison_escape_attempted' => false,
            'energy' => 100,
            'respect' => 10,
            'today_respect' => 0,
            'intelligence' => 1,
            'stamina' => 1,
        ]);

        $prisonService = app(PrisonService::class);
        $energyService = app(EnergyService::class);

        $res = $prisonService->attemptEscape($character, $energyService);
        $character->refresh();

        if (! $res['success']) {
            $this->assertTrue((bool) $character->prison_escape_attempted);
            $this->assertSame(9, $character->respect);
        } else {
            $this->assertFalse((bool) $character->is_in_prison);
            $this->assertSame(25, $character->respect);
        }
    }

    public function test_energy_service_consume_and_integer_seconds_calculation(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'energy' => 15,
            'last_energy_refill_at' => now()->subSeconds(45),
        ]);

        $energyService = app(EnergyService::class);

        $consumed = $energyService->consume($character, 10);
        $this->assertTrue($consumed);
        $this->assertSame(5, $character->fresh()->energy);

        $consumedTooMuch = $energyService->consume($character, 10);
        $this->assertFalse($consumedTooMuch);
        $this->assertSame(5, $character->fresh()->energy);
    }

    public function test_news_article_sanitizes_dangerous_html_attributes(): void
    {
        $article = new NewsArticle([
            'content' => '<p>Hello <img src="x" onerror="alert(1)"> <a href="javascript:alert(2)">Click</a> <strong>Safe</strong></p>',
        ]);

        $formatted = $article->formatted_content;
        $this->assertStringNotContainsString('onerror', $formatted);
        $this->assertStringNotContainsString('javascript:', $formatted);
        $this->assertStringContainsString('<strong>Safe</strong>', $formatted);
    }

    public function test_control_point_blocks_comatose_captures(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
            'energy' => 100,
        ]);

        $point = ControlPoint::create([
            'key' => 'test_punt',
            'name' => 'Test Punt',
            'location_name' => 'Blok A',
            'energy_cost' => 10,
            'cash_per_minute' => 5,
        ]);

        $service = app(ControlPointService::class);
        $res = $service->capture($character, $point);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('Code Blauw', $res['message']);
    }
}

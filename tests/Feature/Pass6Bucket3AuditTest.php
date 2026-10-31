<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\CharacterMemoryStat;
use App\Models\CharacterTrueCrimeState;
use App\Models\Gang;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\LoanService;
use App\Services\RookieService;
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
}

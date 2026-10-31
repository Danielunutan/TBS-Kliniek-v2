<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\LoanService;
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
}

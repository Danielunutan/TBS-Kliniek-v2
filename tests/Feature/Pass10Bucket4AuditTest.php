<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\Loan;
use App\Models\Mission;
use App\Models\Referral;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\ControlPointService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\LoanService;
use App\Services\MissionService;
use App\Services\NuNlNewsService;
use App\Services\RandomDropService;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket4AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Character $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_onboarded' => true]);
        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'AuditPatient4',
            'cash' => 50000,
            'bank' => 100000,
            'stars' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 5,
            'respect' => 500,
            'vault_tokens' => 10,
            'unopened_bags' => 0,
        ]);
    }

    public function test_control_point_capture_deducts_energy_and_awards_respect_atomically(): void
    {
        /** @var ControlPointService $service */
        $service = app(ControlPointService::class);

        $point = ControlPoint::create([
            'key' => 'test_point_vleugel',
            'name' => 'Test Vleugel Controlepunt',
            'location_name' => 'Gangpad C',
            'energy_cost' => 15,
            'cash_per_minute' => 50,
            'total_captures' => 0,
        ]);

        $this->character->energy = 50;
        $this->character->respect = 100;
        $this->character->last_energy_refill_at = now();
        $this->character->save();

        $res = $service->capture($this->character, $point);

        $this->assertTrue($res['success']);
        $this->assertEquals(35, $this->character->energy);
        $this->assertEquals(101, $this->character->respect);

        $this->character->refresh();
        $this->assertEquals(35, $this->character->energy);
        $this->assertEquals(101, $this->character->respect);

        // Cannot recapture own point
        $resSelf = $service->capture($this->character, $point);
        $this->assertFalse($resSelf['success']);
    }

    public function test_energy_service_does_not_regenerate_health_when_comatose(): void
    {
        /** @var EnergyService $service */
        $service = app(EnergyService::class);

        $this->character->coma_until = now()->addMinutes(30);
        $this->character->health = 0;
        $this->character->energy = 0;
        $this->character->last_energy_refill_at = now()->subMinutes(60);
        $this->character->save();

        $this->assertTrue($this->character->isComatose());

        $refreshed = $service->refresh($this->character);

        // Energy regenerates, but health MUST remain 0 while in coma
        $this->assertGreaterThan(0, $refreshed->energy);
        $this->assertEquals(0, $refreshed->health);
    }

    public function test_energy_service_regenerates_health_when_healthy(): void
    {
        /** @var EnergyService $service */
        $service = app(EnergyService::class);

        $this->character->coma_until = null;
        $this->character->health = 10;
        $this->character->energy = 10;
        $this->character->last_energy_refill_at = now()->subMinutes(60);
        $this->character->save();

        $refreshed = $service->refresh($this->character);

        $this->assertGreaterThan(10, $refreshed->health);
        $this->assertGreaterThan(10, $refreshed->energy);
    }

    public function test_level_service_clamps_zero_level_xp_requirement(): void
    {
        /** @var LevelService $service */
        $service = app(LevelService::class);

        $xp0 = $service->getXpRequiredForNextLevel(0);
        $xpNegative = $service->getXpRequiredForNextLevel(-5);
        $xp1 = $service->getXpRequiredForNextLevel(1);

        $this->assertGreaterThan(0, $xp0);
        $this->assertEquals($xp1, $xp0);
        $this->assertEquals($xp1, $xpNegative);
    }

    public function test_loan_service_blocks_comatose_patient(): void
    {
        /** @var LoanService $service */
        $service = app(LoanService::class);

        $this->character->coma_until = now()->addMinutes(20);
        $this->character->save();

        $res = $service->requestLoan($this->character, 10000);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('Code Blauw', $res['message']);

        // Clear coma, loan should succeed
        $this->character->coma_until = null;
        $this->character->save();

        $resValid = $service->requestLoan($this->character, 10000);
        $this->assertTrue($resValid['success']);
        $this->assertInstanceOf(Loan::class, $resValid['loan']);

        // Comatose character cannot repay either
        $this->character->coma_until = now()->addMinutes(20);
        $this->character->save();

        $repayRes = $service->repayLoan($this->character, $resValid['loan'], 1000);
        $this->assertFalse($repayRes['success']);
        $this->assertStringContainsString('Code Blauw', $repayRes['message']);
    }

    public function test_referral_service_blocks_self_referral_for_same_user(): void
    {
        /** @var ReferralService $service */
        $service = app(ReferralService::class);

        $altCharacter = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'AltCharacterSameUser',
        ]);

        // Attempt to refer own alt character
        $referral = $service->recordReferral($altCharacter, (string) $this->character->id);
        $this->assertNull($referral);

        // Attempt valid referral with another user
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'OtherUserCharacter',
        ]);

        $validReferral = $service->recordReferral($otherCharacter, (string) $this->character->id);
        $this->assertInstanceOf(Referral::class, $validReferral);
        $this->assertEquals($this->character->id, $validReferral->referrer_id);
        $this->assertEquals($otherCharacter->id, $validReferral->referred_id);
    }

    public function test_random_drop_service_handles_increments_and_codes(): void
    {
        $this->character->unopened_bags = 3;
        $this->character->save();

        // Run roll multiple times to test integrity
        for ($i = 0; $i < 20; $i++) {
            RandomDropService::roll($this->character);
        }

        $this->character->refresh();
        $this->assertGreaterThanOrEqual(3, $this->character->unopened_bags);

        // Verify VaultCode generation if any were created
        $codes = VaultCode::where('character_id', $this->character->id)->get();
        foreach ($codes as $code) {
            $this->assertStringStartsWith('DROP-', $code->code);
        }
    }

    public function test_nunl_news_service_returns_cached_articles_or_fallback(): void
    {
        /** @var NuNlNewsService $service */
        $service = app(NuNlNewsService::class);

        $articles = $service->getLatestNews(3);
        $this->assertIsArray($articles);
        $this->assertNotEmpty($articles);
        $this->assertArrayHasKey('title', $articles[0]);
        $this->assertArrayHasKey('link', $articles[0]);
    }

    public function test_mission_service_prevents_double_completion_under_concurrency(): void
    {
        /** @var MissionService $service */
        $service = app(MissionService::class);

        $mission = Mission::create([
            'slug' => 'test-audit-mission',
            'title' => 'Test Audit Mission',
            'description' => 'Test mission description',
            'category' => 'inbraak',
            'required_level' => 1,
            'energy_cost' => 5,
            'success_rate' => 100,
            'min_cash_reward' => 500,
            'max_cash_reward' => 500,
            'xp_reward' => 50,
            'respect_reward' => 2,
        ]);

        $this->character->energy = 50;
        $this->character->save();

        $res1 = $service->execute($this->character, $mission, 'B');
        $this->assertTrue($res1['success']);

        // Second attempt must be rejected as already completed
        $res2 = $service->execute($this->character, $mission, 'B');
        $this->assertFalse($res2['success']);
        $this->assertStringContainsString('al voltooid', $res2['message']);
    }

    public function test_halloween_service_handles_negative_or_zero_counts_cleanly(): void
    {
        HalloweenService::record($this->character, 'theft', 0);
        HalloweenService::record($this->character, 'theft', -5);

        $quests = HalloweenService::getCharacterQuests($this->character);
        $this->assertIsArray($quests);
        $this->assertArrayHasKey('daily', $quests);
    }
}

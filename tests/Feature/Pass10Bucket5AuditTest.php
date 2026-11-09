<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\RookieService;
use App\Services\TheftService;
use App\Services\TrainingService;
use App\Services\TurnstileService;
use App\Services\VpnDetectionService;
use App\Services\WeatherService;
use App\Support\GangPropertyCatalog;
use App\Support\MissionCatalog;
use App\Support\MissionScenarioCatalog;
use App\Support\ProfileStickerCatalog;
use App\Support\TreatmentCaseCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Pass10Bucket5AuditTest extends TestCase
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
            'name' => 'AuditPatient5',
            'cash' => 50000,
            'bank' => 100000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 1,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'intelligence' => 10,
            'charisma' => 10,
            'discipline' => 10,
            'respect' => 100,
        ]);
    }

    public function test_training_service_rejects_when_character_is_imprisoned(): void
    {
        $this->character->prison_until = now()->addMinutes(10);
        $this->character->is_in_prison = true;
        $this->character->save();

        $service = app(TrainingService::class);
        $result = $service->train($this->character, 'strength');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('isoleercel', $result['message']);
    }

    public function test_training_service_rejects_when_character_is_comatose(): void
    {
        $this->character->coma_until = now()->addHours(2);
        $this->character->save();

        $service = app(TrainingService::class);
        $result = $service->train($this->character, 'strength');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Code Blauw', $result['message']);
    }

    public function test_training_service_rejects_when_insufficient_cash(): void
    {
        $this->character->cash = 0;
        $this->character->save();

        $service = app(TrainingService::class);
        $result = $service->train($this->character, 'strength');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('nodig om Kracht te trainen', $result['message']);
    }

    public function test_training_service_rejects_when_insufficient_energy(): void
    {
        $this->character->last_energy_refill_at = now();
        $this->character->energy = 5;
        $this->character->save();

        $service = app(TrainingService::class);
        $result = $service->train($this->character, 'strength');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Niet genoeg energie', $result['message']);
    }

    public function test_training_service_rejects_when_stat_already_at_level_cap(): void
    {
        $maxStat = $this->character->getMaxStatForLevel();
        $this->character->strength = $maxStat;
        $this->character->save();

        $service = app(TrainingService::class);
        $result = $service->train($this->character, 'strength', 'stat');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('maximum voor Kracht bereikt', $result['message']);
    }

    public function test_training_service_executes_stat_training_under_transaction_lock(): void
    {
        $initialStrength = (int) $this->character->strength;
        $initialCash = (int) $this->character->cash;
        $initialEnergy = (int) $this->character->energy;

        $service = app(TrainingService::class);
        $result = $service->train($this->character, 'strength', 'stat');

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['stat_gain']);
        $this->assertSame($initialStrength + 1, $result['new_value']);

        $this->character->refresh();
        $this->assertSame($initialStrength + 1, (int) $this->character->strength);
        $this->assertLessThan($initialCash, (int) $this->character->cash);
        $this->assertSame($initialEnergy - 10, (int) $this->character->energy);

        $this->assertDatabaseHas('training_logs', [
            'character_id' => $this->character->id,
            'stat' => 'strength',
            'stat_gain' => 1,
        ]);
    }

    public function test_training_service_executes_level_focus_training(): void
    {
        $initialXp = (int) $this->character->xp;

        $service = app(TrainingService::class);
        $result = $service->train($this->character, 'strength', 'level');

        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['stat_gain']);
        $this->assertSame(60, $result['xp_gain']);

        $this->character->refresh();
        $this->assertSame($initialXp + 60, (int) $this->character->xp);
    }

    public function test_vpn_detection_service_bypasses_private_and_reserved_ips(): void
    {
        $service = app(VpnDetectionService::class);

        $localIps = [
            '127.0.0.1',
            '::1',
            'localhost',
            '10.0.0.1',
            '192.168.1.100',
            '172.16.0.1',
            '172.31.255.255',
            '169.254.1.1',
            'not-a-valid-ip',
        ];

        foreach ($localIps as $ip) {
            $result = $service->check($ip);
            $this->assertFalse($result['is_vpn'], "IP {$ip} should not be flagged as VPN");
            $this->assertNull($result['provider']);
            $this->assertSame('NL', $result['country']);
        }
    }

    public function test_vpn_detection_service_handles_external_vpn_api_response(): void
    {
        Http::fake([
            'https://proxycheck.io/v2/*' => Http::response([
                '8.8.8.8' => [
                    'proxy' => 'yes',
                    'type' => 'VPN',
                    'provider' => 'NordVPN / Tefincom',
                    'isocode' => 'NL',
                ],
            ], 200),
        ]);

        $service = app(VpnDetectionService::class);
        $result = $service->check('8.8.8.8');

        $this->assertTrue($result['is_vpn']);
        $this->assertSame('NordVPN / Tefincom', $result['provider']);
    }

    public function test_turnstile_service_verifies_safely(): void
    {
        $service = app(TurnstileService::class);

        // A missing secret cannot establish that the response is genuine.
        config(['services.turnstile.secret_key' => '']);
        $this->assertFalse($service->verify('dummy-token'));

        // When secret is configured, empty token fails
        config(['services.turnstile.secret_key' => 'test-secret']);
        $this->assertFalse($service->verify(''));

        // When mocked HTTP endpoint returns success
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true], 200),
        ]);
        $this->assertTrue($service->verify('valid-token'));
    }

    public function test_gang_property_catalog_functions_and_rotations(): void
    {
        $allProps = GangPropertyCatalog::all();
        $this->assertNotEmpty($allProps);
        $this->assertArrayHasKey('kleine_loods', $allProps);

        $allNpcs = GangPropertyCatalog::npcs();
        $this->assertNotEmpty($allNpcs);

        $rotation = GangPropertyCatalog::getRotatingProperties();
        $this->assertArrayHasKey('slot', $rotation);
        $this->assertArrayHasKey('listings', $rotation);
        $this->assertNotEmpty($rotation['listings']);
        $this->assertArrayHasKey('token', $rotation['listings'][0]);
        $this->assertNotEmpty($rotation['listings'][0]['token']);

        $npcRotation = GangPropertyCatalog::getRotatingNpcs();
        $this->assertArrayHasKey('slot', $npcRotation);
        $this->assertArrayHasKey('listings', $npcRotation);
        $this->assertNotEmpty($npcRotation['listings']);
        $this->assertArrayHasKey('token', $npcRotation['listings'][0]);
    }

    public function test_treatment_case_catalog_daily_cases(): void
    {
        $case1 = TreatmentCaseCatalog::getCaseForDay(1);
        $this->assertSame(1, $case1['day_of_year']);
        $this->assertNotEmpty($case1['title']);
        $this->assertNotEmpty($case1['case_code']);
        $this->assertCount(3, $case1['questions']);

        $case365 = TreatmentCaseCatalog::getCaseForDay(365);
        $this->assertSame(365, $case365['day_of_year']);
        $this->assertNotEmpty($case365['doctor']['name']);
    }

    public function test_profile_sticker_catalog_stickers(): void
    {
        $stickers = ProfileStickerCatalog::stickers();
        $this->assertNotEmpty($stickers);
        $this->assertArrayHasKey('skull', $stickers);
        $this->assertArrayHasKey('badge_class', $stickers['skull']);

        $all = ProfileStickerCatalog::all();
        $this->assertSame($stickers, $all);
    }

    public function test_mission_and_scenario_catalogs(): void
    {
        $missions = MissionCatalog::all();
        $this->assertNotEmpty($missions);

        $scenarios = MissionScenarioCatalog::scenarios();
        $this->assertNotEmpty($scenarios);
    }

    public function test_theft_service_rejects_comatose_and_imprisoned(): void
    {
        $service = app(TheftService::class);

        // Comatose
        $this->character->coma_until = now()->addHours(2);
        $this->character->save();
        $result = $service->execute($this->character, 'pocket');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Code Blauw', $result['message']);

        // Imprisoned
        $this->character->coma_until = null;
        $this->character->prison_until = now()->addMinutes(10);
        $this->character->is_in_prison = true;
        $this->character->save();
        $result = $service->execute($this->character, 'pocket');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('slot en grendel', $result['message']);
    }

    public function test_theft_service_pvp_guards_and_execution(): void
    {
        $service = app(TheftService::class);

        $victimUser = User::factory()->create(['is_onboarded' => true]);
        $victim = Character::factory()->create([
            'user_id' => $victimUser->id,
            'name' => 'VictimPatient',
            'cash' => 10000,
            'bank' => 20000,
            'health' => 100,
        ]);

        // Cannot steal from oneself
        $selfResult = $service->stealFromInmate($this->character, $this->character);
        $this->assertFalse($selfResult['success']);
        $this->assertStringContainsString('eigen zakken', $selfResult['message']);

        // Cannot steal from comatose victim
        $victim->coma_until = now()->addHours(2);
        $victim->save();
        $comaResult = $service->stealFromInmate($this->character, $victim);
        $this->assertFalse($comaResult['success']);
        $this->assertStringContainsString('Code Blauw', $comaResult['message']);

        // Cannot steal from imprisoned victim
        $victim->coma_until = null;
        $victim->prison_until = now()->addMinutes(5);
        $victim->is_in_prison = true;
        $victim->save();
        $jailResult = $service->stealFromInmate($this->character, $victim);
        $this->assertFalse($jailResult['success']);
        $this->assertStringContainsString('isoleercel', $jailResult['message']);
    }

    public function test_rookie_service_quests_and_claiming(): void
    {
        $service = app(RookieService::class);

        $quests = $service->getQuests($this->character);
        $this->assertArrayHasKey('tasks', $quests);
        $this->assertCount(5, $quests['tasks']);

        // Attempting to claim uncompleted task returns failure
        $failClaim = $service->claimTaskReward($this->character, 'first_theft');
        $this->assertFalse($failClaim['success']);
        $this->assertStringContainsString('nog niet behaald', $failClaim['message']);

        // Mark condition met: give 1 theft succeeded
        $this->character->thefts_succeeded = 1;
        $this->character->save();

        $successClaim = $service->claimTaskReward($this->character, 'first_theft');
        $this->assertTrue($successClaim['success']);
        $this->assertStringContainsString('€1.000 contant en 15 XP', $successClaim['reward']);

        // Cannot claim twice
        $doubleClaim = $service->claimTaskReward($this->character, 'first_theft');
        $this->assertFalse($doubleClaim['success']);
        $this->assertStringContainsString('al geclaimd', $doubleClaim['message']);

        // Complete intake tour
        $tourResult = $service->completeTour($this->character);
        $this->assertTrue($tourResult['success']);
        $this->assertStringContainsString('startersbonus', $tourResult['message']);

        $this->character->refresh();
        $this->assertTrue((bool) ($this->character->perks['intake_tour_completed'] ?? false));
    }

    public function test_training_service_prevents_double_spend_when_funds_cover_only_one_session(): void
    {
        $service = app(TrainingService::class);
        $cashCost = (int) max(20, round(15 + (10 * 3.5))); // 50
        $this->character->cash = $cashCost;
        $this->character->energy = 100;
        $this->character->save();

        $first = $service->train($this->character, 'strength', 'stat');
        $this->assertTrue($first['success']);

        $second = $service->train($this->character, 'strength', 'stat');
        $this->assertFalse($second['success']);
        $this->assertStringContainsString('nodig om Kracht te trainen', $second['message']);
    }

    public function test_training_service_stat_capping_at_max_stat(): void
    {
        $service = app(TrainingService::class);
        $maxStat = $this->character->getMaxStatForLevel();
        $this->character->strength = $maxStat - 1;
        $this->character->cash = 10000;
        $this->character->energy = 100;
        $this->character->save();

        // 1st: reaches max
        $first = $service->train($this->character, 'strength', 'stat');
        $this->assertTrue($first['success']);
        $this->assertSame($maxStat, $first['new_value']);

        // 2nd: rejected due to cap
        $second = $service->train($this->character, 'strength', 'stat');
        $this->assertFalse($second['success']);
        $this->assertStringContainsString('maximum voor Kracht bereikt', $second['message']);
    }

    public function test_weather_service_fallback(): void
    {
        $service = app(WeatherService::class);
        $weather = $service->getClinicWeather();

        $this->assertArrayHasKey('temp', $weather);
        $this->assertArrayHasKey('condition', $weather);
        $this->assertArrayHasKey('yard_status', $weather);
        $this->assertArrayHasKey('yard_open', $weather);
    }
}

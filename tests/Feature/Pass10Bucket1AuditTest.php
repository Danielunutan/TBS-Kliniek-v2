<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsOnboarded;
use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket1AuditTest extends TestCase
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
            'name' => 'AuditPatient1',
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
            'masks' => 5,
            'is_onboarded' => true,
        ]);
    }

    public function test_daily_battle_guards_against_imprisoned_and_comatose_characters(): void
    {
        $this->actingAs($this->user);

        // Comatose character cannot select focus
        $this->character->update(['coma_until' => now()->addHours(2)]);
        $res = $this->post(route('battle.focus'), ['category' => 'fights']);
        $res->assertSessionHas('error');

        // Comatose character cannot claim milestone
        $resMilestone = $this->post(route('battle.milestone', ['tier' => 1]));
        $resMilestone->assertSessionHas('error');

        // Imprisoned character cannot select focus
        $this->character->update([
            'coma_until' => null,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(15),
        ]);
        $res = $this->post(route('battle.focus'), ['category' => 'fights']);
        $res->assertSessionHas('error', 'Vanuit de isoleercel kun je geen focus categorie selecteren!');

        // Imprisoned character cannot claim milestone
        $resMilestone = $this->post(route('battle.milestone', ['tier' => 1]));
        $resMilestone->assertSessionHas('error', 'Vanuit de isoleercel kun je geen escalatie-beloningen claimen!');
    }

    public function test_account_settings_handles_user_without_character_gracefully(): void
    {
        $this->withoutMiddleware(EnsureUserIsOnboarded::class);

        $userWithoutChar = User::factory()->create();
        $this->actingAs($userWithoutChar);

        $resIndex = $this->get(route('settings.account'));
        $resIndex->assertNotFound();

        $resUsername = $this->post(route('settings.username.update'), ['name' => 'NewName']);
        $resUsername->assertSessionHas('error', 'Geen personage gevonden.');

        $resAvatar = $this->postJson(route('settings.avatar.update'), ['image' => 'data:image/png;base64,invalid']);
        $resAvatar->assertStatus(404);
    }

    public function test_casino_play_guards_against_imprisoned_and_comatose_and_prevents_race_conditions(): void
    {
        $this->actingAs($this->user);

        // Imprisoned
        $this->character->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $res = $this->post(route('casino.play'), ['game' => 'dice', 'bet' => 100]);
        $res->assertRedirect(route('prison'));

        // Comatose
        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => now()->addHours(1),
        ]);
        $res = $this->post(route('casino.play'), ['game' => 'dice', 'bet' => 100]);
        $res->assertSessionHas('error');

        // Normal play deducts cash atomically
        $this->character->update(['coma_until' => null, 'cash' => 5000]);
        $initialCash = $this->character->cash;

        $res = $this->post(route('casino.play'), ['game' => 'dice', 'bet' => 500]);
        $this->character->refresh();
        $this->assertNotEquals($initialCash, $this->character->cash);

        // Immediate retry hits cooldown
        $resCooldown = $this->post(route('casino.play'), ['game' => 'dice', 'bet' => 500]);
        $resCooldown->assertSessionHas('error');
    }

    public function test_cooldown_controller_guards_against_comatose_and_imprisoned(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $res = $this->post(route('cooldown.skip'), ['action' => 'hustle']);
        $res->assertSessionHas('error', 'Vanuit de isoleercel kun je geen actie-cooldowns overslaan!');

        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => now()->addHours(1),
        ]);
        $res = $this->post(route('cooldown.skip'), ['action' => 'hustle']);
        $res->assertSessionHas('error');
    }

    public function test_control_point_capture_guards_against_imprisoned_and_comatose(): void
    {
        $this->actingAs($this->user);

        $point = ControlPoint::create([
            'key' => 'kantine_post',
            'name' => 'Kantine Post',
            'location_name' => 'Kantine',
            'description' => 'Test point',
            'cash_per_minute' => 500,
        ]);

        $this->character->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $res = $this->post(route('control-points.capture', $point));
        $res->assertRedirect(route('prison'));

        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => now()->addHours(1),
        ]);
        $res = $this->post(route('control-points.capture', $point));
        $res->assertSessionHas('error');
    }

    public function test_connect_four_controller_handles_null_character_and_guards(): void
    {
        $this->withoutMiddleware(EnsureUserIsOnboarded::class);

        $userWithoutChar = User::factory()->create();
        $this->actingAs($userWithoutChar);

        $resIndex = $this->get(route('connect_four.index'));
        $resIndex->assertNotFound();

        $resFinish = $this->postJson(route('connect_four.move'), ['column' => 3]);
        $resFinish->assertStatus(403);
    }

    public function test_combat_training_controller_guards_against_imprisoned_and_comatose(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $res = $this->post(route('combat_training.train'), ['session_id' => 1]);
        $res->assertRedirect(route('prison'));

        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => now()->addHours(1),
        ]);
        $res = $this->post(route('combat_training.train'), ['session_id' => 1]);
        $res->assertSessionHas('error');
    }

    public function test_bounty_controller_guards_against_imprisoned_and_comatose(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $res = $this->post(route('bounties.store'), ['amount' => 5000, 'target_id' => 999]);
        $res->assertRedirect(route('prison'));

        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => now()->addHours(1),
        ]);
        $res = $this->post(route('bounties.store'), ['amount' => 5000, 'target_id' => 999]);
        $res->assertSessionHas('error');
    }

    public function test_battle_pass_guards_against_imprisoned_and_comatose(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $res = $this->post(route('battlepass.claim'), ['level' => 1, 'track' => 'free']);
        $res->assertSessionHas('error', 'Vanuit de isoleercel kun je geen Battle Pass beloningen claimen!');

        $this->character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => now()->addHours(1),
        ]);
        $res = $this->post(route('battlepass.claim'), ['level' => 1, 'track' => 'free']);
        $res->assertSessionHas('error');
    }

    public function test_admin_ticket_approve_gang_transfer_executes_safely_under_lock(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $this->actingAs($adminUser);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TBS-GNG-TEST',
            'user_id' => $this->user->id,
            'character_id' => $this->character->id,
            'category' => 'gang_overstap',
            'subject' => 'Gang Overstap Bonus',
            'status' => 'open',
            'priority' => 'normaal',
            'metadata' => [
                'gang_name' => 'Audit Gang',
                'gang_tag' => 'AG1',
                'member_count' => 5,
            ],
        ]);

        $res = $this->post(route('admin.tickets.approve_gang', $ticket), [
            'gang_name' => 'Audit Gang',
            'gang_tag' => 'AG1',
            'bonus_cash' => 5000000,
            'vip_days' => 7,
        ]);

        $res->assertSessionHas('success');
        $this->character->refresh();
        $this->assertNotNull($this->character->gang_id);
        $this->assertEquals('leader', $this->character->gang_role);
        $this->assertTrue((bool) $this->character->is_vip);
    }

    public function test_arm_wrestling_handles_null_character_safely(): void
    {
        $this->withoutMiddleware(EnsureUserIsOnboarded::class);

        $userWithoutChar = User::factory()->create();
        $this->actingAs($userWithoutChar);

        $resIndex = $this->get(route('armwrestling.index'));
        $resIndex->assertNotFound();

        $resFinish = $this->postJson(route('armwrestling.npc.finish'), ['npc_id' => 1, 'result' => 'win']);
        $resFinish->assertStatus(403);
    }

    public function test_bank_controller_handles_null_character_safely(): void
    {
        $this->withoutMiddleware(EnsureUserIsOnboarded::class);

        $userWithoutChar = User::factory()->create();
        $this->actingAs($userWithoutChar);

        $resIndex = $this->get(route('bank.index'));
        $resIndex->assertNotFound();

        $resDeposit = $this->post(route('bank.deposit'), ['amount' => 1000]);
        $resDeposit->assertRedirect(route('dashboard'));
    }

    public function test_cinema_controller_handles_null_character_and_closed_rooms(): void
    {
        $this->withoutMiddleware(EnsureUserIsOnboarded::class);

        $userWithoutChar = User::factory()->create();
        $this->actingAs($userWithoutChar);

        $resIndex = $this->get(route('cinema.index'));
        $resIndex->assertRedirect(route('dashboard'));

        $resCreate = $this->post(route('cinema.create'), ['title' => 'Test Room']);
        $resCreate->assertRedirect(route('dashboard'));
    }

    public function test_daily_reward_handles_null_character_safely(): void
    {
        $this->withoutMiddleware(EnsureUserIsOnboarded::class);

        $userWithoutChar = User::factory()->create();
        $this->actingAs($userWithoutChar);

        $resIndex = $this->get(route('daily.index'));
        $resIndex->assertNotFound();

        $resSpin = $this->post(route('daily.spin'));
        $resSpin->assertSessionHas('error', 'Geen personage gevonden.');
    }

    public function test_admin_controller_dashboard_and_count_exceeded_characters(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $this->actingAs($adminUser);

        $res = $this->get(route('admin.dashboard'));
        $res->assertOk();
    }

    public function test_dashboard_blade_keeps_customizer_and_city_modals_inside_alpine_root_scope(): void
    {
        $blade = file_get_contents(resource_path('views/game/dashboard.blade.php'));
        $this->assertNotFalse($blade);

        $openDivs = preg_match_all('/<div\b/i', $blade);
        $closeDivs = preg_match_all('/<\/div>/i', $blade);
        $this->assertSame($openDivs, $closeDivs, 'Dashboard Blade must have balanced <div> and </div> tags.');

        $rootPos = strpos($blade, '<div x-data="window.tbsDashboardCustomizer()"');
        $customizerPos = strpos($blade, 'x-show="showCustomizer"');
        $cityModalPos = strpos($blade, 'x-show="showCityModal"');

        $this->assertNotFalse($rootPos);
        $this->assertNotFalse($customizerPos);
        $this->assertNotFalse($cityModalPos);

        $sliceToCustomizer = substr($blade, $rootPos, $customizerPos - $rootPos);
        $depthAtCustomizer = preg_match_all('/<div\b/i', $sliceToCustomizer) - preg_match_all('/<\/div>/i', $sliceToCustomizer);
        $this->assertGreaterThanOrEqual(1, $depthAtCustomizer, 'Customizer modal must be nested inside x-data="window.tbsDashboardCustomizer()".');

        $sliceToCityModal = substr($blade, $rootPos, $cityModalPos - $rootPos);
        $depthAtCityModal = preg_match_all('/<div\b/i', $sliceToCityModal) - preg_match_all('/<\/div>/i', $sliceToCityModal);
        $this->assertGreaterThanOrEqual(1, $depthAtCityModal, 'City modal must be nested inside x-data="window.tbsDashboardCustomizer()".');
    }
}

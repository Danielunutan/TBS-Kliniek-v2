<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\CooldownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelScalingActivitiesTest extends TestCase
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
            'name' => 'ScalingTester',
            'cash' => 50000,
            'bank' => 100000,
            'stars' => 50,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 1,
            'respect' => 100,
            'vault_tokens' => 5,
        ]);
    }

    public function test_daycare_level_gates_high_tier_jobs(): void
    {
        $this->actingAs($this->user);

        // Job 6 requires Level 10 (character is Level 1)
        $res = $this->post(route('daycare.perform'), ['job_id' => 6]);
        $res->assertSessionHas('error');
        $this->assertStringContainsString('minimaal Level 10', session('error'));

        // Promote to Level 10 and try Job 6
        $this->character->level = 10;
        $this->character->save();

        $resSuccess = $this->post(route('daycare.perform'), ['job_id' => 6]);
        $resSuccess->assertSessionHas('success');
        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'dagbesteding'));

        // Clear cooldown, try Job 7 (requires Level 20)
        app(CooldownService::class)->clearCooldown($this->character, 'dagbesteding');
        $resJob7 = $this->post(route('daycare.perform'), ['job_id' => 7]);
        $resJob7->assertSessionHas('error');
        $this->assertStringContainsString('minimaal Level 20', session('error'));

        // Promote to Level 20 and execute Job 7
        $this->character->level = 20;
        $this->character->save();
        $resJob7Success = $this->post(route('daycare.perform'), ['job_id' => 7]);
        $resJob7Success->assertSessionHas('success');
    }

    public function test_extort_level_gates_high_tier_targets(): void
    {
        $this->actingAs($this->user);

        // Target 7 requires Level 15 (character is Level 1)
        $res = $this->post(route('extort.extort'), ['target_id' => 7]);
        $res->assertSessionHas('error');
        $this->assertStringContainsString('minimaal Level 15', session('error'));

        // Target 8 requires Level 25
        $this->character->level = 15;
        $this->character->save();

        $resTarget8 = $this->post(route('extort.extort'), ['target_id' => 8]);
        $resTarget8->assertSessionHas('error');
        $this->assertStringContainsString('minimaal Level 25', session('error'));
    }

    public function test_lockers_level_gates_high_tier_lockers(): void
    {
        $this->actingAs($this->user);

        // Locker 7 requires Level 15 (character is Level 1)
        $res = $this->post(route('lockers.crack'), ['locker_id' => 7]);
        $res->assertSessionHas('error');
        $this->assertStringContainsString('minimaal Level 15', session('error'));

        // Locker 8 requires Level 25
        $this->character->level = 20;
        $this->character->save();

        $resLocker8 = $this->post(route('lockers.crack'), ['locker_id' => 8]);
        $resLocker8->assertSessionHas('error');
        $this->assertStringContainsString('minimaal Level 25', session('error'));
    }

    public function test_helicopter_dropping_requires_level_15_and_cash(): void
    {
        $this->actingAs($this->user);

        // At level 1, helicopter dropping is blocked
        $res = $this->post(route('droppings.helicopter'));
        $res->assertSessionHas('error');
        $this->assertStringContainsString('minimaal Level 15', session('error'));

        // Promote to level 15 with insufficient cash
        $this->character->level = 15;
        $this->character->cash = 100;
        $this->character->save();

        $resNoCash = $this->post(route('droppings.helicopter'));
        $resNoCash->assertSessionHas('error');
        $this->assertStringContainsString('contant nodig', session('error'));

        // With cash and level 15, ordering helicopter succeeds or records dropping
        $this->character->cash = 5000;
        $this->character->save();

        $resSuccess = $this->post(route('droppings.helicopter'));
        $this->character->refresh();
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($this->character, 'dropping_helicopter'));
    }

    public function test_views_render_level_scaling_bonus_when_character_is_high_level(): void
    {
        $this->actingAs($this->user);
        $this->character->level = 28;
        $this->character->save();

        // Daycare view
        $daycareRes = $this->get(route('daycare.index'));
        $daycareRes->assertOk();
        $daycareRes->assertSee('Level Bonus:', false);
        $daycareRes->assertSee('+108% Beloning', false);

        // Extort view
        $extortRes = $this->get(route('extort.index'));
        $extortRes->assertOk();
        $extortRes->assertSee('Level Bonus:', false);
        $extortRes->assertSee('+108% Buit &amp; XP', false);

        // Lockers view
        $lockersRes = $this->get(route('lockers.index'));
        $lockersRes->assertOk();
        $lockersRes->assertSee('Level Bonus:', false);
        $lockersRes->assertSee('+108% Buit &amp; XP', false);

        // Droppings view
        $droppingsRes = $this->get(route('droppings.index'));
        $droppingsRes->assertOk();
        $droppingsRes->assertSee('Kartel Stealth-Helikopter Dropping', false);
        $droppingsRes->assertSee('Level Bonus:', false);
    }
}

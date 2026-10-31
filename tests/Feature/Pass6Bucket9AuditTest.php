<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket9AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_9_routes_render_and_enforce_coma_and_timeline_guards(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user);

        $slots = $this->get(route('slots.index'));
        $slots->assertOk();
        $slots->assertSee(':disabled="isSpinning || cash < bet || true"', false);

        $timeline = $this->get(route('timeline.index'));
        $timeline->assertOk();
        $timeline->assertSee(':disabled="text.trim().length < 2"', false);

        $this->get(route('settings.account'))->assertOk();
        $this->get(route('shop.index'))->assertOk();
        $this->get(route('smuggle.index'))->assertOk();
        $this->get(route('theft.index'))->assertOk();
        $this->get(route('help.tickets.index'))->assertOk();
        $this->get(route('help.tickets.create'))->assertOk();
        $this->get(route('training.index'))->assertOk();
        $this->get(route('travel.index'))->assertOk();
        $this->get(route('treatment.index'))->assertOk();
        $this->get(route('true_crime.index'))->assertOk();
        $this->get(route('tunnel.index'))->assertOk();
        $this->get(route('vault.index'))->assertOk();
        $this->get(route('vip.index'))->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket8AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_8_casino_views_disable_play_when_comatose_and_default_rps_choice(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user);

        $rps = $this->get(route('rps.index'));
        $rps->assertOk();
        $rps->assertSee('value="steen" class="peer sr-only" required checked', false);
        $rps->assertSee('disabled', false);

        $numberGame = $this->get(route('numbergame.index'));
        $numberGame->assertOk();
        $numberGame->assertSee('disabled', false);

        $roulette = $this->get(route('roulette.index'));
        $roulette->assertOk();
        $roulette->assertSee('disabled', false);

        $pickpocket = $this->get(route('pickpocket.index'));
        $pickpocket->assertOk();
        $pickpocket->assertSee('disabled', false);

        $perks = $this->get(route('perks.index'));
        $perks->assertOk();
        $perks->assertSee('disabled', false);
    }
}

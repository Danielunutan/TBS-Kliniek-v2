<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket7AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_7_views_render_and_highlow_disables_buttons_when_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);
        $gang = Gang::factory()->create(['leader_character_id' => $character->id]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $this->actingAs($user);

        $this->get(route('gangs.index'))->assertOk();
        $this->get(route('gangs.garage'))->assertOk();
        $this->get(route('gangs.panden'))->assertOk();
        $this->get(route('garage.index'))->assertOk();
        $this->get(route('hacking.index'))->assertOk();
        $this->get(route('halloween.quests'))->assertOk();
        $this->get(route('halloween.shop'))->assertOk();
        $this->get(route('heists.index'))->assertOk();
        $this->get(route('hitman.index'))->assertOk();

        $highlow = $this->get(route('highlow.index'));
        $highlow->assertOk();
        $highlow->assertSee('value="lager"', false);
        $highlow->assertSee('disabled', false);
    }
}

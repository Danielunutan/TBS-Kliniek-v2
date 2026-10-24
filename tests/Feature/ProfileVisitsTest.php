<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\ProfileVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileVisitsTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 10,
            'respect' => 10,
            'title' => 'Modelpatiënt',
        ], $attrs));
    }

    public function test_visiting_another_profile_records_a_profile_visit(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'InmateAlice']);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'InmateBob']);

        $response = $this->actingAs($userA)->get(route('profile.show', $charB->name));

        $response->assertOk();
        $response->assertSee('Laatst Bezochte TBS', false);
        $response->assertSee('InmateAlice');

        $this->assertDatabaseHas('profile_visits', [
            'visitor_id' => $charA->id,
            'visited_id' => $charB->id,
        ]);
    }

    public function test_visiting_own_profile_does_not_record_self_visit(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'InmateAlice']);

        $response = $this->actingAs($userA)->get(route('profile.show', $charA->name));

        $response->assertOk();
        $this->assertDatabaseMissing('profile_visits', [
            'visitor_id' => $charA->id,
            'visited_id' => $charA->id,
        ]);
    }

    public function test_subsequent_visits_update_timestamp_without_duplicate_records(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'InmateAlice']);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'InmateBob']);

        $this->actingAs($userA)->get(route('profile.show', $charB->name));
        $this->actingAs($userA)->get(route('profile.show', $charB->name));

        $this->assertEquals(1, ProfileVisit::where('visitor_id', $charA->id)->where('visited_id', $charB->id)->count());
    }
}

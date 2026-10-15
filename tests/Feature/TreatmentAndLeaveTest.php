<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Support\TreatmentCaseCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentAndLeaveTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
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
            'treatment_score' => 50,
            'leave_trips_count' => 0,
            'last_active_at' => now(),
        ], $attrs));
    }

    public function test_treatment_page_can_be_viewed_by_authenticated_user(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('treatment.index'));

        $response->assertStatus(200);
        $response->assertSee('Behandelaarsgesprek');
        $response->assertSee('Incident van Vandaag');
    }

    public function test_submitting_cooperative_answers_grants_accompanied_leave_and_rewards(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 1000,
            'treatment_score' => 50,
        ]);

        $this->actingAs($user)->withSession(['treatment_questions' => [1, 2, 3]]);

        $response = $this->post(route('treatment.submit'), [
            'answers' => [
                1 => 'a', // +25
                2 => 'a', // +25
                3 => 'a', // +25
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertTrue($character->isOnLeave());
        $this->assertNotNull($character->leave_until);
        $this->assertNull($character->sedated_until);
        $this->assertGreaterThan(50, $character->treatment_score);
        $this->assertEquals(3500, $character->cash); // +2500 cash
    }

    public function test_submitting_aggressive_answers_imposes_sedation_and_blocks_leave(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'energy' => 80,
            'treatment_score' => 30,
        ]);

        $this->actingAs($user)->withSession(['treatment_questions' => [1, 2, 3]]);

        $response = $this->post(route('treatment.submit'), [
            'answers' => [
                1 => 'c', // -30
                2 => 'c', // -25
                3 => 'c', // -30
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $character->refresh();
        $this->assertFalse($character->isOnLeave());
        $this->assertTrue($character->isSedated());
        $this->assertNotNull($character->sedated_until);
        $this->assertEquals(50, $character->energy); // -30 energy penalty
    }

    public function test_leave_action_executes_successfully_when_on_leave(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'energy' => 50,
            'cash' => 500,
            'leave_until' => now()->addHours(2),
            'leave_trips_count' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('treatment.leave-action'), [
            'action_id' => 'supermarket',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(38, $character->energy); // 50 - 12
        $this->assertGreaterThan(500, $character->cash);
        $this->assertEquals(1, $character->leave_trips_count);
    }

    public function test_leave_action_fails_when_character_is_not_on_leave(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'leave_until' => null,
            'energy' => 50,
        ]);

        $response = $this->actingAs($user)->post(route('treatment.leave-action'), [
            'action_id' => 'supermarket',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_every_day_of_the_year_has_a_valid_unique_psychiatric_case(): void
    {
        for ($day = 1; $day <= 365; $day++) {
            $case = TreatmentCaseCatalog::getCaseForDay($day);

            $this->assertEquals($day, $case['day_of_year']);
            $this->assertNotEmpty($case['case_code']);
            $this->assertNotEmpty($case['title']);
            $this->assertNotEmpty($case['doctor']['name']);
            $this->assertNotEmpty($case['incident']);
            $this->assertCount(3, $case['questions']);

            foreach ($case['questions'] as $q) {
                $this->assertArrayHasKey('question', $q);
                $this->assertCount(4, $q['options']);
            }
        }
    }
}

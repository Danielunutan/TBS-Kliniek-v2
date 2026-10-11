<?php

namespace Tests\Feature;

use App\Models\CharacterMission;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiftyMissionsAndSingleCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_fifty_unique_missions_exist_in_the_database(): void
    {
        $this->assertEquals(50, Mission::count());

        $first = Mission::where('sort_order', 1)->first();
        $last = Mission::where('sort_order', 50)->first();

        $this->assertNotNull($first);
        $this->assertNotNull($last);
        $this->assertEquals('koffie-en-kaas', $first->slug);
        $this->assertEquals('ultieme-kliniek-uitbraak', $last->slug);
    }

    public function test_mission_can_only_be_completed_once(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->missions()->delete();
        $character->update(['level' => 10, 'energy' => 100, 'is_in_prison' => false, 'missions_completed' => 0]);

        $mission = Mission::where('slug', 'koffie-en-kaas')->first();
        $mission->update(['success_rate' => 100]); // Guarantee success

        $this->actingAs($user);

        // 1. First execution succeeds with correct choice 'B'
        $response = $this->post(route('missions.execute', $mission), [
            'choice' => 'B',
        ]);
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertTrue($character->hasCompletedMission($mission));
        $this->assertEquals(1, CharacterMission::where('character_id', $character->id)->where('mission_id', $mission->id)->value('completed_count'));

        // 2. Second execution attempt is rejected!
        $secondResponse = $this->post(route('missions.execute', $mission), [
            'choice' => 'B',
        ]);
        $secondResponse->assertSessionHas('error');

        // Confirm still completed only once
        $this->assertEquals(1, CharacterMission::where('character_id', $character->id)->where('mission_id', $mission->id)->value('completed_count'));
    }

    public function test_missions_page_displays_progress_and_completed_status(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->missions()->delete();

        $m1 = Mission::where('sort_order', 1)->first();
        $m2 = Mission::where('sort_order', 2)->first();

        CharacterMission::create([
            'character_id' => $character->id,
            'mission_id' => $m1->id,
            'completed_count' => 1,
        ]);
        CharacterMission::create([
            'character_id' => $character->id,
            'mission_id' => $m2->id,
            'completed_count' => 1,
        ]);
        $character->missions_completed = 2;
        $character->save();

        $this->actingAs($user);

        $response = $this->get(route('missions.index'));
        $response->assertStatus(200);
        $response->assertSee('2 / 50');
        $response->assertSee('Voltooid (Eenmalig afgerond)');
        $response->assertSee('Kliniek Missies');
    }
}

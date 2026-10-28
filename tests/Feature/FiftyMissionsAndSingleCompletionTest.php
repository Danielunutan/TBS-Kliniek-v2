<?php

namespace Tests\Feature;

use App\Models\CharacterMission;
use App\Models\Mission;
use App\Models\User;
use App\Support\MissionScenarioCatalog;
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
        $this->assertEquals(70, Mission::count());

        $first = Mission::where('sort_order', 1)->first();
        $mid = Mission::where('sort_order', 50)->first();
        $last = Mission::where('sort_order', 70)->first();

        $this->assertNotNull($first);
        $this->assertNotNull($mid);
        $this->assertNotNull($last);
        $this->assertEquals('koffie-en-kaas', $first->slug);
        $this->assertEquals('ultieme-kliniek-uitbraak', $mid->slug);
        $this->assertEquals('keizer-tbs-onderwereld-eeuwige-kroon', $last->slug);
        $this->assertEquals(60, $last->required_level);
        $this->assertEquals(20, Mission::where('required_level', '>', 35)->count());
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
        $response->assertSee('2 / 70');
        $response->assertSee('Voltooid (Eenmalig afgerond)');
        $response->assertSee('Kliniek Missies');
    }

    public function test_all_seventy_missions_have_interactive_scenarios_with_valid_choices(): void
    {
        $missions = Mission::all();
        $this->assertCount(70, $missions);

        foreach ($missions as $mission) {
            $scenario = MissionScenarioCatalog::get($mission);
            $this->assertNotNull($scenario, "Mission '{$mission->slug}' (#{$mission->id}) must have an interactive scenario!");
            $this->assertNotEmpty($scenario['npc_name']);
            $this->assertNotEmpty($scenario['npc_avatar']);
            $this->assertNotEmpty($scenario['dilemma']);
            $this->assertArrayHasKey('choices', $scenario);
            $this->assertCount(3, $scenario['choices'], "Mission '{$mission->slug}' must have exactly 3 choices!");
            $this->assertContains($scenario['correct_choice'], ['A', 'B', 'C']);
            $this->assertNotEmpty($scenario['feedback_wrong']);
        }
    }

    public function test_wrong_mission_choice_fails_and_jails_character(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->missions()->delete();
        $character->update(['level' => 10, 'energy' => 100, 'is_in_prison' => false, 'missions_completed' => 0]);

        $mission = Mission::where('slug', 'de-eerste-diefstal')->first();
        $scenario = MissionScenarioCatalog::get($mission);
        $wrongChoice = $scenario['correct_choice'] === 'A' ? 'B' : 'A';

        $this->actingAs($user);

        $response = $this->post(route('missions.execute', $mission), [
            'choice' => $wrongChoice,
        ]);

        $response->assertRedirect(route('prison'));
        $character->refresh();
        $this->assertTrue($character->isImprisoned());
        $this->assertFalse($character->hasCompletedMission($mission));
    }
}

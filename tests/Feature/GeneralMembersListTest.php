<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralMembersListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_members_and_online(): void
    {
        $this->get(route('members.index'))->assertRedirect(route('login'));
        $this->get(route('online.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_general_members_list_with_realtime_data(): void
    {
        $user1 = User::factory()->create();
        $char1 = Character::create([
            'user_id' => $user1->id,
            'name' => 'ActivePatientOne',
            'gender' => 'male',
            'level' => 12,
            'respect' => 750,
            'last_active_at' => now(), // Online
        ]);

        $gang = Gang::create([
            'name' => 'Syndicate',
            'tag' => 'SYN',
            'specialty' => 'Vleugel C',
            'leader_character_id' => $char1->id,
            'treasury' => 5000,
        ]);
        $char1->gang_id = $gang->id;
        $char1->gang_role = 'leader';
        $char1->save();

        $user2 = User::factory()->create();
        $char2 = Character::create([
            'user_id' => $user2->id,
            'name' => 'SleepingPatientTwo',
            'gender' => 'female',
            'level' => 5,
            'respect' => 100,
            'last_active_at' => now()->subDay(), // Offline
        ]);

        $this->actingAs($user1);

        $response = $this->get(route('members.index'));

        $response->assertStatus(200);
        $response->assertViewIs('game.members.index');
        $response->assertViewHas('totalCount', 2);
        $response->assertViewHas('onlineCount', 1);
        $response->assertViewHas('gangCount', 1);
        $response->assertViewHas('soloCount', 1);

        $response->assertViewHas('membersData', function ($data) {
            $char1Data = $data->firstWhere('name', 'ActivePatientOne');
            $char2Data = $data->firstWhere('name', 'SleepingPatientTwo');

            return $char1Data['is_online'] === true
                && $char1Data['gang_tag'] === 'SYN'
                && $char2Data['is_online'] === false
                && $char2Data['gang_tag'] === null;
        });

        $response->assertSee('Ledenlijst &amp; Online Patiënten', false);
        $response->assertSee('ActivePatientOne');
        $response->assertSee('SleepingPatientTwo');
    }

    public function test_online_route_redirects_to_members_index_with_online_filter(): void
    {
        $user = User::factory()->create();
        Character::create([
            'user_id' => $user->id,
            'name' => 'OnlinePlayer',
            'gender' => 'male',
            'level' => 1,
            'last_active_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('online.index'));
        $response->assertRedirect(route('members.index', ['filter' => 'online']));
    }
}

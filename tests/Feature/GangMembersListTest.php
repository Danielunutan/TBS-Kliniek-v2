<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangMembersListTest extends TestCase
{
    use RefreshDatabase;

    public function test_gang_members_list_data_and_online_count_are_passed_to_view(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::create([
            'user_id' => $leaderUser->id,
            'name' => 'LeaderMember',
            'gender' => 'male',
            'level' => 15,
            'respect' => 500,
            'last_active_at' => now(), // Online
        ]);

        $gang = Gang::create([
            'name' => 'OnlineGang',
            'tag' => 'OG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'treasury' => 2000,
        ]);

        $leader->gang_id = $gang->id;
        $leader->gang_role = 'leader';
        $leader->save();

        $offlineUser = User::factory()->create();
        $offlineMember = Character::create([
            'user_id' => $offlineUser->id,
            'name' => 'OfflineMember',
            'gender' => 'female',
            'level' => 8,
            'respect' => 200,
            'gang_id' => $gang->id,
            'gang_role' => 'member',
            'last_active_at' => now()->subHours(2), // Offline
        ]);

        $this->actingAs($leaderUser);

        // Test gangs.leden route
        $response = $this->get(route('gangs.leden'));
        $response->assertStatus(200);
        $response->assertViewHas('activeTab', 'leden');
        $response->assertViewHas('onlineMembersCount', 1);
        $response->assertViewHas('gangMembersData', function ($data) {
            return $data->count() === 2
                && $data->firstWhere('name', 'LeaderMember')['is_online'] === true
                && $data->firstWhere('name', 'OfflineMember')['is_online'] === false;
        });

        // Test standard gangs view has the members data
        $mainResponse = $this->get(route('gangs.index'));
        $mainResponse->assertStatus(200);
        $mainResponse->assertViewHas('onlineMembersCount', 1);
        $mainResponse->assertSee('Bende Ledenlijst');
        $mainResponse->assertSee('Zoek bendelid op naam of rol (realtime)...');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_leader_cannot_set_leave_fee_multiplier_greater_than_50(): void
    {
        $user = User::factory()->create();
        $leader = Character::create([
            'user_id' => $user->id,
            'name' => 'LeaderTest',
            'gender' => 'male',
            'level' => 10,
        ]);

        $gang = Gang::create([
            'name' => 'TestGang',
            'tag' => 'TG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'treasury' => 1000,
            'leave_fee_multiplier' => 10,
        ]);

        $leader->gang_id = $gang->id;
        $leader->gang_role = 'leader';
        $leader->save();

        $this->actingAs($user);

        // Attempting to set multiplier > 50 should fail validation
        $response = $this->post(route('gangs.settings'), [
            'action_type' => 'leave_fee',
            'leave_fee_multiplier' => 51,
        ]);

        $response->assertSessionHasErrors(['leave_fee_multiplier']);
        $this->assertEquals(10, $gang->fresh()->leave_fee_multiplier);

        // Setting to 50 should succeed
        $responseValid = $this->post(route('gangs.settings'), [
            'action_type' => 'leave_fee',
            'leave_fee_multiplier' => 50,
        ]);

        $responseValid->assertSessionHasNoErrors();
        $this->assertEquals(50, $gang->fresh()->leave_fee_multiplier);
    }

    public function test_member_cannot_leave_gang_without_enough_cash_for_vertrekboete(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::create([
            'user_id' => $leaderUser->id,
            'name' => 'GangLeader',
            'gender' => 'male',
            'level' => 10,
        ]);

        $gang = Gang::create([
            'name' => 'FeeGang',
            'tag' => 'FG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'treasury' => 5000,
            'leave_fee_multiplier' => 20, // 20 * 150 = 3000 fee
        ]);

        $memberUser = User::factory()->create();
        $member = Character::create([
            'user_id' => $memberUser->id,
            'name' => 'BrokeMember',
            'gender' => 'female',
            'level' => 5,
            'cash' => 500, // Not enough!
            'gang_id' => $gang->id,
            'gang_role' => 'member',
        ]);

        $this->actingAs($memberUser);

        $response = $this->post(route('gangs.leave'));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Je kunt de bende niet verlaten: de vertrekboete bedraagt', session('error'));
        $this->assertEquals($gang->id, $member->fresh()->gang_id);
        $this->assertEquals(5000, $gang->fresh()->treasury);
    }

    public function test_member_pays_vertrekboete_to_gang_treasury_when_leaving(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::create([
            'user_id' => $leaderUser->id,
            'name' => 'GangLeader2',
            'gender' => 'male',
            'level' => 10,
        ]);

        $gang = Gang::create([
            'name' => 'RichGang',
            'tag' => 'RG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'treasury' => 10000,
            'leave_fee_multiplier' => 10, // 10 * 150 = 1500 fee
        ]);

        $memberUser = User::factory()->create();
        $member = Character::create([
            'user_id' => $memberUser->id,
            'name' => 'PayingMember',
            'gender' => 'male',
            'level' => 5,
            'cash' => 2000, // Enough!
            'gang_id' => $gang->id,
            'gang_role' => 'member',
        ]);

        $this->actingAs($memberUser);

        $response = $this->post(route('gangs.leave'));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('vertrekboete', session('success'));

        $member->refresh();
        $gang->refresh();

        $this->assertNull($member->gang_id);
        $this->assertEquals('none', $member->gang_role);
        $this->assertEquals(500, $member->cash); // 2000 - 1500
        $this->assertEquals(11500, $gang->treasury); // 10000 + 1500
    }
}

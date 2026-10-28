<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\User;
use App\Models\Vehicle;
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

    public function test_leaving_gang_returns_stored_vehicles_to_personal_garage(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::create([
            'user_id' => $leaderUser->id,
            'name' => 'LeaderWithMember',
            'gender' => 'male',
            'level' => 10,
        ]);

        $gang = Gang::create([
            'name' => 'VehicleGang',
            'tag' => 'VG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'treasury' => 1000,
            'leave_fee_multiplier' => 0,
        ]);

        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $memberUser = User::factory()->create();
        $member = Character::create([
            'user_id' => $memberUser->id,
            'name' => 'DriverMember',
            'gender' => 'male',
            'level' => 5,
            'gang_id' => $gang->id,
            'gang_role' => 'member',
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Getaway Car',
            'slug' => 'getaway-car',
            'icon' => 'fa-car',
            'price_cash' => 5000,
            'speed_pct' => 80,
            'reliability_pct' => 80,
            'min_level' => 1,
            'is_available' => true,
        ]);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $member->id,
            'vehicle_id' => $vehicle->id,
            'gang_id' => $gang->id,
            'in_gang_garage' => true,
            'stored_by_character_id' => $member->id,
            'stored_at' => now(),
            'current_condition' => 100,
        ]);

        $this->actingAs($memberUser);
        $response = $this->post(route('gangs.leave'));

        $response->assertSessionHas('success');
        $charVehicle->refresh();

        $this->assertFalse($charVehicle->in_gang_garage);
        $this->assertNull($charVehicle->gang_id);
        $this->assertNull($charVehicle->stored_at);
        $this->assertNull($charVehicle->stored_by_character_id);
    }

    public function test_kicking_member_returns_stored_vehicles_to_personal_garage(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::create([
            'user_id' => $leaderUser->id,
            'name' => 'BossKicker',
            'gender' => 'male',
            'level' => 10,
        ]);

        $gang = Gang::create([
            'name' => 'KickGang',
            'tag' => 'KG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
        ]);

        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $memberUser = User::factory()->create();
        $member = Character::create([
            'user_id' => $memberUser->id,
            'name' => 'KickedMember',
            'gender' => 'female',
            'level' => 5,
            'gang_id' => $gang->id,
            'gang_role' => 'member',
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Bike',
            'slug' => 'bike',
            'icon' => 'fa-motorcycle',
            'price_cash' => 2000,
            'speed_pct' => 60,
            'reliability_pct' => 70,
            'min_level' => 1,
            'is_available' => true,
        ]);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $member->id,
            'vehicle_id' => $vehicle->id,
            'gang_id' => $gang->id,
            'in_gang_garage' => true,
            'stored_by_character_id' => $member->id,
            'stored_at' => now(),
            'current_condition' => 100,
        ]);

        $this->actingAs($leaderUser);
        $response = $this->post(route('gangs.kick', $member));

        $response->assertSessionHas('success');
        $charVehicle->refresh();

        $this->assertFalse($charVehicle->in_gang_garage);
        $this->assertNull($charVehicle->gang_id);
        $this->assertNull($charVehicle->stored_at);
        $this->assertNull($charVehicle->stored_by_character_id);
    }

    public function test_disbanding_gang_returns_all_stored_vehicles_to_owners(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::create([
            'user_id' => $leaderUser->id,
            'name' => 'DisbandLeader',
            'gender' => 'male',
            'level' => 10,
        ]);

        $gang = Gang::create([
            'name' => 'DisbandGang',
            'tag' => 'DG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
        ]);

        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $vehicle = Vehicle::create([
            'name' => 'Truck',
            'slug' => 'truck',
            'icon' => 'fa-truck',
            'price_cash' => 10000,
            'speed_pct' => 50,
            'reliability_pct' => 90,
            'min_level' => 1,
            'is_available' => true,
        ]);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $leader->id,
            'vehicle_id' => $vehicle->id,
            'gang_id' => $gang->id,
            'in_gang_garage' => true,
            'stored_by_character_id' => $leader->id,
            'stored_at' => now(),
            'current_condition' => 100,
        ]);

        $this->actingAs($leaderUser);
        $response = $this->post(route('gangs.disband'));

        $response->assertSessionHas('success');
        $charVehicle->refresh();

        $this->assertFalse($charVehicle->in_gang_garage);
        $this->assertNull($charVehicle->gang_id);
        $this->assertNull($charVehicle->stored_at);
        $this->assertNull($charVehicle->stored_by_character_id);
    }
}

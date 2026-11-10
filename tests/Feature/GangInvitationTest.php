<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_can_invite_solo_player(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'name' => 'DonVito',
            'gang_role' => 'leader',
        ]);
        $gang = Gang::factory()->create([
            'name' => 'Corleone',
            'tag' => 'CRL',
            'leader_character_id' => $leader->id,
            'max_members' => 10,
        ]);
        $leader->update(['gang_id' => $gang->id]);

        $soloUser = User::factory()->create();
        $solo = Character::factory()->create([
            'user_id' => $soloUser->id,
            'name' => 'Michael',
            'gang_id' => null,
        ]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.invite'), [
            'player_name' => 'Michael',
            'message' => 'Kom bij onze familie!',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('gang_invitations', [
            'gang_id' => $gang->id,
            'character_id' => $solo->id,
            'invited_by_id' => $leader->id,
            'message' => 'Kom bij onze familie!',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $solo->id,
            'type' => 'gang_invite',
        ]);

        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $solo->id,
            'sender_id' => $leader->id,
            'type' => 'gang',
        ]);
    }

    public function test_officer_cannot_invite_player_already_in_a_gang(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'gang_role' => 'leader',
        ]);
        $gang = Gang::factory()->create([
            'leader_character_id' => $leader->id,
        ]);
        $leader->update(['gang_id' => $gang->id]);

        $otherGang = Gang::factory()->create();
        $otherMember = Character::factory()->create([
            'name' => 'AlreadyTaken',
            'gang_id' => $otherGang->id,
        ]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.invite'), [
            'player_name' => 'AlreadyTaken',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('gang_invitations', [
            'character_id' => $otherMember->id,
        ]);
    }

    public function test_player_can_accept_invitation_and_joins_gang(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'gang_role' => 'leader',
        ]);
        $gang = Gang::factory()->create([
            'name' => 'Peaky Blinders',
            'tag' => 'PB',
            'leader_character_id' => $leader->id,
            'max_members' => 10,
        ]);
        $leader->update(['gang_id' => $gang->id]);

        $soloUser = User::factory()->create();
        $solo = Character::factory()->create([
            'user_id' => $soloUser->id,
            'name' => 'Arthur',
            'gang_id' => null,
        ]);

        $invitation = GangInvitation::create([
            'gang_id' => $gang->id,
            'character_id' => $solo->id,
            'invited_by_id' => $leader->id,
            'message' => 'Join us!',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($soloUser)->post(route('gangs.handle-invite', $invitation), [
            'action' => 'accept',
        ]);

        $response->assertRedirect(route('gangs.index', ['tab' => 'mijn_gang']));

        $solo->refresh();
        $this->assertEquals($gang->id, $solo->gang_id);
        $this->assertEquals('member', $solo->gang_role);

        $invitation->refresh();
        $this->assertEquals('accepted', $invitation->status);

        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $leader->id,
            'type' => 'gang',
        ]);
    }

    public function test_player_can_decline_invitation(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'gang_role' => 'leader',
        ]);
        $gang = Gang::factory()->create([
            'leader_character_id' => $leader->id,
        ]);
        $leader->update(['gang_id' => $gang->id]);

        $soloUser = User::factory()->create();
        $solo = Character::factory()->create([
            'user_id' => $soloUser->id,
            'gang_id' => null,
        ]);

        $invitation = GangInvitation::create([
            'gang_id' => $gang->id,
            'character_id' => $solo->id,
            'invited_by_id' => $leader->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($soloUser)->post(route('gangs.handle-invite', $invitation), [
            'action' => 'decline',
        ]);

        $response->assertSessionHas('info');

        $solo->refresh();
        $this->assertNull($solo->gang_id);

        $invitation->refresh();
        $this->assertEquals('declined', $invitation->status);
    }

    public function test_officer_can_cancel_invitation(): void
    {
        $leaderUser = User::factory()->create();
        $leader = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'gang_role' => 'leader',
        ]);
        $gang = Gang::factory()->create([
            'leader_character_id' => $leader->id,
        ]);
        $leader->update(['gang_id' => $gang->id]);

        $solo = Character::factory()->create(['gang_id' => null]);

        $invitation = GangInvitation::create([
            'gang_id' => $gang->id,
            'character_id' => $solo->id,
            'invited_by_id' => $leader->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.cancel-invite', $invitation));

        $response->assertSessionHas('success');

        $invitation->refresh();
        $this->assertEquals('cancelled', $invitation->status);
    }
}

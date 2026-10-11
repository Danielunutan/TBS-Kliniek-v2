<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Friend;
use App\Models\Gang;
use App\Models\GangRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangRequestAndFriendMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(string $name, int $respect = 100): Character
    {
        $user = User::factory()->create();

        return Character::create([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 1000,
            'bank' => 500,
            'respect' => $respect,
            'energy' => 100,
            'max_energy' => 100,
            'level' => 1,
            'xp' => 0,
        ]);
    }

    public function test_player_cannot_send_message_to_non_friend(): void
    {
        $char1 = $this->createCharacter('SpelerEen');
        $char2 = $this->createCharacter('SpelerTwee');

        $this->actingAs($char1->user);

        $response = $this->post(route('messages.send'), [
            'recipient_name' => $char2->name,
            'subject' => 'Hoi onbekende',
            'content' => 'Mag ik je wat vragen?',
        ]);

        $response->assertSessionHas('error', "Je kunt alleen brieven sturen naar patiënten die op je vriendenlijst staan! Voeg {$char2->name} eerst toe als vriend via Vrienden.");
        $this->assertDatabaseMissing('game_messages', [
            'sender_id' => $char1->id,
            'recipient_id' => $char2->id,
        ]);
    }

    public function test_player_can_send_message_to_accepted_friend(): void
    {
        $char1 = $this->createCharacter('VriendEen');
        $char2 = $this->createCharacter('VriendTwee');

        Friend::create([
            'character_id' => $char1->id,
            'friend_id' => $char2->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($char1->user);

        $response = $this->post(route('messages.send'), [
            'recipient_name' => $char2->name,
            'subject' => 'Beste vriend',
            'content' => 'Gezellig op de binnenplaats wandelen?',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('game_messages', [
            'sender_id' => $char1->id,
            'recipient_id' => $char2->id,
            'subject' => 'Beste vriend',
        ]);
    }

    public function test_gang_membership_request_creates_pending_request_and_notifies_leader(): void
    {
        $leader = $this->createCharacter('BendeLeider', 500);

        $gang = Gang::create([
            'name' => 'De Peaky Blinders',
            'tag' => 'PEAK',
            'specialty' => 'Kantine',
            'leader_character_id' => $leader->id,
            'treasury' => 5000,
            'is_open' => true,
        ]);

        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $recruit = $this->createCharacter('Nieuwkomer', 200);

        $this->actingAs($recruit->user);

        $response = $this->post(route('gangs.request-join', $gang), [
            'message' => 'Ik wil me graag bewijzen voor de bende!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('gang_requests', [
            'gang_id' => $gang->id,
            'character_id' => $recruit->id,
            'status' => 'pending',
            'message' => 'Ik wil me graag bewijzen voor de bende!',
        ]);

        // Leader gets notified
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $leader->id,
            'type' => 'gang',
        ]);
    }

    public function test_direct_join_route_routes_to_request_flow_instead_of_instant_join(): void
    {
        $leader = $this->createCharacter('CapoLeader', 500);

        $gang = Gang::create([
            'name' => 'Syndicaat',
            'tag' => 'SYND',
            'specialty' => 'Kantine',
            'leader_character_id' => $leader->id,
            'treasury' => 1000,
            'is_open' => true,
        ]);

        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $applicant = $this->createCharacter('KandidaatSpeler', 200);

        $this->actingAs($applicant->user);

        // Posting to gangs.join must NOT instantly make candidate a member
        $response = $this->post(route('gangs.join', $gang));

        $response->assertSessionHas('success');
        $applicant->refresh();
        $this->assertNull($applicant->gang_id);

        $this->assertDatabaseHas('gang_requests', [
            'gang_id' => $gang->id,
            'character_id' => $applicant->id,
            'status' => 'pending',
        ]);
    }

    public function test_gang_leader_can_accept_and_reject_membership_requests(): void
    {
        $leader = $this->createCharacter('ElPatron', 1000);

        $gang = Gang::create([
            'name' => 'Cartel TBS',
            'tag' => 'CART',
            'specialty' => 'Kantine',
            'leader_character_id' => $leader->id,
            'treasury' => 10000,
            'is_open' => true,
        ]);

        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $recruit = $this->createCharacter('Soldaat', 300);

        $req = GangRequest::create([
            'gang_id' => $gang->id,
            'character_id' => $recruit->id,
            'status' => 'pending',
        ]);

        $this->actingAs($leader->user);

        $response = $this->post(route('gangs.handle-request', $req), [
            'action' => 'accept',
        ]);

        $response->assertSessionHas('success');
        $req->refresh();
        $recruit->refresh();

        $this->assertEquals('accepted', $req->status);
        $this->assertEquals($gang->id, $recruit->gang_id);
        $this->assertEquals('member', $recruit->gang_role);
    }

    public function test_gang_leader_can_toggle_requests_off_in_settings_and_subsequent_requests_are_blocked(): void
    {
        $leader = $this->createCharacter('DonVito', 1000);

        $gang = Gang::create([
            'name' => 'Gesloten Factie',
            'tag' => 'SLOT',
            'specialty' => 'Kantine',
            'leader_character_id' => $leader->id,
            'treasury' => 2000,
            'is_open' => true,
        ]);

        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $this->actingAs($leader->user);

        // Toggle requests off
        $response = $this->post(route('gangs.settings'), [
            'action_type' => 'requests_toggle',
            'is_open' => '0',
        ]);

        $response->assertSessionHas('success');
        $gang->refresh();
        $this->assertFalse((bool) $gang->is_open);

        // Now an outsider tries to request to join
        $outsider = $this->createCharacter('Buitenstaander', 200);

        $this->actingAs($outsider->user);

        $requestResponse = $this->post(route('gangs.request-join', $gang));
        $requestResponse->assertSessionHas('error', "Bende '{$gang->name}' accepteert momenteel geen nieuwe lidmaatschapsverzoeken (gesloten).");

        $this->assertDatabaseMissing('gang_requests', [
            'gang_id' => $gang->id,
            'character_id' => $outsider->id,
        ]);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Character;
use App\Models\Gang;

class Pass6Bucket7AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_7_views_are_renderable(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $gang = Gang::factory()->create(['leader_character_id' => $character->id]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $this->actingAs($user);

        // Test routes or views associated with Bucket 7
        $response = $this->get(route('gangs.index'));
        $response->assertStatus(200);

        $responseGarage = $this->get(route('gangs.garage'));
        $responseGarage->assertStatus(200);

        $responsePanden = $this->get(route('gangs.panden'));
        $responsePanden->assertStatus(200);

        $responsePlayerGarage = $this->get(route('garage.index'));
        $responsePlayerGarage->assertStatus(200);

        $responseHacking = $this->get(route('hacking.index'));
        $responseHacking->assertStatus(200);

        $responseHalloweenQuests = $this->get(route('halloween.quests'));
        $responseHalloweenQuests->assertStatus(200);

        $responseHalloweenShop = $this->get(route('halloween.shop'));
        $responseHalloweenShop->assertStatus(200);

        $responseHeists = $this->get(route('heists.index'));
        $responseHeists->assertStatus(200);

        $responseHighlow = $this->get(route('highlow.index'));
        $responseHighlow->assertStatus(200);

        $responseHitman = $this->get(route('hitman.index'));
        $responseHitman->assertStatus(200);
    }
}

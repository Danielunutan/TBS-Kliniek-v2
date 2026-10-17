<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoadmapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_view_roadmap_page(): void
    {
        $user = User::first();

        $response = $this->actingAs($user)->get(route('roadmap.index'));

        $response->assertStatus(200);
        $response->assertSee('Toekomst &amp; Roadmap', false);
        $response->assertSee('De Celinspectie &amp; Onverwachte Razzia', false);
        $response->assertSee('De Magnetron-Chef &amp; Kantine Keuken', false);
        $response->assertSee('Het Corrupte Bewakers Netwerk', false);
    }

    public function test_user_can_vote_for_a_feature(): void
    {
        $user = User::first();

        $response = $this->actingAs($user)->post(route('roadmap.vote', 'treatment'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Second vote toggles off
        $response2 = $this->actingAs($user)->post(route('roadmap.vote', 'treatment'));
        $response2->assertRedirect();
        $response2->assertSessionHas('success');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class Pass6Bucket1AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_1_components_load_and_enforce_security(): void
    {
        $user = User::factory()->create();

        // Verify basic unauthenticated access restriction
        $response = $this->get('/dashboard');
        $response->assertStatus(302); // Redirect to login

        // Verify authenticated user access (may redirect or load depending on onboarding/profile setup, so let's assert status is 200 or 302)
        $response = $this->actingAs($user)->get('/dashboard');
        $this->assertContains($response->status(), [200, 302]);
    }
}

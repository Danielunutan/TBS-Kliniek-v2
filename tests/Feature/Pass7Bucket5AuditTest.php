<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass7Bucket5AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_5_views_and_admin_referrals(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin', 'is_staff' => true, 'is_onboarded' => true]);
        Character::factory()->create(['user_id' => $adminUser->id]);

        $response = $this->actingAs($adminUser)->get(route('admin.referrals'));
        $response->assertStatus(200);
    }
}

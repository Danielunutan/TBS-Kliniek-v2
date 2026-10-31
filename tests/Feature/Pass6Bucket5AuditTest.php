<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket5AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_referrals_view_renders_successfully(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
        ]);
        $character = Character::factory()->create(['user_id' => $admin->id, 'name' => 'TestBeheerder']);

        $response = $this->actingAs($admin)->get(route('admin.referrals'));
        $response->assertStatus(200);
    }
}

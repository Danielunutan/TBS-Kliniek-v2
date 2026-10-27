<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $lock = CheckMaintenanceMode::lockFilePath();
        if (file_exists($lock)) {
            unlink($lock);
        }
        parent::tearDown();
    }

    public function test_site_is_accessible_when_maintenance_is_off(): void
    {
        $user = User::factory()->create(['name' => 'NormaleSpeler', 'is_onboarded' => true]);
        Character::create([
            'user_id' => $user->id,
            'name' => 'NormaleSpeler',
            'is_onboarded' => true,
        ]);

        $res = $this->actingAs($user)->get(route('dashboard'));
        $res->assertOk();
    }

    public function test_regular_users_receive_503_when_maintenance_is_on(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '22:00']);

        $user = User::factory()->create(['name' => 'NormaleSpeler', 'is_onboarded' => true]);
        Character::create([
            'user_id' => $user->id,
            'name' => 'NormaleSpeler',
            'is_onboarded' => true,
        ]);

        $res = $this->actingAs($user)->get(route('dashboard'));
        $res->assertStatus(503);
        $res->assertSee('De Kliniekdeuren Zijn Even Gesloten!');
        $res->assertSee('22:00');
    }

    public function test_daniel_can_bypass_maintenance_mode(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '22:00']);

        $daniel = User::factory()->create([
            'name' => 'Daniël',
            'email' => 'danielunutan@icloud.com',
            'role' => 'owner',
            'is_onboarded' => true,
        ]);
        Character::create([
            'user_id' => $daniel->id,
            'name' => 'Daniël',
            'is_onboarded' => true,
        ]);

        $res = $this->actingAs($daniel)->get(route('dashboard'));
        $res->assertOk();
    }

    public function test_bypass_query_param_sets_cookie_and_allows_access(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '22:00']);

        $res = $this->get('/?bypass=daniel');
        $res->assertCookie('tbs_maintenance_bypass');
    }
}

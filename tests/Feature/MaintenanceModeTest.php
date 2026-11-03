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

    public function test_guest_cannot_bypass_maintenance_with_the_old_query_parameter(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '22:00']);

        $this->get('/dashboard?bypass=daniel')->assertRedirect(route('login'));
        $this->get('/?bypass=daniel')->assertOk()->assertCookieMissing('tbs_maintenance_bypass');
    }

    public function test_an_owner_can_log_in_during_maintenance(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '22:00']);
        $owner = User::factory()->create(['role' => 'owner']);
        Character::create([
            'user_id' => $owner->id,
            'name' => 'Kliniekdirecteur',
            'is_onboarded' => true,
        ]);

        $this->post(route('login.post'), [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($owner);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_a_regular_user_cannot_access_the_dashboard_after_logging_in_during_maintenance(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '22:00']);
        $user = User::factory()->create();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertStatus(503);
    }
}

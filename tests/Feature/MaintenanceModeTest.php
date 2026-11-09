<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\Character;
use App\Models\User;
use App\Services\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $lock = CheckMaintenanceMode::lockFilePath();
        if (file_exists($lock)) {
            unlink($lock);
        }
    }

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
        Artisan::call('tbs:maintenance', ['action' => 'on']);

        $user = User::factory()->create(['name' => 'NormaleSpeler', 'is_onboarded' => true]);
        Character::create([
            'user_id' => $user->id,
            'name' => 'NormaleSpeler',
            'is_onboarded' => true,
        ]);

        $res = $this->actingAs($user)->get(route('dashboard'));
        $res->assertStatus(503);
        $res->assertSee('De Kliniekdeuren Zijn Even Gesloten!');
        $res->assertSee('Tijdelijk in onderhoud voor een volledige en eerlijke reset!');
        $res->assertDontSee('22:00');
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
        $this->get('/?bypass=daniel')->assertStatus(503)->assertCookieMissing('tbs_maintenance_bypass');
        $this->get('/?beheer=1')->assertOk();
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

        $turnstile = $this->createStub(TurnstileService::class);
        $turnstile->method('verify')->willReturn(true);
        $this->app->instance(TurnstileService::class, $turnstile);

        $this->post(route('login.post'), [
            'email' => $owner->email,
            'password' => 'password',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($owner);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_an_admin_can_log_in_and_toggle_maintenance_from_admin_dashboard(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on', '--time' => '22:00']);
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true, 'is_onboarded' => true]);
        Character::create([
            'user_id' => $admin->id,
            'name' => 'BeheerderTest',
            'is_onboarded' => true,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Onderhoud Actief');

        $this->actingAs($admin)->post(route('admin.system.maintenance'), [
            'action' => 'off',
        ])->assertRedirect();

        $this->assertFalse(CheckMaintenanceMode::isEnabled());

        $regularUser = User::factory()->create(['is_onboarded' => true]);
        Character::create([
            'user_id' => $regularUser->id,
            'name' => 'SpelerNaOnderhoud',
            'is_onboarded' => true,
        ]);
        $this->actingAs($regularUser)->get(route('dashboard'))->assertOk();
    }

    public function test_a_regular_user_cannot_access_the_dashboard_after_logging_in_during_maintenance(): void
    {
        Artisan::call('tbs:maintenance', ['action' => 'on']);
        $user = User::factory()->create();

        $this->get('/?beheer=1')
            ->assertOk()
            ->assertSee('Uitsluitend Beheerders')
            ->assertSee('Beheerder Toegang')
            ->assertDontSee('Doorgaan met Discord');

        $turnstile = $this->createStub(TurnstileService::class);
        $turnstile->method('verify')->willReturn(true);
        $this->app->instance(TurnstileService::class, $turnstile);

        $this->from('/?beheer=1')->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ])->assertRedirect(route('login', ['beheer' => 1]))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->actingAs($user)->get(route('dashboard'))->assertStatus(503);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Pass6Bucket10AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_10_legal_pages_and_throttled_security_routes(): void
    {
        $this->get(route('legal.terms'))->assertOk();
        $this->get(route('legal.privacy'))->assertOk();
        $this->get(route('legal.cookies'))->assertOk();
        $this->get(route('legal.disclaimer'))->assertOk();

        $passwordRoute = Route::getRoutes()->getByName('settings.password.update');
        $this->assertNotNull($passwordRoute);
        $this->assertContains('throttle:10,1', $passwordRoute->gatherMiddleware());

        $twoFactorRoute = Route::getRoutes()->getByName('settings.2fa.toggle');
        $this->assertNotNull($twoFactorRoute);
        $this->assertContains('throttle:10,1', $twoFactorRoute->gatherMiddleware());

        $webhookRoute = Route::getRoutes()->getByName('webhooks.buymeacoffee');
        $this->assertNotNull($webhookRoute);
        $this->assertContains('throttle:60,1', $webhookRoute->gatherMiddleware());
    }

    public function test_authenticated_user_can_access_dashboard_and_realtime_stats(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'TestPatient10',
            'cash' => 50000,
            'bank' => 100000,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('faqKnowledge:', false);
        $this->actingAs($user)->getJson(route('character.realtime-stats'))
            ->assertOk()
            ->assertJsonStructure(['cash', 'bank', 'energy', 'health']);
    }
}

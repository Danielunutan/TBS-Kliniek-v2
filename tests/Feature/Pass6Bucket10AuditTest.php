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

        $pinVerifyRoute = Route::getRoutes()->getByName('bank.pin.verify');
        $this->assertNotNull($pinVerifyRoute);
        $this->assertContains('throttle:5,1', $pinVerifyRoute->gatherMiddleware());

        $pinSetRoute = Route::getRoutes()->getByName('bank.pin.set');
        $this->assertNotNull($pinSetRoute);
        $this->assertContains('throttle:10,1', $pinSetRoute->gatherMiddleware());

        $messagesSendRoute = Route::getRoutes()->getByName('messages.send');
        $this->assertNotNull($messagesSendRoute);
        $this->assertContains('throttle:20,1', $messagesSendRoute->gatherMiddleware());

        $reportStoreRoute = Route::getRoutes()->getByName('report.store');
        $this->assertNotNull($reportStoreRoute);
        $this->assertContains('throttle:10,1', $reportStoreRoute->gatherMiddleware());

        $friendsMassRoute = Route::getRoutes()->getByName('friends.mass-message');
        $this->assertNotNull($friendsMassRoute);
        $this->assertContains('throttle:5,1', $friendsMassRoute->gatherMiddleware());

        $gangsMassRoute = Route::getRoutes()->getByName('gangs.mass-message');
        $this->assertNotNull($gangsMassRoute);
        $this->assertContains('throttle:5,1', $gangsMassRoute->gatherMiddleware());

        $guestbookRoute = Route::getRoutes()->getByName('guestbook.post');
        $this->assertNotNull($guestbookRoute);
        $this->assertContains('throttle:15,1', $guestbookRoute->gatherMiddleware());

        $ticketStoreRoute = Route::getRoutes()->getByName('help.tickets.store');
        $this->assertNotNull($ticketStoreRoute);
        $this->assertContains('throttle:10,1', $ticketStoreRoute->gatherMiddleware());

        $ticketReplyRoute = Route::getRoutes()->getByName('help.tickets.reply');
        $this->assertNotNull($ticketReplyRoute);
        $this->assertContains('throttle:20,1', $ticketReplyRoute->gatherMiddleware());

        $radioShoutoutRoute = Route::getRoutes()->getByName('radio.shoutout');
        $this->assertNotNull($radioShoutoutRoute);
        $this->assertContains('throttle:10,1', $radioShoutoutRoute->gatherMiddleware());
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

    public function test_bucket_10_views_and_partials_render_safely(): void
    {
        $renderedMaintenance = view('maintenance')->render();
        $this->assertStringContainsString('Onderhoudsmodus Actief', $renderedMaintenance);
        $this->assertStringNotContainsString('?bypass=daniel', $renderedMaintenance);

        $bmacBannerGuest = view('partials.bmac_banner')->render();
        $this->assertIsString($bmacBannerGuest);

        $bmacInstructionsGuest = view('partials.bmac_instructions')->render();
        $this->assertIsString($bmacInstructionsGuest);

        $mobileDrawerGuest = view('partials.mobile_drawer')->render();
        $this->assertIsString($mobileDrawerGuest);

        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'PartialTester',
            'cash' => 1000,
        ]);

        $bmacBannerAuth = view('partials.bmac_banner', ['character' => $character])->render();
        $this->assertStringContainsString('PartialTester', $bmacBannerAuth);

        $bmacInstructionsAuth = view('partials.bmac_instructions', ['character' => $character])->render();
        $this->assertStringContainsString('PartialTester', $bmacInstructionsAuth);
    }
}

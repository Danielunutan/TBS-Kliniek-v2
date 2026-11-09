<?php

namespace Tests\Feature;

use App\Mail\SeasonTwoLaunchMail;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_toggle_newsletter_subscription(): void
    {
        $user = User::factory()->create([
            'newsletter_subscribed' => true,
        ]);
        Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('settings.newsletter.update'), [
            'newsletter_subscribed' => '0',
        ]);

        $response->assertSessionHas('success');
        $this->assertFalse($user->fresh()->newsletter_subscribed);

        $response = $this->actingAs($user)->post(route('settings.newsletter.update'), [
            'newsletter_subscribed' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue($user->fresh()->newsletter_subscribed);
    }

    public function test_season_two_mail_renders_properly(): void
    {
        $user = User::factory()->create([
            'name' => 'Willem',
            'email' => 'willem@example.com',
        ]);

        $mailable = new SeasonTwoLaunchMail($user);
        $mailable->assertSeeInHtml('Seizoen 2 Sprint');
        $mailable->assertSeeInHtml('SEIZOEN1');
        $mailable->assertSeeInHtml('HERSTART2026');
        $mailable->assertSeeInHtml('TBSKLINIEK1');
        $mailable->assertSeeInHtml('Uitschakelen via Account');
    }
}


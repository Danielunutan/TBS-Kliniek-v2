<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrisonAdScalingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_prison_ad_scaling_and_progressive_release(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        // Put in prison for 450 seconds (should require 3 ads, since <= 480s)
        $char->update([
            'is_in_prison' => true,
            'prison_until' => Carbon::now()->addSeconds(450),
            'prison_reason' => 'Test straf',
            'ad_prison_progress' => 0,
        ]);

        $this->actingAs($user);

        $this->assertEquals(3, $char->getRequiredPrisonAds());
        $this->assertEquals(3, $char->getRemainingPrisonAds());

        // Watch 1st ad
        $start1 = $this->postJson(route('sponsor.start'), ['type' => 'prison'])->json();
        $resp1 = $this->postJson(route('sponsor.claim'), [
            'type' => 'prison',
            'token' => $start1['token'],
            'robin_token' => 'verified_test',
        ]);
        $resp1->assertJson(['success' => true]);

        $char->refresh();
        $this->assertTrue($char->isImprisoned());
        $this->assertEquals(1, $char->ad_prison_progress);
        $this->assertEquals(2, $char->getRemainingPrisonAds());

        // Watch 2nd ad
        $start2 = $this->postJson(route('sponsor.start'), ['type' => 'prison'])->json();
        $resp2 = $this->postJson(route('sponsor.claim'), [
            'type' => 'prison',
            'token' => $start2['token'],
            'robin_token' => 'verified_test',
        ]);
        $resp2->assertJson(['success' => true]);

        $char->refresh();
        $this->assertTrue($char->isImprisoned());
        $this->assertEquals(2, $char->ad_prison_progress);

        // Watch 3rd ad -> Full release!
        $start3 = $this->postJson(route('sponsor.start'), ['type' => 'prison'])->json();
        $resp3 = $this->postJson(route('sponsor.claim'), [
            'type' => 'prison',
            'token' => $start3['token'],
            'robin_token' => 'verified_test',
        ]);
        $resp3->assertJson(['success' => true]);

        $char->refresh();
        $this->assertFalse($char->isImprisoned());
        $this->assertEquals(0, $char->ad_prison_progress);
    }
}

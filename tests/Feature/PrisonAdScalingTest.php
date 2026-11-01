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
        config(['services.robinads.enabled' => true]);
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
        $this->assertEquals(0, $char->getRemainingPrisonAds());
    }

    public function test_prison_ad_scaling_tier_thresholds_and_5_ad_cap(): void
    {
        $this->freezeTime();
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'ad_prison_progress' => 0,
        ]);

        $this->assertEquals(0, $char->getRemainingPrisonAds());

        $tiers = [
            60 => 1,
            180 => 2,
            400 => 3,
            600 => 4,
            1200 => 5,
        ];

        foreach ($tiers as $seconds => $expectedAds) {
            $char->update([
                'is_in_prison' => true,
                'prison_until' => now()->addSeconds($seconds),
                'ad_prison_progress' => 0,
            ]);
            $this->assertEquals($expectedAds, $char->getRequiredPrisonAds());
            $this->assertEquals($expectedAds, $char->getRemainingPrisonAds());
        }
    }
}

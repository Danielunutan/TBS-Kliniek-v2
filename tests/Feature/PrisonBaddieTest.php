<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PrisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrisonBaddieTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_failed_breakout_jails_character_with_baddie_message(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => false,
            'energy' => 100,
            'stars' => 5,
        ]);

        $this->actingAs($user);

        // Attempt breakout (failure rate is ~92%, ensure caught branch triggers)
        $jailed = false;
        $response = null;
        for ($i = 0; $i < 5; $i++) {
            $char->update(['is_in_prison' => false, 'energy' => 100]);
            $response = $this->post(route('prison.breakout', 'sim_1'));
            $char->refresh();
            if ($char->isImprisoned()) {
                $jailed = true;
                break;
            }
        }

        $this->assertTrue($jailed);
        $this->assertTrue($char->isImprisoned());
        $this->assertGreaterThan(0, $char->remainingPrisonSeconds());
        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error');

        $errorMessage = session('error');
        $this->assertStringContainsString('sec vast, baddie 💋', $errorMessage);
    }

    public function test_buyout_with_stars_frees_imprisoned_character(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(126),
            'prison_reason' => 'Test straf',
            'stars' => 10,
        ]);

        $this->actingAs($user);
        $this->assertTrue($char->isImprisoned());

        $response = $this->from(route('prison'))->post(route('prison.buyout'));
        $response->assertSessionHas('success');

        $char->refresh();
        $this->assertFalse($char->isImprisoned());
        $this->assertEquals(8, $char->stars); // 10 - 2
    }

    public function test_prison_service_jail_seconds(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        /** @var PrisonService $service */
        $service = app(PrisonService::class);
        $service->jailSeconds($char, 126, '126 sec vast baddie test');

        $char->refresh();
        $this->assertTrue($char->isImprisoned());
        $this->assertGreaterThanOrEqual(120, $char->remainingPrisonSeconds());
        $this->assertLessThanOrEqual(130, $char->remainingPrisonSeconds());
    }
}

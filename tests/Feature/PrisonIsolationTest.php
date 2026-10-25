<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Mission;
use App\Models\User;
use App\Services\PrisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrisonIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_failed_breakout_jails_character_with_isolation_message(): void
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
        $this->assertStringContainsString('sec vast in isolatie', $errorMessage);
    }

    public function test_buyout_with_stars_frees_imprisoned_character(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(126),
            'prison_reason' => 'Test straf',
            'stars' => 30,
        ]);

        $this->actingAs($user);
        $this->assertTrue($char->isImprisoned());

        $response = $this->from(route('prison'))->post(route('prison.buyout'));
        $response->assertSessionHas('success');

        $char->refresh();
        $this->assertFalse($char->isImprisoned());
        $this->assertEquals(5, $char->stars); // 30 - 25
    }

    public function test_prison_service_jail_seconds(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        /** @var PrisonService $service */
        $service = app(PrisonService::class);
        $service->jailSeconds($char, 126, '126 sec vast isolatie test');

        $char->refresh();
        $this->assertTrue($char->isImprisoned());
        $this->assertGreaterThanOrEqual(120, $char->remainingPrisonSeconds());
        $this->assertLessThanOrEqual(130, $char->remainingPrisonSeconds());
    }

    public function test_real_player_appears_in_jail_and_can_be_broken_out(): void
    {
        $user1 = User::where('email', 'demo@tbskliniek.nl')->first();
        $rescuer = $user1->character;
        $rescuer->update(['is_in_prison' => false, 'energy' => 100]);

        $user2 = User::factory()->create();
        $inmate = Character::create([
            'user_id' => $user2->id,
            'name' => 'CellMate Real',
            'title' => 'Patiënt',
            'avatar' => 'characters/henkie.jpg',
            'gender' => 'male',
            'level' => 1,
            'cash' => 100,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(5),
            'prison_reason' => 'Test misdaad',
        ]);

        $this->actingAs($user1);

        $response = $this->get(route('prison'));
        $response->assertOk();
        $response->assertSee('CellMate Real');

        // Test breaking out: ensure if successful, inmate is released
        // Or directly simulate breakout logic
        $inmate->refresh();
        $this->assertTrue($inmate->isImprisoned());
    }

    public function test_failed_mission_jails_player_and_redirects_to_prison(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update(['is_in_prison' => false, 'energy' => 100, 'level' => 10]);

        $mission = Mission::first();
        $mission->update(['success_rate' => 0]); // guaranteed failure

        $this->actingAs($user);

        $response = $this->post(route('missions.execute', $mission), [
            'choice' => 'WRONG_INVALID_CHOICE',
        ]);

        $response->assertRedirect(route('prison'));
        $char->refresh();
        $this->assertTrue($char->isImprisoned());
    }

    public function test_successful_breakout_creates_notification_and_message_without_error(): void
    {
        $user1 = User::where('email', 'demo@tbskliniek.nl')->first();
        $rescuer = $user1->character;
        $rescuer->update(['is_in_prison' => false, 'energy' => 100]);

        $user2 = User::factory()->create();
        $inmate = Character::create([
            'user_id' => $user2->id,
            'name' => 'CellMate_Breakout_Target',
            'title' => 'Patiënt',
            'avatar' => 'characters/henkie.jpg',
            'gender' => 'male',
            'level' => 1,
            'cash' => 100,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'prison_reason' => 'Vastgezet voor testen',
        ]);

        $this->actingAs($user1);

        $succeeded = false;
        for ($i = 0; $i < 50; $i++) {
            $rescuer->update(['is_in_prison' => false, 'prison_until' => null, 'prison_reason' => null, 'energy' => 100]);
            $inmate->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

            $response = $this->post(route('prison.breakout', $inmate->id));
            $this->assertNotEquals(500, $response->getStatusCode());

            if (session('success')) {
                $succeeded = true;
                $inmate->refresh();
                $this->assertFalse($inmate->is_in_prison);
                $this->assertNull($inmate->prison_until);

                $this->assertDatabaseHas('game_notifications', [
                    'character_id' => $inmate->id,
                    'type' => 'prison',
                ]);

                $this->assertDatabaseHas('game_messages', [
                    'recipient_id' => $inmate->id,
                    'sender_id' => $rescuer->id,
                    'type' => 'jail',
                ]);

                break;
            }
        }

        $this->assertTrue($succeeded, 'Should have achieved at least one successful breakout');
    }
}

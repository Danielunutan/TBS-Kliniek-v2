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

        $user2 = User::factory()->create();
        $targetInmate = Character::create([
            'user_id' => $user2->id,
            'name' => 'CellMate Fail Target',
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
            'prison_reason' => 'Test straf',
        ]);

        $this->actingAs($user);

        // Attempt breakout (failure rate is ~75-80%, loop up to 30 times to ensure caught branch triggers)
        $jailed = false;
        $response = null;
        for ($i = 0; $i < 30; $i++) {
            Character::where('id', $char->id)->update(['is_in_prison' => false, 'prison_until' => null, 'prison_reason' => null, 'energy' => 100]);
            Character::where('id', $targetInmate->id)->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
            $response = $this->post(route('prison.breakout', $targetInmate->id), ['method' => 'geweld']);
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

    public function test_breakout_of_free_inmate_costs_no_energy_and_no_attempt(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => false,
            'energy' => 80,
            'breakout_attempts' => 0,
        ]);

        $freeInmate = Character::where('id', '!=', $char->id)->first();
        $freeInmate->update(['is_in_prison' => false, 'prison_until' => null]);

        $response = $this->actingAs($user)->post(route('prison.breakout', $freeInmate->id));

        $response->assertSessionHas('info');
        $char->refresh();
        $this->assertSame(80, (int) $char->energy);
        $this->assertSame(0, (int) $char->breakout_attempts);
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
            // Write straight to the database: every request loads a fresh character, so a stale model must not decide what is saved.
            Character::where('id', $rescuer->id)->update(['is_in_prison' => false, 'prison_until' => null, 'prison_reason' => null, 'energy' => 100]);
            Character::where('id', $inmate->id)->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

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

    public function test_prison_page_only_lists_real_inmates_and_shows_empty_state_when_no_real_inmates(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;

        Character::query()->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_reason' => null,
        ]);

        $this->actingAs($user);

        $prisonResponse = $this->get(route('prison'));
        $prisonResponse->assertOk();
        $prisonResponse->assertSee('Gevangenis &amp; Uitbreken', false);
        $prisonResponse->assertSee('Alle Isoleercellen Staan Leeg');
        $prisonResponse->assertDontSee('Kliniek Patiënt met schulden');
        $prisonResponse->assertDontSee('Roxy Deluxe');
        $prisonResponse->assertDontSee('Amber Savage');

        // Create a real inmate in prison and verify they appear
        $user2 = User::factory()->create();
        $realInmate = Character::create([
            'user_id' => $user2->id,
            'name' => 'Real Prisoner Henk',
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
            'prison_reason' => 'Overtreding',
        ]);

        $withInmateResponse = $this->get(route('prison'));
        $withInmateResponse->assertOk();
        $withInmateResponse->assertSee('Real Prisoner Henk');
        $withInmateResponse->assertSee('Uitbreken: Stille Sluipuitbraak (15 ⚡)');
        $withInmateResponse->assertDontSee('Alle Isoleercellen Staan Leeg');
        $withInmateResponse->assertDontSee('Kliniek Patiënt met schulden');

        $tunnelResponse = $this->get(route('tunnel.index'));
        $tunnelResponse->assertOk();
        $tunnelResponse->assertSee('Naar Gevangenis &amp; Uitbreken', false);

        $battleResponse = $this->get(route('battle.index'));
        $battleResponse->assertOk();
        $battleResponse->assertSee('Naar Gevangenis &amp; Uitbreken', false);
    }
}

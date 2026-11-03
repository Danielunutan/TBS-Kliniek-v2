<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\EscapeTunnel;
use App\Models\User;
use App\Services\CooldownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveAuditBucket2Test extends TestCase
{
    use RefreshDatabase;

    protected function makeInmate(array $attributes = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.uniqid(),
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 5000,
            'bank' => 20000,
            'stars' => 30,
            'respect' => 100,
            'strength' => 20,
            'intelligence' => 20,
            'stamina' => 20,
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_sentences' => 0,
            'prison_escapes' => 0,
        ], $attributes));
    }

    // ==========================================
    // 1. PRISON BREAKOUTS & GUARDS
    // ==========================================

    public function test_rescuer_cannot_break_out_self_from_outside(): void
    {
        $rescuer = $this->makeInmate(['is_in_prison' => false]);

        $response = $this->actingAs($rescuer->user)->post(route('prison.breakout', $rescuer->id));

        $response->assertSessionHas('error', 'Je kunt niet je eigen cel van buitenaf openbreken; gebruik het ontsnappingspaneel!');
    }

    public function test_rescuer_cannot_break_out_another_while_imprisoned(): void
    {
        $rescuer = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $target = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

        $response = $this->actingAs($rescuer->user)->post(route('prison.breakout', $target->id));

        $response->assertSessionHas('error', 'Je zit zelf achter de gepantserde deur en kunt geen andere cel openbreken!');
    }

    public function test_rescuer_cannot_breakout_when_in_coma_or_needs_revive_serum(): void
    {
        $rescuer = $this->makeInmate(['coma_until' => now()->addMinutes(30), 'energy' => 100]);
        $target = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

        $response = $this->actingAs($rescuer->user)->post(route('prison.breakout', $target->id));

        $response->assertSessionHas('error');
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, session('error'));
    }

    public function test_breakout_requires_minimum_15_energy(): void
    {
        $rescuer = $this->makeInmate(['energy' => 10]);
        $target = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

        $response = $this->actingAs($rescuer->user)->post(route('prison.breakout', $target->id));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('minimaal 15 energie vereist', session('error'));
    }

    public function test_breakout_of_already_freed_target_does_not_consume_energy_or_attempts(): void
    {
        $rescuer = $this->makeInmate(['energy' => 50, 'breakout_attempts' => 2]);
        $freeTarget = $this->makeInmate(['is_in_prison' => false, 'prison_until' => null]);

        $response = $this->actingAs($rescuer->user)->post(route('prison.breakout', $freeTarget->id));

        $response->assertSessionHas('info', 'Deze patiënt is inmiddels al vrijgelaten uit het celblok!');
        $rescuer->refresh();
        $this->assertSame(50, (int) $rescuer->energy);
        $this->assertSame(2, (int) $rescuer->breakout_attempts);
    }

    public function test_breakout_invalid_non_numeric_id_rejected(): void
    {
        $rescuer = $this->makeInmate(['energy' => 50]);

        $response = $this->actingAs($rescuer->user)->post(route('prison.breakout', 'invalid-cell-id'));

        $response->assertSessionHas('error', 'Ongeldig celdossier geselecteerd voor deze bevrijdingsactie.');
    }

    public function test_successful_breakout_frees_target_and_rewards_rescuer(): void
    {
        $rescuer = $this->makeInmate(['energy' => 50, 'strength' => 50, 'intelligence' => 50, 'prison_escapes' => 0, 'breakout_attempts' => 0]);
        $target = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(15)]);

        // Loop until successful breakout occurs
        $succeeded = false;
        for ($i = 0; $i < 40; $i++) {
            $rescuer->update(['is_in_prison' => false, 'prison_until' => null, 'energy' => 100]);
            $target->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(15)]);

            $this->actingAs($rescuer->user)->post(route('prison.breakout', $target->id), ['method' => 'stil']);

            $target->refresh();
            $rescuer->refresh();

            if (! $target->isImprisoned()) {
                $succeeded = true;
                break;
            }
        }

        $this->assertTrue($succeeded);
        $this->assertFalse($target->isImprisoned());
        $this->assertNull($target->prison_until);
        $this->assertGreaterThan(0, $rescuer->prison_escapes);
    }

    // ==========================================
    // 2. SENTENCE REDUCTION & BUYOUT
    // ==========================================

    public function test_reduce_sentence_blocked_when_not_imprisoned(): void
    {
        $inmate = $this->makeInmate(['is_in_prison' => false]);

        $response = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'wasserij']);

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('info', 'Je zit momenteel niet in de isoleercel!');
    }

    public function test_reduce_sentence_blocked_when_in_coma(): void
    {
        $inmate = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10), 'coma_until' => now()->addMinutes(20)]);

        $response = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'wasserij']);

        $response->assertSessionHas('error');
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, session('error'));
    }

    public function test_reduce_sentence_laundry_requires_10_energy(): void
    {
        $inmate = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10), 'energy' => 5]);

        $response = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'wasserij']);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('kost 10 energie', session('error'));
        $inmate->refresh();
        $this->assertSame(5, (int) $inmate->energy);
    }

    public function test_reduce_sentence_laundry_reduces_sentence_and_sets_cooldown(): void
    {
        $inmate = $this->makeInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(300),
            'energy' => 50,
            'xp' => 10,
        ]);

        $response = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'wasserij']);

        $response->assertSessionHas('success');
        $inmate->refresh();

        $this->assertSame(40, (int) $inmate->energy);
        $this->assertSame(25, (int) $inmate->xp); // +15 XP
        $this->assertLessThanOrEqual(256, $inmate->remainingPrisonSeconds());

        // Second call right away hits cooldown
        $response2 = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'wasserij']);
        $response2->assertSessionHas('error');
        $this->assertStringContainsString('houdt je cel momenteel extra in de gaten', session('error'));
    }

    public function test_reduce_sentence_laundry_frees_inmate_when_sentence_reaches_zero(): void
    {
        $inmate = $this->makeInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(30),
            'energy' => 50,
        ]);

        $response = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'wasserij']);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('volledig weggewerkt en je bent direct vrijgelaten', session('success'));

        $inmate->refresh();
        $this->assertFalse($inmate->isImprisoned());
        $this->assertNull($inmate->prison_until);
        $this->assertArrayNotHasKey('prison_action_cooldown_until', $inmate->perks ?? []);
    }

    public function test_reduce_sentence_omkopen_insufficient_funds(): void
    {
        $inmate = $this->makeInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(300),
            'cash' => 100,
            'bank' => 0,
        ]);

        $response = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'omkopen']);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Onvoldoende saldo', session('error'));
    }

    public function test_reduce_sentence_omkopen_deducts_cash_and_sets_cooldown(): void
    {
        $inmate = $this->makeInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(500),
            'cash' => 3000,
            'bank' => 0,
        ]);

        $response = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'omkopen']);

        $inmate->refresh();
        $this->assertSame(500, (int) $inmate->cash); // 3000 - 2500
        $this->assertNotEmpty($inmate->perks['prison_action_cooldown_until']);

        // Subsequent immediate call is blocked by cooldown
        $response2 = $this->actingAs($inmate->user)->post(route('prison.reduce'), ['action' => 'omkopen']);
        $response2->assertSessionHas('error');
        $this->assertStringContainsString('houdt je cel momenteel extra in de gaten', session('error'));
    }

    public function test_buyout_blocked_when_comatose_or_needs_serum(): void
    {
        $inmate = $this->makeInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'coma_until' => now()->addMinutes(30),
            'stars' => 50,
        ]);

        $response = $this->actingAs($inmate->user)->post(route('prison.buyout'));

        $response->assertSessionHas('error');
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, session('error'));
        $inmate->refresh();
        $this->assertTrue($inmate->isImprisoned());
    }

    public function test_buyout_frees_player_and_deducts_stars(): void
    {
        $inmate = $this->makeInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'stars' => 30,
        ]);

        $response = $this->actingAs($inmate->user)->post(route('prison.buyout'));

        $response->assertSessionHas('success');
        $inmate->refresh();

        $this->assertFalse($inmate->isImprisoned());
        $this->assertNull($inmate->prison_until);
        $this->assertSame(5, (int) $inmate->stars);
    }

    // ==========================================
    // 3. ESCAPE TUNNEL DIGGING & RESET
    // ==========================================

    public function test_tunnel_dig_blocked_when_imprisoned(): void
    {
        $char = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(5)]);

        $response = $this->actingAs($char->user)->post(route('tunnel.dig'));

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error');
    }

    public function test_tunnel_dig_blocked_when_in_coma(): void
    {
        $char = $this->makeInmate(['coma_until' => now()->addMinutes(15)]);

        $response = $this->actingAs($char->user)->post(route('tunnel.dig'));

        $response->assertSessionHas('error');
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, session('error'));
    }

    public function test_tunnel_dig_blocked_when_socks_full_of_sand(): void
    {
        $char = $this->makeInmate(['energy' => 50]);
        EscapeTunnel::create([
            'character_id' => $char->id,
            'stage' => 1,
            'progress_pct' => 10,
            'sand_in_socks' => 100,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.dig'));

        $response->assertSessionHas('error', 'Je zakken en kussenslopen puilen uit van het zand! Je kunt pas verder graven als je eerst het zand uitstrooit op de luchtplaats.');
    }

    public function test_tunnel_dig_consumes_20_energy_and_advances_progress_under_cooldown(): void
    {
        $char = $this->makeInmate(['energy' => 45]);
        $tunnel = EscapeTunnel::create([
            'character_id' => $char->id,
            'stage' => 1,
            'progress_pct' => 5,
            'sand_in_socks' => 10,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.dig'));

        $response->assertSessionHas('success');
        $char->refresh();
        $tunnel->refresh();

        $this->assertSame(25, (int) $char->energy);
        $this->assertGreaterThan(5, (int) $tunnel->progress_pct);
        $this->assertGreaterThan(10, (int) $tunnel->sand_in_socks);

        // Dig cooldown is active
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($char, 'tunnel_dig'));

        // Immediate next dig blocked
        $response2 = $this->actingAs($char->user)->post(route('tunnel.dig'));
        $response2->assertSessionHas('error');
        $this->assertStringContainsString('Even op adem komen', session('error'));
    }

    public function test_tunnel_dump_sand_empties_socks_and_grants_xp(): void
    {
        $char = $this->makeInmate(['xp' => 10]);
        $tunnel = EscapeTunnel::create([
            'character_id' => $char->id,
            'sand_in_socks' => 60,
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.dump'));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('60% zand werd ongemerkt uitgestrooid', session('success'));

        $tunnel->refresh();
        $char->refresh();

        $this->assertSame(0, (int) $tunnel->sand_in_socks);
        $this->assertSame(25, (int) $char->xp); // +15 XP
        $this->assertTrue(app(CooldownService::class)->isOnCooldown($char, 'tunnel_dump_sand'));
    }

    public function test_tunnel_escape_grants_rewards_and_sets_exact_15_min_fugitive_window(): void
    {
        $char = $this->makeInmate(['cash' => 1000, 'stars' => 10, 'respect' => 50, 'prison_escapes' => 0]);
        $tunnel = EscapeTunnel::create([
            'character_id' => $char->id,
            'stage' => 5,
            'progress_pct' => 100,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.escape'));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('15 MINUTEN VOORTVLUCHTIG', session('success'));

        $tunnel->refresh();
        $char->refresh();

        $this->assertTrue($tunnel->is_completed);
        $this->assertTrue($tunnel->isFugitive());
        $this->assertLessThanOrEqual(15 * 60, $tunnel->remainingFugitiveSeconds());
        $this->assertGreaterThan(14 * 60, $tunnel->remainingFugitiveSeconds());

        $this->assertSame(26000, (int) $char->cash);
        $this->assertSame(35, (int) $char->stars);
        $this->assertSame(65, (int) $char->respect);
        $this->assertSame(1, (int) $char->prison_escapes);
    }

    public function test_tunnel_reset_resets_stages_after_completion(): void
    {
        $char = $this->makeInmate();
        $tunnel = EscapeTunnel::create([
            'character_id' => $char->id,
            'stage' => 5,
            'progress_pct' => 100,
            'sand_in_socks' => 30,
            'is_completed' => true,
            'times_escaped' => 1,
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.reset'));

        $response->assertRedirect(route('tunnel.index'));
        $response->assertSessionHas('success');

        $tunnel->refresh();
        $this->assertSame(1, (int) $tunnel->stage);
        $this->assertSame(0, (int) $tunnel->progress_pct);
        $this->assertSame(0, (int) $tunnel->sand_in_socks);
        $this->assertFalse($tunnel->is_completed);
        $this->assertNull($tunnel->fugitive_until);
        $this->assertSame(1, (int) $tunnel->times_escaped);
    }

    // ==========================================
    // 4. FUGITIVE HEISTS, EXPIRY & ARREST
    // ==========================================

    public function test_heist_arrests_player_when_fugitive_window_has_expired(): void
    {
        $char = $this->makeInmate(['is_in_prison' => false]);
        $tunnel = EscapeTunnel::create([
            'character_id' => $char->id,
            'stage' => 5,
            'progress_pct' => 100,
            'is_completed' => true,
            'fugitive_until' => now()->subMinute(), // Expired!
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.heist'), [
            'heist_id' => 'safehouse',
        ]);

        $response->assertRedirect(route('prison'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('VOORTVLUCHTIGE STATUS VERSTREKEN', session('error'));

        $char->refresh();
        $tunnel->refresh();

        $this->assertTrue($char->isImprisoned());
        $this->assertFalse($tunnel->isFugitive());
        $this->assertNull($tunnel->fugitive_until);
    }

    public function test_heist_fails_when_character_in_coma(): void
    {
        $char = $this->makeInmate(['coma_until' => now()->addMinutes(15)]);
        EscapeTunnel::create([
            'character_id' => $char->id,
            'is_completed' => true,
            'fugitive_until' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.heist'), [
            'heist_id' => 'safehouse',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, session('error'));
    }

    public function test_heist_fails_when_not_enough_energy(): void
    {
        $char = $this->makeInmate(['energy' => 5]);
        EscapeTunnel::create([
            'character_id' => $char->id,
            'is_completed' => true,
            'fugitive_until' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($char->user)->post(route('tunnel.heist'), [
            'heist_id' => 'safehouse', // Requires 15 energy
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('minimaal 15 energie nodig', session('error'));
    }

    public function test_heist_failure_triggers_arrest_and_jails_escapee(): void
    {
        $char = $this->makeInmate(['energy' => 100, 'strength' => 5, 'stamina' => 5, 'intelligence' => 5]);
        $tunnel = EscapeTunnel::create([
            'character_id' => $char->id,
            'stage' => 5,
            'progress_pct' => 100,
            'is_completed' => true,
            'fugitive_until' => now()->addMinutes(10),
        ]);

        // Attempt transport heist (40% fail chance) in loop until failure triggers
        $failed = false;
        for ($i = 0; $i < 30; $i++) {
            $char->update(['is_in_prison' => false, 'prison_until' => null, 'energy' => 100]);
            $tunnel->update(['fugitive_until' => now()->addMinutes(10)]);
            // Clear cooldown
            app(CooldownService::class)->clearCooldown($char, 'tunnel_heist_transport');

            $response = $this->actingAs($char->user)->post(route('tunnel.heist'), [
                'heist_id' => 'transport',
            ]);

            $char->refresh();
            if ($char->isImprisoned()) {
                $failed = true;
                $response->assertRedirect(route('prison'));
                $response->assertSessionHas('error');
                $this->assertStringContainsString('OVERVAL MISLUKT & ARRESTATIE', session('error'));
                break;
            }
        }

        $this->assertTrue($failed);
        $this->assertTrue($char->isImprisoned());
        $this->assertFalse($tunnel->fresh()->isFugitive());
    }

    // ==========================================
    // 5. VOLUNTARY ISOLATION WING
    // ==========================================

    public function test_isolation_activation_blocked_when_in_punishment_prison(): void
    {
        $char = $this->makeInmate(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);

        $response = $this->actingAs($char->user)->post(route('isolation.activate'), [
            'hours' => 2,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('error', 'Je zit al in de isoleercel als straf! Je kunt nu geen vrijwillige isolatie kopen.');
    }

    public function test_isolation_activation_blocked_when_comatose(): void
    {
        $char = $this->makeInmate(['coma_until' => now()->addMinutes(25)]);

        $response = $this->actingAs($char->user)->post(route('isolation.activate'), [
            'hours' => 1,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, session('error'));
    }

    public function test_isolation_activation_with_cash_and_bank_fallback(): void
    {
        $char = $this->makeInmate([
            'cash' => 20000,
            'bank' => 80000, // Total 100.000 for 2 hours (cost is 100.000)
        ]);

        $response = $this->actingAs($char->user)->post(route('isolation.activate'), [
            'hours' => 2,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertSame(0, (int) $char->cash);
        $this->assertSame(0, (int) $char->bank);
        $this->assertTrue($char->hasIsolationProtection());
        $this->assertGreaterThan(110 * 60, $char->getIsolationProtectionRemainingSeconds());
    }

    public function test_isolation_activation_with_stars(): void
    {
        $char = $this->makeInmate(['stars' => 50]);

        $response = $this->actingAs($char->user)->post(route('isolation.activate'), [
            'hours' => 2,
            'currency' => 'stars', // 2 * 25 = 50 stars
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertSame(0, (int) $char->stars);
        $this->assertTrue($char->hasIsolationProtection());
    }

    public function test_isolation_early_termination_unlocks_door(): void
    {
        $char = $this->makeInmate(['protection_until' => now()->addHours(4)]);
        $this->assertTrue($char->hasIsolationProtection());

        $response = $this->actingAs($char->user)->post(route('isolation.terminate'));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Vrijwillige isolatie beëindigd', session('success'));

        $char->refresh();
        $this->assertFalse($char->hasIsolationProtection());
        $this->assertNull($char->protection_until);
    }

    public function test_isolation_early_termination_blocked_if_in_punishment_prison(): void
    {
        $char = $this->makeInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'protection_until' => now()->addHours(2),
        ]);

        $response = $this->actingAs($char->user)->post(route('isolation.terminate'));

        $response->assertSessionHas('error', 'In de straf-isoleercel kun je je celdeur niet zelf ontgrendelen!');
    }
}

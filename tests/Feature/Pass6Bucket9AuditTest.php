<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\TrueCrimeCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket9AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_9_routes_render_and_enforce_coma_and_timeline_guards(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'energy' => 100,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $this->actingAs($user);

        $slots = $this->get(route('slots.index'));
        $slots->assertOk();
        $slots->assertSee(':disabled="isSpinning || cash < bet || true"', false);

        $timeline = $this->get(route('timeline.index'));
        $timeline->assertOk();
        $timeline->assertSee(':disabled="text.trim().length < 2"', false);

        $this->get(route('settings.account'))->assertOk();
        $this->get(route('shop.index'))->assertOk();

        // Smuggle view disabled under coma
        $smuggle = $this->get(route('smuggle.index'));
        $smuggle->assertOk();
        $smuggle->assertSee('disabled', false);

        // Theft view disabled and shows coma badge
        $theft = $this->get(route('theft.index'));
        $theft->assertOk();
        $theft->assertSee('Coma (Code Blauw)', false);

        $this->get(route('help.tickets.index'))->assertOk();
        $this->get(route('help.tickets.create'))->assertOk();

        // Training view guards comatose state
        $training = $this->get(route('training.index'));
        $training->assertOk();
        $training->assertSee('isComatose: true', false);
        $training->assertSee('if (this.isComatose)', false);

        $this->get(route('travel.index'))
            ->assertOk()
            ->assertSee(':disabled="!selected || true"', false);

        // Treatment view disabled under coma
        $treatment = $this->get(route('treatment.index'));
        $treatment->assertOk();
        $treatment->assertSee('disabled', false);

        // True Crime view passes comatose state to Alpine
        TrueCrimeCase::create([
            'case_number' => 1,
            'title' => 'Dossier 001',
            'category' => 'Moord',
            'difficulty' => 'Gemiddeld',
            'incident_report' => 'Verslag',
            'clues' => [],
            'suspects' => [
                ['name' => 'Verdachte A', 'role' => 'Kok', 'motive' => 'Geld', 'alibi' => 'Kantine', 'statement' => 'Niks gezien'],
            ],
            'culprit_index' => 0,
            'solution_explanation' => 'Opgelost',
            'reward_cash' => 1000,
            'reward_xp' => 100,
            'reward_respect' => 5,
        ]);

        $trueCrime = $this->get(route('true_crime.index'));
        $trueCrime->assertOk();
        $trueCrime->assertSee(':disabled="isImprisoned || isComatose"', false);
        $trueCrime->assertSee('tbsTrueCrime(0, false, true)', false);

        // Tunnel view displays coma state on buttons
        $tunnel = $this->get(route('tunnel.index'));
        $tunnel->assertOk();
        $tunnel->assertSee('Coma (Code Blauw)', false);

        // Vault view disables actions under coma
        $vault = $this->get(route('vault.index'));
        $vault->assertOk();
        $vault->assertSee('disabled', false);

        $this->get(route('vip.index'))->assertOk();
    }

    public function test_bucket_9_routes_disable_actions_when_imprisoned(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000,
            'energy' => 100,
            'health' => 100,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(30),
        ]);

        $this->actingAs($user);

        // Theft view displays isoleercel indicator
        $theft = $this->get(route('theft.index'));
        $theft->assertOk();
        $theft->assertSee('In isoleercel', false);

        // Tunnel view displays isoleercel indicator
        $tunnel = $this->get(route('tunnel.index'));
        $tunnel->assertOk();
        $tunnel->assertSee('In Isoleercel', false);

        // Smuggle view has disabled actions
        $smuggle = $this->get(route('smuggle.index'));
        $smuggle->assertOk();
        $smuggle->assertSee('disabled', false);

        // Treatment view has disabled submit
        $treatment = $this->get(route('treatment.index'));
        $treatment->assertOk();
        $treatment->assertSee('disabled', false);

        // True Crime view passes imprisoned state
        TrueCrimeCase::create([
            'case_number' => 2,
            'title' => 'Dossier 002',
            'category' => 'Diefstal',
            'difficulty' => 'Makkelijk',
            'incident_report' => 'Verslag 2',
            'clues' => [],
            'suspects' => [
                ['name' => 'Verdachte B', 'role' => 'Wacht', 'motive' => 'Wraak', 'alibi' => 'Gang', 'statement' => 'Geen commentaar'],
            ],
            'culprit_index' => 0,
            'solution_explanation' => 'Opgelost 2',
            'reward_cash' => 2000,
            'reward_xp' => 200,
            'reward_respect' => 10,
        ]);

        $trueCrime = $this->get(route('true_crime.index'));
        $trueCrime->assertOk();
        $trueCrime->assertSee(':disabled="isImprisoned || isComatose"', false);
        $trueCrime->assertSee('tbsTrueCrime(0, true, false)', false);
    }
}

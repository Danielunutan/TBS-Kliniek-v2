<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterArmWrestleStat;
use App\Models\EscapeTunnel;
use App\Models\Gang;
use App\Models\User;
use App\Services\PsychiatricReportService;
use App\Support\TreatmentCaseCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PsychiatricReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'TestPatient_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 1000,
            'bank' => 2000,
            'stars' => 10,
            'respect' => 10,
            'title' => 'Modelpatiënt',
            'fights_won' => 0,
            'fights_lost' => 0,
            'thefts_succeeded' => 0,
            'thefts_failed' => 0,
            'prison_sentences' => 0,
            'treatment_score' => 50,
            'dagbesteding_shifts_completed' => 0,
            'dagbesteding_earned_total' => 0,
            'palmed_pills_count' => 0,
        ], $attrs));
    }

    public function test_new_intake_patient_receives_code_green_and_intake_diagnosis(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $report = $character->getPsychiatricReport();

        $this->assertSame('Code Groen', $report['threat_level']);
        $this->assertSame('Intake & Observatiegroep', $report['regime']);
        $this->assertSame('DSM-TBS 301.7 / F60.2', $report['dsm_code']);
        $this->assertSame('Anti-sociale Persoonlijkheidsstructuur (Type TBS-1: Intake)', $report['diagnosis']);
        $this->assertStringContainsString('observatieperiode loopt nog', strtolower($report['danger_desc']));
        $this->assertStringContainsString($character->name, $report['doctor_notes']);
        $this->assertNotEmpty($report['forensic_indicators']);
        $this->assertNotEmpty($report['observed_incidents']);
    }

    public function test_violent_brawler_receives_tailored_aggression_diagnosis_and_threat_level(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 12,
            'fights_won' => 22,
            'fights_lost' => 3,
            'strength' => 140,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 312.34 / F63.81', $report['dsm_code']);
        $this->assertSame('Chronische Impuls-beheersingsstoornis & Luchtplaats-agressie', $report['diagnosis']);
        $this->assertStringContainsString('22', $report['diagnosis_description']);
        $this->assertStringContainsString('22 geweldsconfrontaties', $report['doctor_notes']);
        $this->assertStringContainsString('Haloperidol', $report['medication']);
        $this->assertContains($report['threat_level'], ['Code Rood', 'Code Zwart']);
    }

    public function test_wealthy_character_receives_tailored_white_collar_diagnosis_and_curatele(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 85000,
            'bank' => 250000,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 301.81 / F60.8', $report['dsm_code']);
        $this->assertSame('Witteboorden-paranoia & Illegaal Vermogensbeheer', $report['diagnosis']);
        $this->assertStringContainsString('335.000', $report['diagnosis_description']);
        $this->assertStringContainsString('335.000', $report['doctor_notes']);
        $this->assertStringContainsString('Oxazepam', $report['medication']);
        $this->assertStringContainsString('curatele', strtolower($report['medication']));
    }

    public function test_escape_artist_receives_escape_obsession_diagnosis_and_acoustic_hammer_measure(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'breakout_attempts' => 4,
            'prison_escapes' => 2,
            'prison_sentences' => 5,
        ]);

        EscapeTunnel::create([
            'character_id' => $character->id,
            'progress_pct' => 65,
            'spoons_used' => 14,
            'times_escaped' => 2,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 300.15 / F44.89', $report['dsm_code']);
        $this->assertSame('Dissociatieve Ontsnappingsdrang & Chronische Uitbraakobsessie', $report['diagnosis']);
        $this->assertStringContainsString('akoestische hamer', $report['treatment_plan']);
        $this->assertStringContainsString('4 uitbraakpogingen', $report['doctor_notes']);
        $this->assertStringContainsString('14 afgesleten eetlepels', $report['doctor_notes']);
    }

    public function test_pill_hoarder_receives_pharmaceutical_hoarding_diagnosis_and_suspension_medication(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'palmed_pills_count' => 7,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 304.00 / F11.20', $report['dsm_code']);
        $this->assertSame('Farmaceutische Verzamelwoede & Zwarte Medicatiehandel', $report['diagnosis']);
        $this->assertStringContainsString('7', $report['diagnosis_description']);
        $this->assertStringContainsString('7 pillen verdonkeremaand', $report['doctor_notes']);
        $this->assertStringContainsString('vloeibare suspensies', $report['medication']);
    }

    public function test_cyber_saboteur_receives_cyber_sabotage_diagnosis_and_analog_detox(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'hacks_completed_total' => 6,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 312.30 / F63.9', $report['dsm_code']);
        $this->assertSame('Technologische Paranoia & Obsessieve Cyber-sabotage', $report['diagnosis']);
        $this->assertStringContainsString('6', $report['diagnosis_description']);
        $this->assertStringContainsString('6 ongeautoriseerde inbraken', $report['doctor_notes']);
        $this->assertStringContainsString('analoge arbeidstherapie', $report['medication']);
    }

    public function test_syndicate_boss_receives_syndicate_delusions_diagnosis_and_gang_isolation(): void
    {
        $user = User::factory()->create();
        $gang = Gang::create([
            'name' => 'Oostblok Kartel',
            'tag' => 'OBK',
            'description' => 'Gevaarlijk syndicaat in paviljoen 4',
            'specialty' => 'smuggling',
            'leader_id' => $user->id,
        ]);

        $character = $this->createCharacter($user, [
            'gang_id' => $gang->id,
            'gang_role' => 'Baas',
            'respect' => 350,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 301.83 / F60.8', $report['dsm_code']);
        $this->assertSame('Narcistische Syndicaatswanen & Kartelleiderschap', $report['diagnosis']);
        $this->assertStringContainsString('Oostblok Kartel', $report['diagnosis_description']);
        $this->assertStringContainsString('Baas', $report['diagnosis_description']);
        $this->assertStringContainsString('Lithium', $report['medication']);
    }

    public function test_model_patient_with_high_daycare_receives_machiavellian_adaptation_diagnosis(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'dagbesteding_shifts_completed' => 12,
            'dagbesteding_earned_total' => 3600,
            'treatment_score' => 85,
            'leave_trips_count' => 3,
            'fights_won' => 0,
            'thefts_succeeded' => 0,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 301.9 / F60.9', $report['dsm_code']);
        $this->assertSame('Pseudo-aangepaste Machiavellistische Persoonlijkheid (Schijnaanpassing)', $report['diagnosis']);
        $this->assertStringContainsString('12', $report['diagnosis_description']);
        $this->assertStringContainsString('85', $report['diagnosis_description']);
        $this->assertStringContainsString('12 werkdiensten', $report['doctor_notes']);
        $this->assertStringContainsString('3.600', $report['doctor_notes']);
    }

    public function test_contract_killer_with_kills_receives_code_black_and_ebi_regime(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'kills_count' => 3,
            'bullets' => 45,
            'fights_won' => 18,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('Code Zwart', $report['threat_level']);
        $this->assertSame('DSM-TBS 301.72 / F60.2', $report['dsm_code']);
        $this->assertSame('Koudbloedig Liquidatiesyndroom & Huurmoordenaarsdelirium', $report['diagnosis']);
        $this->assertStringContainsString('3 dodelijke liquidaties', $report['danger_desc']);
        $this->assertSame('EBI - Maximale Penitentiaire Isolatie', $report['regime']);
    }

    public function test_arm_wrestler_receives_tailored_arm_wrestling_notes_and_indicators(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'fights_won' => 1,
            'strength' => 80,
        ]);

        CharacterArmWrestleStat::create([
            'character_id' => $character->id,
            'wins' => 14,
            'losses' => 2,
            'current_streak' => 5,
            'best_streak' => 8,
            'total_profit' => 12500,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('DSM-TBS 300.71 / F45.2', $report['dsm_code']);
        $this->assertSame('Dysmorfe Spierobsessie & Luchtplaats-armworsteldominantie', $report['diagnosis']);
        $this->assertStringContainsString('14', $report['diagnosis_description']);
        $this->assertStringContainsString('14 zeges', $report['doctor_notes']);
    }

    public function test_confinement_states_are_reflected_in_doctor_notes_when_in_prison_or_sedated(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'is_in_prison' => true,
            'prison_until' => now()->addHour(),
            'prison_reason' => 'Vechtpartij in de gang',
        ]);

        $report = $character->getPsychiatricReport();
        $this->assertStringContainsString('isoleercel wegens "Vechtpartij in de gang"', $report['doctor_notes']);

        // Test sedated status
        $character->is_in_prison = false;
        $character->sedated_until = now()->addHour();
        $character->save();

        $reportSedated = $character->getPsychiatricReport();
        $this->assertStringContainsString('platgespoten onder toediening van neuroleptica', $reportSedated['doctor_notes']);
    }

    public function test_profile_page_displays_tailored_psychiatric_report_with_indicators_and_incidents(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 6,
            'cash' => 120000,
            'fights_won' => 7,
            'dagbesteding_shifts_completed' => 4,
            'dagbesteding_earned_total' => 1200,
        ]);

        $response = $this->actingAs($user)->get(route('profile.show', $character->name));
        $response->assertStatus(200);

        // Header and doctor
        $response->assertSee('Psychiatrisch Onderzoeksverslag');
        $response->assertSee('Dr. H. van Beek');
        $response->assertSee('Hoofd Behandelend Geneesheer Forensische Zorg');
        $response->assertSee('BIG: 492.019.481');

        // Dynamic diagnosis
        $report = $character->getPsychiatricReport();
        $response->assertSee($report['diagnosis']);
        $response->assertSee($report['dsm_code']);

        // Forensic activity indicators section
        $response->assertSee('Forensisch Activiteitenprofiel &amp; Gedragsindicatoren', false);
        $response->assertSee('Agressie &amp; Luchtplaats', false);
        $response->assertSee('Financieel Kapitaal', false);
        $response->assertSee('Dagbesteding &amp; Arbeid', false);
        $response->assertSee('Geverifieerde Feiten &amp; Observaties in Dossier:', false);

        // Barcode
        $response->assertSee($report['dossier_nr'].'-DJI-NETHERLANDS');

        // Balanced opening and closing quotation marks in doctor notes card
        $response->assertSee('&ldquo;', false);
        $response->assertSee('&rdquo;', false);
    }

    public function test_singular_activity_counts_and_role_formatting_in_doctor_notes(): void
    {
        $user = User::factory()->create();
        $gang = Gang::create([
            'name' => 'Linkerbaan Gang',
            'tag' => 'LBG',
            'description' => 'Test gang',
            'specialty' => 'smuggling',
            'leader_id' => $user->id,
        ]);

        $character = $this->createCharacter($user, [
            'name' => 'Dave',
            'fights_won' => 1,
            'fights_lost' => 1,
            'thefts_succeeded' => 1,
            'thefts_failed' => 1,
            'breakout_attempts' => 1,
            'prison_escapes' => 1,
            'palmed_pills_count' => 1,
            'hacks_completed_total' => 1,
            'kills_count' => 1,
            'bullets' => 1,
            'dagbesteding_shifts_completed' => 1,
            'dagbesteding_earned_total' => 500,
            'gang_id' => $gang->id,
            'gang_role' => 'co_leader',
            'respect' => 12185,
        ]);

        CharacterArmWrestleStat::create([
            'character_id' => $character->id,
            'wins' => 1,
            'losses' => 1,
        ]);

        EscapeTunnel::create([
            'character_id' => $character->id,
            'progress_pct' => 20,
            'spoons_used' => 1,
            'times_escaped' => 1,
        ]);

        $report = $character->getPsychiatricReport();
        $notes = $report['doctor_notes'];

        // Singular grammar checks
        $this->assertStringContainsString('1 geweldsconfrontatie', $notes);
        $this->assertStringNotContainsString('1 geweldsconfrontaties', $notes);
        $this->assertStringContainsString('1 incassatie', $notes);
        $this->assertStringNotContainsString('1 incassaties', $notes);
        $this->assertStringContainsString('1 zege', $notes);
        $this->assertStringNotContainsString('1 zeges', $notes);
        $this->assertStringContainsString('1 dodelijke liquidatie', $notes);
        $this->assertStringNotContainsString('1 dodelijke liquidaties', $notes);
        $this->assertStringContainsString('1 patroon', $notes);
        $this->assertStringNotContainsString('1 patronen', $notes);
        $this->assertStringContainsString('1 geslaagde ontvreemding.', $notes);
        $this->assertStringNotContainsString('1 geslaagde ontvreemdingen', $notes);
        $this->assertStringContainsString('1 uitbraakpoging', $notes);
        $this->assertStringNotContainsString('1 uitbraakpogingen', $notes);
        $this->assertStringContainsString('1 afgesleten eetlepel', $notes);
        $this->assertStringNotContainsString('1 afgesleten eetlepels', $notes);
        $this->assertStringContainsString('1 pil verdonkeremaand', $notes);
        $this->assertStringNotContainsString('1 pillen verdonkeremaand', $notes);
        $this->assertStringContainsString('1 ongeautoriseerde inbraak', $notes);
        $this->assertStringNotContainsString('1 ongeautoriseerde inbraken', $notes);
        $this->assertStringContainsString('1 werkdienst', $notes);
        $this->assertStringNotContainsString('1 werkdiensten', $notes);

        // Role & respect formatting checks
        $this->assertStringContainsString('Onderbaas', $notes);
        $this->assertStringNotContainsString('co_leader', $notes);
        $this->assertStringContainsString('12.185 respectpunten', $notes);
        $this->assertStringNotContainsString('12185 respectpunten', $notes);

        // Check multi-paragraph structure (at least 4 paragraphs separated by \n\n)
        $paragraphs = explode("\n\n", $notes);
        $this->assertGreaterThanOrEqual(4, count($paragraphs));

        // Plural indicators check
        $multiFightChar = $this->createCharacter($user, [
            'fights_won' => 5,
            'fights_lost' => 3,
        ]);
        $multiReport = $multiFightChar->getPsychiatricReport();
        $indicatorValues = array_column($multiReport['forensic_indicators'], 'value');
        $this->assertContains('5 zeges / 3 verliezen', $indicatorValues);
    }

    public function test_koudbloedigheid_and_genadeschot_influence_report_and_indicators(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'level' => 26,
            'kills_count' => 6,
            'bullets' => 50,
            'perks' => [
                'cold_blooded_pct' => 85,
            ],
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame(85, $report['cold_blooded_pct']);
        $this->assertTrue($report['can_one_tap']);
        $this->assertSame('Code Zwart', $report['threat_level']);
        $this->assertContains('Koudbloedig', $report['personality_traits']);

        // Check forensic indicator
        $labels = array_column($report['forensic_indicators'], 'label');
        $this->assertContains('Koudbloedigheid & Focus', $labels);

        $indicatorKey = array_search('Koudbloedigheid & Focus', $labels, true);
        $this->assertSame('85% Kaliberfocus', $report['forensic_indicators'][$indicatorKey]['value']);
        $this->assertSame('kritiek', $report['forensic_indicators'][$indicatorKey]['status']);

        // Check doctor notes
        $this->assertStringContainsString('85%', $report['doctor_notes']);
        $this->assertStringContainsString('genadeschot', strtolower($report['doctor_notes']));
    }

    public function test_xss_inputs_are_properly_sanitized_in_psychiatric_report(): void
    {
        $user = User::factory()->create();
        $gang = Gang::create([
            'name' => '<b onmouseover=alert(1)>BadGang</b>',
            'tag' => 'BAD',
            'description' => 'Test gang',
            'specialty' => 'smuggling',
            'leader_id' => $user->id,
        ]);

        $character = $this->createCharacter($user, [
            'name' => "<script>alert('xss')</script>Pawn",
            'gang_id' => $gang->id,
            'gang_role' => '<u>Underboss</u>',
            'is_in_prison' => true,
            'prison_reason' => '<img src=x onerror=alert(2)>Breakout',
        ]);

        $report = $character->getPsychiatricReport();

        // Verify script and HTML tags are sanitized
        $this->assertStringNotContainsString('<script>', $report['doctor_notes']);
        $this->assertStringNotContainsString('</script>', $report['doctor_notes']);
        $this->assertStringNotContainsString('<b onmouseover', $report['doctor_notes']);
        $this->assertStringNotContainsString('<img', $report['doctor_notes']);
        $this->assertStringNotContainsString('<u>', $report['doctor_notes']);
        $this->assertStringContainsString('Pawn', $report['doctor_notes']);
        $this->assertStringContainsString('BadGang', $report['doctor_notes']);
    }

    public function test_negative_boundary_inputs_clamp_safely_without_errors(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => -500,
            'bank' => -1000,
            'respect' => -20,
            'level' => -5,
            'fights_won' => -10,
            'fights_lost' => -2,
            'thefts_succeeded' => -4,
            'thefts_failed' => -3,
            'bullets' => -15,
            'treatment_score' => -10,
        ]);

        $report = $character->getPsychiatricReport();

        $this->assertSame('Code Groen', $report['threat_level']);
        $this->assertSame(10, $report['threat_meter']);
        $this->assertIsArray($report['forensic_indicators']);
        $this->assertIsArray($report['observed_incidents']);
        $this->assertNotEmpty($report['doctor_notes']);
    }

    public function test_treatment_controller_prevents_comatose_and_imprisoned_exploit(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'energy' => 100,
            'treatment_score' => 50,
        ]);

        // 1. Imprisoned character cannot submit treatment
        $character->is_in_prison = true;
        $character->prison_until = now()->addHour();
        $character->save();

        $response = $this->actingAs($user)->post(route('treatment.submit'), [
            'answers' => ['1' => 'a', '2' => 'a', '3' => 'a'],
        ]);
        $response->assertRedirect(route('prison'));

        // 2. Comatose character cannot submit treatment
        $character->is_in_prison = false;
        $character->prison_until = null;
        $character->coma_until = now()->addHours(2);
        $character->save();

        $response = $this->actingAs($user)->post(route('treatment.submit'), [
            'answers' => ['1' => 'a', '2' => 'a', '3' => 'a'],
        ]);
        $response->assertSessionHas('error');

        // 3. Healthy character can submit treatment within transaction
        $character->coma_until = null;
        $character->save();

        $dayOfYear = (int) now()->dayOfYear;
        $dailyCase = TreatmentCaseCatalog::getCaseForDay($dayOfYear);
        $questionKeys = array_keys($dailyCase['questions']);

        $response = $this->actingAs($user)->post(route('treatment.submit'), [
            'answers' => [
                (string) $questionKeys[0] => 'a',
                (string) $questionKeys[1] => 'a',
                (string) $questionKeys[2] => 'a',
            ],
        ]);
        $response->assertSessionHasNoErrors();

        $character->refresh();
        $this->assertNotNull($character->treatment_history);
        $this->assertNotNull($character->last_treatment_at);
    }

    public function test_fail_closed_catches_unexpected_exceptions_and_logs_telemetry(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'PsychiatricReportService')
                    && isset($context['error'])
                    && isset($context['trace']);
            });

        // Mock service or character that throws inside generate
        $mockService = new class extends PsychiatricReportService
        {
            protected function extractPatientMetrics(Character $character): array
            {
                throw new \RuntimeException('Corrupted forensic database link simulated.');
            }
        };

        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $fallbackReport = $mockService->generate($character);

        $this->assertSame('Code Groen', $fallbackReport['threat_level']);
        $this->assertSame('Intake & Observatiegroep', $fallbackReport['regime']);
        $this->assertSame('Anti-sociale Persoonlijkheidsstructuur (Type TBS-1: Intake)', $fallbackReport['diagnosis']);
        $this->assertNotEmpty($fallbackReport['doctor_notes']);
        $this->assertIsArray($fallbackReport['forensic_indicators']);
    }
}

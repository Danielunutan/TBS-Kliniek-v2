<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterArmWrestleStat;
use App\Models\EscapeTunnel;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }
}

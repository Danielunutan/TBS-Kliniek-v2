<?php

namespace App\Services;

use App\Models\Character;

class PsychiatricReportService
{
    /**
     * Generate dynamic official psychiatric evaluation report from Dr. Van Beek tailored to player activity.
     *
     * @return array{
     *     dossier_nr: string,
     *     threat_level: string,
     *     threat_color: string,
     *     threat_badge_color: string,
     *     threat_icon: string,
     *     danger_desc: string,
     *     threat_meter: int,
     *     regime: string,
     *     dsm_code: string,
     *     diagnosis: string,
     *     diagnosis_description: string,
     *     doctor_notes: string,
     *     treatment_plan: string,
     *     medication: string,
     *     doctor_name: string,
     *     doctor_role: string,
     *     big_registration: string,
     *     forensic_indicators: array<int, array{label: string, value: string, status: string, badge_class: string, icon: string, note: string}>,
     *     observed_incidents: array<int, string>,
     *     prognosis: string,
     *     patient_classification: string,
     *     personality_traits: array<int, string>
     * }
     */
    public function generate(Character $character): array
    {
        $data = $this->extractPatientMetrics($character);
        $archetype = $this->determineArchetype($data);
        $threat = $this->calculateThreatProfile($data, $archetype);
        $indicators = $this->buildForensicIndicators($data);
        $incidents = $this->compileObservedIncidents($data, $archetype);
        $doctorNotes = $this->generateDoctorNotes($data, $archetype, $threat, $incidents);

        return [
            'dossier_nr' => 'TBS-'.str_pad((string) $character->id, 5, '0', STR_PAD_LEFT),
            'threat_level' => $threat['level'],
            'threat_color' => $threat['color'],
            'threat_badge_color' => $threat['badge_color'],
            'threat_icon' => $threat['icon'],
            'danger_desc' => $threat['danger_desc'],
            'threat_meter' => $threat['meter'],
            'regime' => $threat['regime'],
            'dsm_code' => $archetype['dsm_code'],
            'diagnosis' => $archetype['diagnosis'],
            'diagnosis_description' => $archetype['description'],
            'doctor_notes' => $doctorNotes,
            'treatment_plan' => $archetype['treatment_plan'],
            'medication' => $archetype['medication'],
            'doctor_name' => 'Dr. H. van Beek',
            'doctor_role' => 'Hoofd Behandelend Geneesheer Forensische Zorg',
            'big_registration' => 'BIG: 492.019.481',
            'forensic_indicators' => $indicators,
            'observed_incidents' => $incidents,
            'prognosis' => $archetype['prognosis'],
            'patient_classification' => $threat['classification'],
            'personality_traits' => $archetype['traits'],
        ];
    }

    /**
     * Format a gang role key to a clean Dutch title.
     */
    protected function formatRole(?string $role): string
    {
        if (empty($role)) {
            return 'Lid';
        }

        $normalized = strtolower(trim($role));

        return match ($normalized) {
            'baas', 'leader', 'leider', 'founder', 'eigenaar' => 'Baas',
            'co_leader', 'co-leader', 'co leader', 'onderbaas', 'underboss' => 'Onderbaas',
            'officer', 'officier' => 'Officier',
            'member', 'lid' => 'Lid',
            'recruit', 'recruut' => 'Recruut',
            default => ucwords(str_replace('_', ' ', $role)),
        };
    }

    /**
     * Extract and normalize all clinical activity metrics from character record.
     *
     * @return array<string, mixed>
     */
    protected function extractPatientMetrics(Character $character): array
    {
        $gang = $character->relationLoaded('gang') ? $character->gang : $character->gang()->first();
        $armWrestle = $character->relationLoaded('armWrestleStat') ? $character->armWrestleStat : $character->armWrestleStat()->first();
        $escapeTunnel = $character->relationLoaded('escapeTunnel') ? $character->escapeTunnel : $character->escapeTunnel()->first();

        $cash = (int) ($character->cash ?? 0);
        $bank = (int) ($character->bank ?? 0);
        $wealth = $cash + $bank;
        $respect = (int) ($character->respect ?? 0);

        $fightsWon = (int) ($character->fights_won ?? 0);
        $fightsLost = (int) ($character->fights_lost ?? 0);
        $theftsSucceeded = (int) ($character->thefts_succeeded ?? 0);
        $theftsFailed = (int) ($character->thefts_failed ?? 0);

        $treatmentHistory = is_array($character->treatment_history) ? $character->treatment_history : [];

        return [
            'name' => $character->name,
            'level' => (int) ($character->level ?? 1),
            'cash' => $cash,
            'bank' => $bank,
            'wealth' => $wealth,
            'formatted_wealth' => number_format($wealth, 0, ',', '.'),
            'formatted_cash' => number_format($cash, 0, ',', '.'),
            'formatted_bank' => number_format($bank, 0, ',', '.'),
            'respect' => $respect,
            'formatted_respect' => number_format($respect, 0, ',', '.'),
            'fights_won' => $fightsWon,
            'fights_lost' => $fightsLost,
            'total_fights' => $fightsWon + $fightsLost,
            'daily_attacks' => (int) ($character->daily_attacks_count ?? 0),
            'strength' => (int) ($character->strength ?? 10),
            'defense' => (int) ($character->defense ?? 10),
            'stamina' => (int) ($character->stamina ?? 10),
            'thefts_succeeded' => $theftsSucceeded,
            'thefts_failed' => $theftsFailed,
            'total_thefts' => $theftsSucceeded + $theftsFailed,
            'missions_completed' => (int) ($character->missions_completed ?? 0),
            'prison_sentences' => (int) ($character->prison_sentences ?? 0),
            'prison_escapes' => (int) ($character->prison_escapes ?? 0),
            'breakout_attempts' => (int) ($character->breakout_attempts ?? 0),
            'is_in_prison' => (bool) ($character->is_in_prison ?? false),
            'prison_reason' => $character->prison_reason,
            'tunnel_progress' => (int) ($escapeTunnel?->progress_pct ?? 0),
            'spoons_used' => (int) ($escapeTunnel?->spoons_used ?? 0),
            'palmed_pills' => (int) ($character->palmed_pills_count ?? 0),
            'dagbesteding_shifts' => (int) ($character->dagbesteding_shifts_completed ?? 0),
            'dagbesteding_earned' => (int) ($character->dagbesteding_earned_total ?? 0),
            'formatted_dagbesteding_earned' => number_format((int) ($character->dagbesteding_earned_total ?? 0), 0, ',', '.'),
            'treatment_score' => (int) ($character->treatment_score ?? 50),
            'leave_trips' => (int) ($character->leave_trips_count ?? 0),
            'is_on_leave' => $character->isOnLeave(),
            'is_sedated' => $character->isSedated(),
            'is_comatose' => $character->isComatose(),
            'treatment_history' => $treatmentHistory,
            'gang_name' => $gang?->name,
            'gang_role' => $this->formatRole($character->gang_role),
            'hacks_completed' => (int) ($character->hacks_completed_total ?? 0),
            'kills' => (int) ($character->kills_count ?? 0),
            'deaths' => (int) ($character->deaths_count ?? 0),
            'bullets' => (int) ($character->bullets ?? 0),
            'arm_wrestle_wins' => (int) ($armWrestle?->wins ?? 0),
            'arm_wrestle_losses' => (int) ($armWrestle?->losses ?? 0),
            'created_at' => $character->created_at,
        ];
    }

    /**
     * Determine primary psychiatric diagnosis archetype based on the player's most prominent activities.
     *
     * @param  array<string, mixed>  $m
     * @return array{
     *     key: string,
     *     dsm_code: string,
     *     diagnosis: string,
     *     description: string,
     *     treatment_plan: string,
     *     medication: string,
     *     prognosis: string,
     *     traits: array<int, string>
     * }
     */
    protected function determineArchetype(array $m): array
    {
        $scores = [];

        // 1. Contract Killer / Hitman
        $scores['contract_killer'] = ($m['kills'] * 40) + ($m['bullets'] >= 10 ? 25 : 0);

        // 2. Escape Artist / Flight Risk
        $scores['escape_artist'] = ($m['prison_escapes'] * 35)
            + ($m['breakout_attempts'] * 20)
            + ($m['tunnel_progress'] > 0 ? (int) ($m['tunnel_progress'] * 0.5) : 0)
            + ($m['spoons_used'] * 2)
            + ($m['prison_sentences'] * 3);

        // 3. Cyber Saboteur
        $scores['cyber_saboteur'] = ($m['hacks_completed'] * 25) + ($m['hacks_completed'] >= 3 ? 30 : 0);

        // 4. Pill Hoarder / Substance Traffic
        $scores['pill_hoarder'] = ($m['palmed_pills'] * 20) + ($m['palmed_pills'] >= 3 ? 25 : 0);

        // 5. Syndicate Boss
        $isLeader = in_array(strtolower((string) $m['gang_role']), ['baas', 'leider', 'founder', 'eigenaar', 'onderbaas']);
        $scores['syndicate_boss'] = ! empty($m['gang_name'])
            ? (($isLeader ? 50 : 25) + min(35, (int) round($m['respect'] / 25)))
            : 0;

        // 6. Arm Wrestler / Hyper-masculine gym beast
        $scores['arm_wrestler'] = ($m['arm_wrestle_wins'] * 8) + ($m['arm_wrestle_wins'] >= 5 ? 20 : 0);

        // 7. Money Launderer / Wealthy racketeer
        $wealthScore = 0;
        if ($m['wealth'] >= 500000) {
            $wealthScore = 80;
        } elseif ($m['wealth'] >= 100000) {
            $wealthScore = 55;
        } elseif ($m['wealth'] >= 25000) {
            $wealthScore = 30;
        }
        $scores['money_launderer'] = $wealthScore + ($m['cash'] >= 50000 ? 15 : 0);

        // 8. Violent Brawler / Luchtplaats fighter
        $scores['violent_brawler'] = ($m['fights_won'] * 4)
            + ($m['fights_lost'] * 1)
            + ($m['daily_attacks'] * 3)
            + ($m['strength'] >= 50 ? 15 : 0);

        // 9. Habitual Kleptomaniac / Thief
        $scores['kleptomaniac'] = ($m['thefts_succeeded'] * 3)
            + ($m['thefts_failed'] * 1)
            + ($m['missions_completed'] * 2);

        // 10. Model Patient / Daycare worker
        $isModel = $m['dagbesteding_shifts'] >= 3 && $m['treatment_score'] >= 60 && $m['fights_won'] < 8;
        $scores['model_patient'] = $isModel
            ? (($m['dagbesteding_shifts'] * 6) + ($m['treatment_score'] >= 75 ? 30 : 15) + ($m['leave_trips'] * 10))
            : 0;

        // Find the highest score
        arsort($scores);
        $topKey = (string) array_key_first($scores);
        $topScore = $scores[$topKey] ?? 0;

        // If top activity score is negligible, fallback to intake diagnosis
        if ($topScore < 15) {
            $topKey = 'intake';
        }

        return match ($topKey) {
            'contract_killer' => [
                'key' => 'contract_killer',
                'dsm_code' => 'DSM-TBS 301.72 / F60.2',
                'diagnosis' => 'Koudbloedig Liquidatiesyndroom & Huurmoordenaarsdelirium',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt toont een afwezigheid van empathie en instrumentele doelgerichtheid, ondersteund door rapporten aangaande '.($m['kills'] === 1 ? '1 dodelijke confrontatie' : $m['kills'].' dodelijke confrontaties').' en bezit van scherpe patronen.',
                'treatment_plan' => 'Plaatsing in maximale penitentiaire isolatie (EBI); fysieke scheiding van rivaliserende groepen; celdeurinspectie uitsluitend via beveiligd doorgeefluik.',
                'medication' => '30mg Aripiprazol eenmaal daags; bij verhoogde spanning direct 20mg Diazepam i.m.',
                'prognosis' => 'Uiterst ongunstig. Permanente dreiging voor personeel en mede-gedetineerden.',
                'traits' => ['Gewetenloos', 'Koudbloedig', 'Emotieloos', 'Berekenend'],
            ],
            'escape_artist' => [
                'key' => 'escape_artist',
                'dsm_code' => 'DSM-TBS 300.15 / F44.89',
                'diagnosis' => 'Dissociatieve Ontsnappingsdrang & Chronische Uitbraakobsessie',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt kampt met een obsessieve drang om de bouwkundige beveiliging te doorbreken, blijkend uit '.(($m['prison_escapes'] + $m['breakout_attempts']) === 1 ? '1 uitbraakincident' : ($m['prison_escapes'] + $m['breakout_attempts']).' uitbraakincidenten').' en structurele graafpogingen.',
                'treatment_plan' => 'Dagelijkse tralie- en vloercontrole met akoestische hamer; seismische bodemdetectie onder de celvloer; eetbestek en gereedschap verplicht tellen na gebruik.',
                'medication' => '25mg Promethazine voor de nacht ter vermindering van nachtelijke onrust en dwaaldrang.',
                'prognosis' => 'Zeer hoog vluchtrisico. Zelfstandig verlof is onder alle omstandigheden uitgesloten.',
                'traits' => ['Vluchtgevaarlijk', 'Vindingrijk', 'Obsessief', 'Onrustig'],
            ],
            'cyber_saboteur' => [
                'key' => 'cyber_saboteur',
                'dsm_code' => 'DSM-TBS 312.30 / F63.9',
                'diagnosis' => 'Technologische Paranoia & Obsessieve Cyber-sabotage',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt exploiteert informatiesystemen en heeft '.($m['hacks_completed'] === 1 ? '1 geregistreerde inbraak' : $m['hacks_completed'].' geregistreerde inbraken').' gepleegd op de digitale infrastructuur en cameranetwerken van FPC De Balk.',
                'treatment_plan' => 'Algeheel verbod op computers, communicatieapparatuur en chipkaarten; netwerkaansluitingen in celvleugel fysiek ontkoppeld en verzegeld.',
                'medication' => 'Geen zware psychofarmaca; verplichte analoge arbeidstherapie en cognitieve herstructurering.',
                'prognosis' => 'Gereserveerd. Technisch vernuft vormt een structureel gevaar voor de inrichtingsbeveiliging.',
                'traits' => ['Technisch Begaafd', 'Manipulatief', 'Paranoïde', 'Subversief'],
            ],
            'pill_hoarder' => [
                'key' => 'pill_hoarder',
                'dsm_code' => 'DSM-TBS 304.00 / F11.20',
                'diagnosis' => 'Farmaceutische Verzamelwoede & Zwarte Medicatiehandel',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt ontvreemdt systematisch voorgeschreven farmaca bij de ziekenboeg (reeds '.($m['palmed_pills'] === 1 ? '1 pil' : $m['palmed_pills'].' pillen').' achterovergedrukt) ter facilitering van zwarte handel in de paviljoens.',
                'treatment_plan' => 'Onaangekondigde visitaties van matrasnaden en holle celbuizen; strikt toegangsverbod tot het medisch uitreikpunt zonder dubbele begeleiding.',
                'medication' => 'Volledige omschakeling naar vloeibare suspensies; verplichte mond- en wanginspectie met spatel na inname.',
                'prognosis' => 'Matig tot zorgelijk. Hoge kans op terugval in substantiehandel en misbruik.',
                'traits' => ['Compulsief', 'Manipulatief', 'Stiekem', 'Afhankelijk'],
            ],
            'syndicate_boss' => [
                'key' => 'syndicate_boss',
                'dsm_code' => 'DSM-TBS 301.83 / F60.8',
                'diagnosis' => 'Narcistische Syndicaatswanen & Kartelleiderschap',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt instrumentaliseert informele groepsdynamiek en stuurt als '.$m['gang_role'].' van syndicaat \''.($m['gang_name'] ?? 'Onbekend').'\' mede-gedetineerden aan ter ondermijning van de inrichtingsorde.',
                'treatment_plan' => 'Strikte separatie van mede-bendeleden; volledige screening van correspondentie en telefonie; luchten in afgeschermd compartiment.',
                'medication' => 'Stemmingsstabilisator Lithium 400mg tweemaal daags ter demping van grandioze controle- en dominantiedrang.',
                'prognosis' => 'Ongunstig. Sterk hiërarchisch denkpatroon blokkeert acceptatie van behandelautoriteit.',
                'traits' => ['Dominant', 'Hiërarchisch', 'Machtbelust', 'Narcistisch'],
            ],
            'arm_wrestler' => [
                'key' => 'arm_wrestler',
                'dsm_code' => 'DSM-TBS 300.71 / F45.2',
                'diagnosis' => 'Dysmorfe Spierobsessie & Luchtplaats-armworsteldominantie',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt kanaliseert innerlijke onrust via buitensporige krachttraining en armworstelconfrontaties ('.($m['arm_wrestle_wins'] === 1 ? '1 overwinning' : $m['arm_wrestle_wins'].' overwinningen').'), gepaard gaande met illegale weddenschappen.',
                'treatment_plan' => 'Verbod op ongecontroleerde krachtwedstrijden in de recreatiezaal; toezicht op verzwaringen en geïmproviseerde gewichten.',
                'medication' => 'Magnesiumsulfaat en lichte spierontspanners (Baclofen 5mg); psychosomatische begeleiding.',
                'prognosis' => 'Redelijk mits fysieke energie wordt omgebogen naar constructieve dagbesteding.',
                'traits' => ['Lichamelijk Gefixeerd', 'Competitief', 'Spierobsessief', 'Territoriaal'],
            ],
            'money_launderer' => [
                'key' => 'money_launderer',
                'dsm_code' => 'DSM-TBS 301.81 / F60.8',
                'diagnosis' => 'Witteboorden-paranoia & Illegaal Vermogensbeheer',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt toont een hardnekkige fixatie op vermogensopbouw en zwarte geldstromen (€ '.$m['formatted_wealth'].' geregistreerd vermogen), met een verhoogd risico op omkoping van personeel.',
                'treatment_plan' => 'Regelmatige inspectie van celkluisjes en smokkelroutes; maximaal € 50 zakgeld per week toegestaan op de kliniekrekening.',
                'medication' => '15mg Oxazepam p.n. bij financiële onrust; strikte financiële curatele door het Ministerie.',
                'prognosis' => 'Zorgelijk. Materiële hebzucht overschaduwt elk moreel ziektebesef.',
                'traits' => ['Materialistisch', 'Berekenend', 'Omkoopgevoelig', 'Wantoestig'],
            ],
            'violent_brawler' => [
                'key' => 'violent_brawler',
                'dsm_code' => 'DSM-TBS 312.34 / F63.81',
                'diagnosis' => 'Chronische Impuls-beheersingsstoornis & Luchtplaats-agressie',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt toont structurele fysieke agressie en territoriumdrift op het recreatieplein, resulterend in reeds '.($m['fights_won'] === 1 ? '1 overwinning' : $m['fights_won'].' overwinningen').' in gevechten met mede-gedetineerden.',
                'treatment_plan' => 'Verplichte agressieregulatietraining (ART); toegangsverbod tot het krachthonk tijdens spitsuren; extra toezicht door bewapende zaalwachten.',
                'medication' => '200mg Haloperidol depot 1x p/w + Stemmingsstabilisatoren bij plotselinge woede-uitbarstingen.',
                'prognosis' => 'Ongunstig zolang fysieke confrontatie de primaire copingsstrategie blijft.',
                'traits' => ['Agressief', 'Impulsief', 'Prikkelbaar', 'Confronterend'],
            ],
            'kleptomaniac' => [
                'key' => 'kleptomaniac',
                'dsm_code' => 'DSM-TBS 312.32 / F63.2',
                'diagnosis' => 'Compulsieve Kleptomanie & Georganiseerde Beroepscriminaliteit',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt kan andermans bezittingen niet met rust laten en heeft inmiddels '.($m['thefts_succeeded'] === 1 ? '1 geslaagde diefstal' : $m['thefts_succeeded'].' geslaagde diefstallen').' binnen en buiten de inrichtingsmuren geregistreerd.',
                'treatment_plan' => 'Grondige fouillering bij het betreden en verlaten van gemeenschappelijke ruimtes; metaaldetectie bij celdeuren; opbergkist verzegeld.',
                'medication' => '50mg Sertraline \'s ochtends ter vermindering van compulsieve ontvreemdingsdrang.',
                'prognosis' => 'Matig. Vereist voortdurende institutionele controle en fouillering.',
                'traits' => ['Kleptomaan', 'Geruisloos', 'Compulsief', 'Opportunistisch'],
            ],
            'model_patient' => [
                'key' => 'model_patient',
                'dsm_code' => 'DSM-TBS 301.9 / F60.9',
                'diagnosis' => 'Pseudo-aangepaste Machiavellistische Persoonlijkheid (Schijnaanpassing)',
                'description' => 'Vastgesteld conform klinische DSM-TBS richtlijnen. Patiënt vertoont een opvallend stipte naleving van de kliniekregels, met '.($m['dagbesteding_shifts'] === 1 ? '1 voltooide werkdienst' : $m['dagbesteding_shifts'].' voltooide werkdiensten').' en een behandelscore van '.$m['treatment_score'].'%; het team waakt voor geraffineerde schijnaanpassing.',
                'treatment_plan' => 'Toelating tot fase-2 werktherapie in de werkplaats; periodieke proefverloven onder strikte meldplicht en onaangekondigde screening.',
                'medication' => 'Geen zware psychofarmaca geïndiceerd; tweewekelijkse gesprekstherapie ter toetsing van oprechtheid.',
                'prognosis' => 'Gunstig op papier, doch voorzichtigheid geboden inzake opportunistische motieven.',
                'traits' => ['Aangepast', 'Coöperatief', 'Berekenend', 'Beheerst'],
            ],
            default => [
                'key' => 'intake',
                'dsm_code' => 'DSM-TBS 301.7 / F60.2',
                'diagnosis' => 'Anti-sociale Persoonlijkheidsstructuur (Type TBS-1: Intake)',
                'description' => 'Vastgesteld conform klinische DSM-TBS standaarden. Patiënt bevindt zich in de initiële observatiefase; toont een structureel patroon van verhoogde risicobereidheid, dominantiedrang en verminderde impulsremming.',
                'treatment_plan' => 'Wekelijkse één-op-één sessies met psychiater Dr. Van Beek; deelname aan kliniekactiviteiten en dagbesteding stimuleren.',
                'medication' => 'Geen zware medicatie geïndiceerd; gedragsmonitoring in teamoverleg.',
                'prognosis' => 'Nog niet definitief vast te stellen. Observatieperiode loopt thans.',
                'traits' => ['Afwachtend', 'Argwanend', 'Prikkelbaar', 'Intake-status'],
            ],
        };
    }

    /**
     * Compute threat level, color scheme, regime, and primary danger description.
     *
     * @param  array<string, mixed>  $m
     * @param  array<string, mixed>  $archetype
     * @return array{
     *     level: string,
     *     color: string,
     *     badge_color: string,
     *     icon: string,
     *     danger_desc: string,
     *     meter: int,
     *     regime: string,
     *     classification: string
     * }
     */
    protected function calculateThreatProfile(array $m, array $archetype): array
    {
        $points = 10;

        // Level weight
        $points += (int) min(25, round($m['level'] * 1.5));

        // Violence weight
        $points += (int) min(35, round($m['fights_won'] * 1.5));

        // Kills weight
        $points += (int) min(30, $m['kills'] * 20);

        // Escape & prison weight
        $points += (int) min(30, ($m['prison_escapes'] * 18) + ($m['breakout_attempts'] * 10) + ($m['prison_sentences'] * 3));

        // Wealth weight
        if ($m['wealth'] >= 500000) {
            $points += 25;
        } elseif ($m['wealth'] >= 100000) {
            $points += 18;
        } elseif ($m['wealth'] >= 25000) {
            $points += 10;
        }

        // Thefts weight
        $points += (int) min(18, round($m['thefts_succeeded'] * 0.4));

        // Hacking & pill hoarding
        $points += (int) min(15, ($m['hacks_completed'] * 5) + ($m['palmed_pills'] * 4));

        // Gang influence
        if (! empty($m['gang_name'])) {
            $points += in_array(strtolower((string) $m['gang_role']), ['baas', 'leider', 'founder']) ? 15 : 8;
        }

        // Compliance discount
        if ($m['treatment_score'] >= 70 && $m['dagbesteding_shifts'] >= 5) {
            $points -= 12;
        }

        $meter = max(10, min(100, $points));

        // Explicit rule overrides matching game rules & existing test expectations
        $isBlack = $m['fights_won'] >= 35
            || $m['level'] >= 20
            || $m['kills'] >= 1
            || $m['prison_escapes'] >= 2
            || $meter >= 92;

        $isRed = ! $isBlack && (
            $m['fights_won'] >= 15
            || $m['level'] >= 10
            || $m['breakout_attempts'] >= 3
            || $m['thefts_succeeded'] >= 30
            || $meter >= 75
        );

        $isOrange = ! $isBlack && ! $isRed && (
            $m['fights_won'] >= 5
            || $m['level'] >= 5
            || $m['thefts_succeeded'] >= 10
            || $m['wealth'] >= 25000
            || $m['palmed_pills'] >= 3
            || $meter >= 50
        );

        $isYellow = ! $isBlack && ! $isRed && ! $isOrange && (
            $m['level'] >= 2
            || $m['fights_won'] >= 1
            || $m['thefts_succeeded'] >= 1
            || $m['dagbesteding_shifts'] >= 1
            || $meter >= 25
        );

        if ($isBlack) {
            $level = 'Code Zwart';
            $color = 'bg-rose-100/90 dark:bg-slate-950 text-rose-950 dark:text-rose-300 border-rose-400 dark:border-rose-600/80 shadow-xs';
            $badgeColor = 'bg-rose-200/80 dark:bg-rose-500/20 text-rose-900 dark:text-rose-300 border-rose-400 dark:border-rose-500/50';
            $icon = 'fa-solid fa-biohazard text-rose-600 dark:text-rose-400 animate-pulse';
            $meter = 100;
            $classification = 'Categorie A (Extreem Gewelds- en Vluchtgevaarlijk)';

            $regime = match ($archetype['key']) {
                'escape_artist' => 'EBI - Vluchtgevaarlijk Regime',
                'contract_killer' => 'EBI - Maximale Penitentiaire Isolatie',
                'syndicate_boss' => 'EBI - Separatieregime Georganiseerde Misdaad',
                'money_launderer' => 'EBI - Financieel Subversief Toezicht',
                default => 'EBI - Extra Beveiligde Inrichting',
            };

            $dangerDesc = match ($archetype['key']) {
                'contract_killer' => 'EXTREEM GEVAARLIJK. Dossier vermeldt '.($m['kills'] === 1 ? '1 dodelijke liquidatie' : $m['kills'].' dodelijke liquidaties').'. Maximale penitentiaire isolatie en constante camerabewaking vereist.',
                'escape_artist' => 'ACUUT ONTSNAPPINGSGEVAAR. Heeft reeds '.($m['prison_escapes'] === 1 ? '1 geslaagde ontsnapping' : $m['prison_escapes'].' geslaagde ontsnappingen').' en '.($m['breakout_attempts'] === 1 ? '1 uitbraakpoging' : $m['breakout_attempts'].' uitbraakpogingen').' ondernomen. Vluchtrisico maximaal.',
                'syndicate_boss' => 'EXTREEM DOMINANT. Stuurt criminele syndicaatsnetwerken aan vanuit de inrichting met '.$m['formatted_respect'].' respectpunten. Risico op gijzeling en opstand.',
                'money_launderer' => 'SUBVERSIEF GEVAAR. Beheert een illegaal vermogen van € '.$m['formatted_wealth'].'. Verhoogd risico op grootschalige omkoping van penitentiair personeel.',
                default => 'EXTREEM GEVAARLIJK. Maximale penitentiaire isolatie. Verhoogd ontsnappings- en gijzelingsrisico.',
            };
        } elseif ($isRed) {
            $level = 'Code Rood';
            $color = 'bg-rose-50 dark:bg-rose-950/70 text-rose-900 dark:text-rose-200 border-rose-300 dark:border-rose-800 shadow-xs';
            $badgeColor = 'bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 border-rose-300 dark:border-rose-700';
            $icon = 'fa-solid fa-skull-crossbones text-rose-600 dark:text-rose-400';
            $meter = max(75, min(88, $meter));
            $classification = 'Categorie B (Verhoogd Gewelds- en Veiligheidsrisico)';

            $regime = match ($archetype['key']) {
                'escape_artist' => 'VOS - Verhoogd Ontsnappingsrisico',
                'cyber_saboteur' => 'Digitale Isolatie & Analoog Regime',
                default => 'Afdeling Intensief Toezicht (AIT)',
            };

            $dangerDesc = match ($archetype['key']) {
                'escape_artist' => 'VERHOOGD VLUCHTGEVAAR. Heeft '.($m['breakout_attempts'] === 1 ? '1 uitbraakpoging' : $m['breakout_attempts'].' uitbraakpogingen').' ondernomen. Celinspectie en perimetercontrole aangescherpt.',
                'cyber_saboteur' => 'DIGITAAL SABOTAGEGEVAAR. Verantwoordelijk voor '.($m['hacks_completed'] === 1 ? '1 hack' : $m['hacks_completed'].' hacks').' op inrichtingssystemen. Netwerkontzegging actief.',
                'kleptomaniac' => 'ERNSTIG RECIDIVERISICO. Heeft '.($m['thefts_succeeded'] === 1 ? '1 diefstal' : $m['thefts_succeeded'].' diefstallen').' gepleegd. Fysieke fouillering verplicht bij zaalwissels.',
                default => 'Uiterst gewelddadig en dominant. Vermijd direct oogcontact tijdens recreatie en luchtuurtjes.',
            };
        } elseif ($isOrange) {
            $level = 'Code Oranje';
            $color = 'bg-amber-50 dark:bg-amber-950/70 text-amber-900 dark:text-amber-200 border-amber-300 dark:border-amber-800 shadow-xs';
            $badgeColor = 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 border-amber-300 dark:border-amber-700';
            $icon = 'fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400';
            $meter = max(50, min(68, $meter));
            $classification = 'Categorie C (Matig tot Verhoogd Zorg- & Ordeprofiel)';

            $regime = match ($archetype['key']) {
                'pill_hoarder' => 'Medisch Scherp Toezicht & Detox',
                default => 'Gesloten Behandelafdeling',
            };

            $dangerDesc = match ($archetype['key']) {
                'pill_hoarder' => 'FARMACEUTISCH RISICO. Betrapt op het achteroverdrukken van '.($m['palmed_pills'] === 1 ? '1 pil' : $m['palmed_pills'].' pillen').'. Verhoogde alertheid op medicatiesmokkel.',
                'money_launderer' => 'FINANCIEEL ONRUSTIG. Heeft € '.$m['formatted_wealth'].' aan transacties. Celkluisjes onderworpen aan periodieke inspectie.',
                default => 'Verhoogde waakzaamheid geboden. Vertoont manipulatief, territoriaal en opstandig gedrag.',
            };
        } elseif ($isYellow) {
            $level = 'Code Geel';
            $color = 'bg-yellow-50 dark:bg-yellow-950/70 text-yellow-900 dark:text-yellow-200 border-yellow-300 dark:border-yellow-800 shadow-xs';
            $badgeColor = 'bg-yellow-100 dark:bg-yellow-900/60 text-yellow-800 dark:text-yellow-200 border-yellow-300 dark:border-yellow-700';
            $icon = 'fa-solid fa-circle-exclamation text-yellow-600 dark:text-yellow-400';
            $meter = max(25, min(42, $meter));
            $classification = 'Categorie D (Regulier Behandelprofiel)';

            $regime = match ($archetype['key']) {
                'model_patient' => 'Behandelafdeling B (Resocialisatie)',
                default => 'Regulier Gesloten Regime',
            };

            $dangerDesc = match ($archetype['key']) {
                'model_patient' => 'BEHEERSBAAR RISICO. Vertoont constructief gedrag bij dagbesteding ('.($m['dagbesteding_shifts'] === 1 ? '1 dienst' : $m['dagbesteding_shifts'].' diensten').') en therapie; team let op schijnaanpassing.',
                default => 'Matig risico. Patiënt past zich ogenschijnlijk aan de regels aan, maar beraamt plannen in de schaduw.',
            };
        } else {
            $level = 'Code Groen';
            $color = 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-900 dark:text-emerald-200 border-emerald-300 dark:border-emerald-800 shadow-xs';
            $badgeColor = 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border-emerald-300 dark:border-emerald-700';
            $icon = 'fa-solid fa-shield-halved text-emerald-600 dark:text-emerald-400';
            $meter = 10;
            $regime = 'Intake & Observatiegroep';
            $classification = 'Categorie E (Intake & Observatiestatus)';
            $dangerDesc = 'Nieuwe intake. Observatieperiode loopt nog; vooralsnog beheersbaar binnen de leefgroep.';
        }

        return [
            'level' => $level,
            'color' => $color,
            'badge_color' => $badgeColor,
            'icon' => $icon,
            'danger_desc' => $dangerDesc,
            'meter' => $meter,
            'regime' => $regime,
            'classification' => $classification,
        ];
    }

    /**
     * Build rich forensic metric indicators based on the patient's verified in-game records.
     *
     * @param  array<string, mixed>  $m
     * @return array<int, array{label: string, value: string, status: string, badge_class: string, icon: string, note: string}>
     */
    protected function buildForensicIndicators(array $m): array
    {
        $indicators = [];

        // 1. Violence / Courtyard
        $fightStatus = $m['fights_won'] >= 15 ? 'kritiek' : ($m['fights_won'] >= 5 ? 'verhoogd' : 'veilig');
        $fightWonLabel = $m['fights_won'] === 1 ? '1 zege' : $m['fights_won'].' zeges';
        $fightLostLabel = $m['fights_lost'] === 1 ? '1 verlies' : $m['fights_lost'].' verlies';
        $indicators[] = [
            'label' => 'Agressie & Luchtplaats',
            'value' => $fightWonLabel.' / '.$fightLostLabel,
            'status' => $fightStatus,
            'badge_class' => $fightStatus === 'kritiek'
                ? 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200'
                : ($fightStatus === 'verhoogd' ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200'),
            'icon' => 'fa-solid fa-hand-fist',
            'note' => $m['fights_won'] >= 10 ? 'Fysiek dominant op het terrein' : ($m['fights_won'] > 0 ? 'Incidenten geregistreerd' : 'Geen geweldsescalaties'),
        ];

        // 2. Wealth / Money Laundering
        $wealthStatus = $m['wealth'] >= 100000 ? 'kritiek' : ($m['wealth'] >= 25000 ? 'verhoogd' : 'veilig');
        $indicators[] = [
            'label' => 'Financieel Kapitaal',
            'value' => '€ '.$m['formatted_wealth'],
            'status' => $wealthStatus,
            'badge_class' => $wealthStatus === 'kritiek'
                ? 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200'
                : ($wealthStatus === 'verhoogd' ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200'),
            'icon' => 'fa-solid fa-vault',
            'note' => $m['wealth'] >= 100000 ? 'Verhoogd risico op omkoping' : ($m['wealth'] >= 25000 ? 'Clandestien vermogen aanwezig' : 'Standaard zakgeldniveau'),
        ];

        // 3. Escapes & Isolation
        $escapeCount = $m['prison_escapes'] + $m['breakout_attempts'];
        $escapeStatus = $m['prison_escapes'] >= 1 || $m['breakout_attempts'] >= 2 ? 'kritiek' : ($escapeCount > 0 || $m['prison_sentences'] > 0 ? 'verhoogd' : 'veilig');
        $breakoutLabel = $m['breakout_attempts'] === 1 ? '1 uitbraak' : $m['breakout_attempts'].' uitbraken';
        $sentenceLabel = $m['prison_sentences'] === 1 ? '1x cel' : $m['prison_sentences'].'x cel';
        $indicators[] = [
            'label' => 'Vlucht- & Isoleerstatus',
            'value' => $breakoutLabel.' / '.$sentenceLabel,
            'status' => $escapeStatus,
            'badge_class' => $escapeStatus === 'kritiek'
                ? 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200'
                : ($escapeStatus === 'verhoogd' ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200'),
            'icon' => 'fa-solid fa-person-running',
            'note' => $m['prison_escapes'] > 0 ? ($m['prison_escapes'] === 1 ? '1x succesvol ontvlucht' : $m['prison_escapes'].'x succesvol ontvlucht') : ($m['breakout_attempts'] > 0 ? 'Actieve uitbraakdrang' : 'Geen vluchtsignalen'),
        ];

        // 4. Thefts & Property Crimes
        $theftStatus = $m['thefts_succeeded'] >= 20 ? 'kritiek' : ($m['thefts_succeeded'] >= 5 ? 'verhoogd' : 'veilig');
        $theftSuccessLabel = $m['thefts_succeeded'] === 1 ? '1 geslaagd' : $m['thefts_succeeded'].' geslaagd';
        $indicators[] = [
            'label' => 'Diefstallen & Delicten',
            'value' => $theftSuccessLabel.' ('.$m['thefts_failed'].' betrapt)',
            'status' => $theftStatus,
            'badge_class' => $theftStatus === 'kritiek'
                ? 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200'
                : ($theftStatus === 'verhoogd' ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200'),
            'icon' => 'fa-solid fa-mask',
            'note' => $m['thefts_succeeded'] >= 10 ? 'Compulsief ontvreemdingspatroon' : ($m['thefts_succeeded'] > 0 ? 'Eigendomsdelicten vastgelegd' : 'Geen diefstalhistorie'),
        ];

        // 5. Daycare & Labor Therapy
        $daycareStatus = $m['dagbesteding_shifts'] >= 10 ? 'veilig' : ($m['dagbesteding_shifts'] >= 3 ? 'matig' : 'veilig');
        $shiftCountLabel = $m['dagbesteding_shifts'] === 1 ? '1 dienst' : $m['dagbesteding_shifts'].' diensten';
        $indicators[] = [
            'label' => 'Dagbesteding & Arbeid',
            'value' => $shiftCountLabel.' (€ '.$m['formatted_dagbesteding_earned'].')',
            'status' => $daycareStatus,
            'badge_class' => $m['dagbesteding_shifts'] >= 5
                ? 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200'
                : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200',
            'icon' => 'fa-solid fa-briefcase',
            'note' => $m['dagbesteding_shifts'] >= 10 ? 'Actieve werktherapie in wasserette' : ($m['dagbesteding_shifts'] > 0 ? 'Deelname aan werkplaats' : 'Nog geen dagbestedingsuren'),
        ];

        // 6. Pill Hoarding (if any)
        if ($m['palmed_pills'] > 0) {
            $pillStatus = $m['palmed_pills'] >= 5 ? 'kritiek' : 'verhoogd';
            $palmedPillLabel = $m['palmed_pills'] === 1 ? '1 pil verdonkeremaand' : $m['palmed_pills'].' pillen verdonkeremaand';
            $indicators[] = [
                'label' => 'Farmaceutische Contrabande',
                'value' => $palmedPillLabel,
                'status' => $pillStatus,
                'badge_class' => 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200',
                'icon' => 'fa-solid fa-pills',
                'note' => 'Onderschept bij ziekenboegcontrole',
            ];
        }

        // 7. Gang syndicate (if in gang)
        if (! empty($m['gang_name'])) {
            $indicators[] = [
                'label' => 'Syndicaatsalliantie',
                'value' => $m['gang_role'].' van '.$m['gang_name'],
                'status' => 'verhoogd',
                'badge_class' => 'bg-purple-50 dark:bg-purple-950/50 border-purple-200 dark:border-purple-800 text-purple-900 dark:text-purple-200',
                'icon' => 'fa-solid fa-users-viewfinder',
                'note' => $m['formatted_respect'].' respectpunten in bendeorde',
            ];
        }

        // 8. Cyber hacks (if any)
        if ($m['hacks_completed'] > 0) {
            $hackCountLabel = $m['hacks_completed'] === 1 ? '1 netwerkaanval' : $m['hacks_completed'].' netwerkaanvallen';
            $indicators[] = [
                'label' => 'Cyberinbraken & Sabotage',
                'value' => $hackCountLabel,
                'status' => $m['hacks_completed'] >= 5 ? 'kritiek' : 'verhoogd',
                'badge_class' => 'bg-indigo-50 dark:bg-indigo-950/50 border-indigo-200 dark:border-indigo-800 text-indigo-900 dark:text-indigo-200',
                'icon' => 'fa-solid fa-terminal',
                'note' => 'Inbreuk op inrichtingsintranet',
            ];
        }

        // 9. Arm wrestling (if any)
        if ($m['arm_wrestle_wins'] > 0) {
            $indicators[] = [
                'label' => 'Armworstelen (Recreatie)',
                'value' => $m['arm_wrestle_wins'].'W / '.$m['arm_wrestle_losses'].'L',
                'status' => 'matig',
                'badge_class' => 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200',
                'icon' => 'fa-solid fa-trophy',
                'note' => 'Fysieke krachtmeting in zaal',
            ];
        }

        // 10. Treatment Score
        $score = $m['treatment_score'];
        $scoreStatus = $score >= 70 ? 'veilig' : ($score >= 35 ? 'matig' : 'kritiek');
        $indicators[] = [
            'label' => 'Behandeling & Meewerken',
            'value' => $score.'% ('.($score >= 70 ? 'Gunstig' : ($score >= 35 ? 'Observatie' : 'Zorgelijk')).')',
            'status' => $scoreStatus,
            'badge_class' => $scoreStatus === 'veilig'
                ? 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200'
                : ($scoreStatus === 'matig' ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200' : 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200'),
            'icon' => 'fa-solid fa-brain',
            'note' => $m['leave_trips'] > 0 ? ($m['leave_trips'] === 1 ? '1x verlof toegekend' : $m['leave_trips'].'x verlof toegekend') : 'Geen verlofstatus',
        ];

        return $indicators;
    }

    /**
     * Compile a bulleted list of verified concrete facts from the patient's record.
     *
     * @param  array<string, mixed>  $m
     * @param  array<string, mixed>  $archetype
     * @return array<int, string>
     */
    protected function compileObservedIncidents(array $m, array $archetype): array
    {
        $incidents = [];

        if ($m['kills'] > 0) {
            $killLabel = $m['kills'] === 1 ? '1 dodelijke liquidatie' : $m['kills'].' dodelijke liquidaties';
            $incidents[] = 'Dossier registreert '.$killLabel.' binnen of buiten de muren.';
        }

        if ($m['fights_won'] > 0 || $m['fights_lost'] > 0) {
            $fightWonLabel = $m['fights_won'] === 1 ? '1 geweldsconfrontatie' : $m['fights_won'].' geweldsconfrontaties';
            $fightLostLabel = $m['fights_lost'] === 1 ? '1 nederlaag' : $m['fights_lost'].' nederlagen';
            $incidents[] = $fightWonLabel.' gewonnen op het recreatieplein en de luchtplaats ('.$fightLostLabel.').';
        }

        if ($m['arm_wrestle_wins'] > 0) {
            $armWonLabel = $m['arm_wrestle_wins'] === 1 ? '1 overwinning' : $m['arm_wrestle_wins'].' overwinningen';
            $incidents[] = $armWonLabel.' behaald bij het armpjedrukken in de ontspanningsruimte.';
        }

        if ($m['wealth'] >= 25000) {
            $incidents[] = 'Clandestien vermogen van € '.$m['formatted_wealth'].' geconstateerd (€ '.$m['formatted_cash'].' contant, € '.$m['formatted_bank'].' bank).';
        }

        if ($m['thefts_succeeded'] > 0) {
            $theftSuccessLabel = $m['thefts_succeeded'] === 1 ? '1 succesvolle ontvreemding' : $m['thefts_succeeded'].' succesvolle ontvreemdingen';
            $incidents[] = $theftSuccessLabel.' van eigendommen geregistreerd in het justitiële register.';
        }

        if ($m['prison_escapes'] > 0) {
            $escapeLabel = $m['prison_escapes'] === 1 ? '1 geslaagde ontvluchting' : $m['prison_escapes'].' geslaagde ontvluchtingen';
            $incidents[] = $escapeLabel.' uit beveiligde penitentiaire inrichtingen vastgelegd.';
        }

        if ($m['breakout_attempts'] > 0) {
            $breakoutLabel = $m['breakout_attempts'] === 1 ? '1 actieve uitbraakpoging' : $m['breakout_attempts'].' actieve uitbraakpogingen';
            $incidents[] = $breakoutLabel.' ondernomen en verijdeld door het bewakingspersoneel.';
        }

        if ($m['tunnel_progress'] > 0 || $m['spoons_used'] > 0) {
            $spoonLabel = $m['spoons_used'] === 1 ? '1 gebruikte eetlepel' : $m['spoons_used'].' gebruikte eetlepels';
            $incidents[] = 'Ondergrondse graafactiviteit ontdekt: '.$m['tunnel_progress'].'% tunnelvoortgang met '.$spoonLabel.'.';
        }

        if ($m['prison_sentences'] > 0) {
            $sentenceLabel = $m['prison_sentences'] === 1 ? '1 tuchtplaatsing' : $m['prison_sentences'].' tuchtplaatsingen';
            $incidents[] = $sentenceLabel.' in de isoleercel wegens ernstige schendingen van de afdelingsorde.';
        }

        if ($m['palmed_pills'] > 0) {
            $pillLabel = $m['palmed_pills'] === 1 ? '1 voorgeschreven tablet' : $m['palmed_pills'].' voorgeschreven tabletten';
            $incidents[] = $pillLabel.' weggemoffeld onder de tong bij de medische post.';
        }

        if ($m['hacks_completed'] > 0) {
            $hackLabel = $m['hacks_completed'] === 1 ? '1 ongeautoriseerde computeraanval' : $m['hacks_completed'].' ongeautoriseerde computeraanvallen';
            $incidents[] = $hackLabel.' op het inrichtingsintranet gerapporteerd door ICT-beheer.';
        }

        if (! empty($m['gang_name'])) {
            $incidents[] = 'Actief als '.$m['gang_role'].' binnen syndicaat \''.$m['gang_name'].'\' met '.$m['formatted_respect'].' respectpunten.';
        }

        if ($m['dagbesteding_shifts'] > 0) {
            $shiftLabel = $m['dagbesteding_shifts'] === 1 ? '1 werktherapiedienst' : $m['dagbesteding_shifts'].' werktherapiediensten';
            $incidents[] = $shiftLabel.' volbracht bij de dagbesteding met € '.$m['formatted_dagbesteding_earned'].' legaal verdienste.';
        }

        if (! empty($m['treatment_history']['title'])) {
            $incidents[] = 'Laatste behandeladvies van behandelteam: '.$m['treatment_history']['title'].' (score: '.$m['treatment_score'].'%).';
        }

        if ($m['is_in_prison']) {
            $reason = ! empty($m['prison_reason']) ? ' wegens "'.$m['prison_reason'].'"' : '';
            $incidents[] = 'Patiënt verblijft thans in de strafcel'.$reason.'.';
        }

        if ($m['is_on_leave']) {
            $incidents[] = 'Patiënt geniet thans van goedgekeurd begeleid proefverlof buiten de kliniekpoorten.';
        }

        if ($m['is_sedated']) {
            $incidents[] = 'Patiënt is momenteel onderworpen aan gedwongen medische sedatie wegens agitatie.';
        }

        if ($m['is_comatose']) {
            $incidents[] = 'Patiënt ligt thans buiten bewustzijn op de Intensive Care van de ziekenboeg.';
        }

        // Fallback for new intakes
        if (empty($incidents)) {
            $incidents[] = 'Nieuwe patiënt binnengebracht; initiële diagnostische observatieperiode loopt.';
            $incidents[] = 'Geen geregistreerde gewelds- of ontvluchtingsincidenten in het inrichtingsdossier.';
            $incidents[] = 'Standaard deelname aan leefgroepactiviteiten en kennismaking met sociotherapie.';
        }

        return $incidents;
    }

    /**
     * Generate an authentic, multi-paragraph forensic psychiatric letter from Dr. H. van Beek.
     *
     * @param  array<string, mixed>  $m
     * @param  array<string, mixed>  $archetype
     * @param  array<string, mixed>  $threat
     * @param  array<int, string>  $incidents
     */
    protected function generateDoctorNotes(array $m, array $archetype, array $threat, array $incidents): string
    {
        $paragraphs = [];

        // Paragraph 1: Clinical Orientation & Current Confinement Status
        $p1 = 'Patiënt '.$m['name'].' verblijft thans onder toezicht op afdeling '.$threat['regime'].'. ';
        if ($m['is_in_prison']) {
            $p1 .= 'Ten tijde van deze rapportage bevindt het individu zich in de isoleercel'.(! empty($m['prison_reason']) ? ' wegens "'.$m['prison_reason'].'"' : '').', hetgeen getuigt van aanhoudende refractairiteit jegens het inrichtingskader. ';
        } elseif ($m['is_sedated']) {
            $p1 .= 'Wegens acute agitatie en verzet is patiënt heden platgespoten onder toediening van neuroleptica, waarbij elk contact met de leefgroep tijdelijk is opgeschort. ';
        } elseif ($m['is_on_leave']) {
            $p1 .= 'Het behandelteam heeft recent toestemming verleend voor begeleid verlof buiten de hekken, al blijft de sociotherapeutische bewaking uiterst stringent. ';
        } elseif ($m['is_comatose']) {
            $p1 .= 'Patiënt ligt momenteel ter observatie op de medische intensive care na ernstig fysiek trauma, waardoor therapeutische interactie tijdelijk gestaakt is. ';
        } else {
            $p1 .= 'Tijdens de dagelijkse observatierondes toont patiënt een typisch forensisch gedragspatroon waarin impulsiviteit, waakzaamheid en calculerend vermogen domineren. ';
        }
        $p1 .= 'De vastgestelde dreigingsstatus luidt '.$threat['level'].' (schaal '.$threat['meter'].'%), geclassificeerd als '.$threat['classification'].'.';
        $paragraphs[] = $p1;

        // Paragraph 2: Fysieke Confrontaties & Geweldsincidenten
        $pViolence = [];
        if ($m['fights_won'] > 0 || $m['fights_lost'] > 0) {
            $fightWonText = $m['fights_won'] === 1 ? '1 geweldsconfrontatie' : $m['fights_won'].' geweldsconfrontaties';
            $fightLostText = $m['fights_lost'] === 1 ? '1 incassatie' : $m['fights_lost'].' incassaties';
            $pViolence[] = 'Op de luchtplaats en het sportveld heeft patiënt inmiddels '.$fightWonText.' op zijn naam geschreven (tegenover '.$fightLostText.'). Met een fysieke krachtscore van '.$m['strength'].' dwingt het individu aanzien af bij mede-inwoners.';
        }
        if ($m['arm_wrestle_wins'] > 0) {
            $zegeText = $m['arm_wrestle_wins'] === 1 ? '1 zege' : $m['arm_wrestle_wins'].' zeges';
            $pViolence[] = 'In de ontspanningszaal profileert patiënt zich bij het armpjedrukken ('.$zegeText.'), waarbij informele weddenschappen en dominantievertoon hand in hand gaan.';
        }
        if ($m['kills'] > 0) {
            $killText = $m['kills'] === 1 ? '1 dodelijke liquidatie' : $m['kills'].' dodelijke liquidaties';
            $bulletText = $m['bullets'] === 1 ? '1 patroon' : $m['bullets'].' patronen';
            $pViolence[] = 'Zeer verontrustend zijn de rapporten aangaande '.$killText.' en het voorhanden hebben van scherpe munitie ('.$bulletText.' aangetroffen); het geweten lijkt nagenoeg afwezig.';
        }
        if (! empty($pViolence)) {
            $paragraphs[] = implode(' ', $pViolence);
        }

        // Paragraph 3: Financieel Vermogen & Eigendomsdelicten
        $pFinance = [];
        if ($m['wealth'] >= 25000) {
            $pFinance[] = 'Het financiële rechercheteam van Justitie stuitte op een clandestien vermogen van € '.$m['formatted_wealth'].' (€ '.$m['formatted_cash'].' contant op de man en € '.$m['formatted_bank'].' op afgeschermde rekeningen). Er zijn sterke aanwijzingen voor woekerrentes en witwaspraktijken binnen de muren.';
        }
        if ($m['thefts_succeeded'] > 0) {
            $theftText = $m['thefts_succeeded'] === 1 ? '1 geslaagde ontvreemding' : $m['thefts_succeeded'].' geslaagde ontvreemdingen';
            $pFinance[] = 'Het diefstalregister vermeldt '.$theftText.'. Bewaarders rapporteren dat patiënt systematisch pasjes, sigaretten en gereedschap uit andermans zakken laat verdwijnen.';
        }
        if (! empty($pFinance)) {
            $paragraphs[] = implode(' ', $pFinance);
        }

        // Paragraph 4: Vluchtgevaar, Uitbraakincidenten & Contrabande
        $pEscape = [];
        if ($m['prison_escapes'] > 0 || $m['breakout_attempts'] > 0 || $m['tunnel_progress'] > 0 || $m['spoons_used'] > 0) {
            $breakoutText = $m['breakout_attempts'] === 1 ? '1 uitbraakpoging' : $m['breakout_attempts'].' uitbraakpogingen';
            $escapeText = $m['prison_escapes'] === 1 ? '1 maal' : $m['prison_escapes'].' maal';
            $spoonText = $m['spoons_used'] === 1 ? '1 afgesleten eetlepel' : $m['spoons_used'].' afgesleten eetlepels';
            $tunnelText = ($m['tunnel_progress'] > 0 || $m['spoons_used'] > 0)
                ? ' waarbij onder de celvloer een tunnelgang werd aangetroffen met '.$spoonText
                : '';
            $pEscape[] = 'Het vluchtrisico is acuut: patiënt heeft reeds '.$breakoutText.' ondernomen en '.$escapeText.' de inrichting ontvlucht'.$tunnelText.'.';
        }
        if ($m['palmed_pills'] > 0) {
            $pillText = $m['palmed_pills'] === 1 ? '1 pil verdonkeremaand' : $m['palmed_pills'].' pillen verdonkeremaand';
            $pEscape[] = 'Bij controles van de ziekenboeg bleek patiënt stelselmatig medicatie achterover te drukken (inmiddels '.$pillText.'), vermoedelijk bestemd voor de zwarte handel in het paviljoen.';
        }
        if ($m['hacks_completed'] > 0) {
            $hackText = $m['hacks_completed'] === 1 ? '1 ongeautoriseerde inbraak' : $m['hacks_completed'].' ongeautoriseerde inbraken';
            $pEscape[] = 'De afdeling IT-beveiliging signaleerde '.$hackText.' op het inrichtingsnetwerk, waarbij getracht werd beveiligingscamera\'s en personeelsroosters te manipuleren.';
        }
        if (! empty($pEscape)) {
            $paragraphs[] = implode(' ', $pEscape);
        }

        // Paragraph 5: Syndicaatsstructuur & Arbeidstherapie
        $pSocial = [];
        if (! empty($m['gang_name'])) {
            $pSocial[] = 'Op sociologisch vlak bekleedt patiënt de positie van '.$m['gang_role'].' binnen het gevreesde syndicaat \''.$m['gang_name'].'\' (reputatie: '.$m['formatted_respect'].' respectpunten), hetgeen leidt tot een machtspositie die de gezagsverhoudingen ondermijnt.';
        }
        if ($m['dagbesteding_shifts'] > 0) {
            $shiftText = $m['dagbesteding_shifts'] === 1 ? '1 werkdienst' : $m['dagbesteding_shifts'].' werkdiensten';
            $pSocial[] = 'Positief te vermelden is de inzet bij de dagbesteding: patiënt heeft '.$shiftText.' volbracht en hiermee eerlijk € '.$m['formatted_dagbesteding_earned'].' verdiend, al blijft de sociotherapeut alert op dubbele agenda\'s.';
        }
        if (! empty($pSocial)) {
            $paragraphs[] = implode(' ', $pSocial);
        }

        // Fallback if no specific activities exist
        if (empty($pViolence) && empty($pFinance) && empty($pEscape) && empty($pSocial)) {
            $paragraphs[] = 'Patiënt vertoont typisch kliniekgedrag: zoekt allianties, bouwt reputatie op en probeert de regels van de inrichting naar eigen hand te zetten. Er zijn vooralsnog geen grootschalige ontvluchtings- of geweldsincidenten geregistreerd in het basisdossier.';
        }

        // Final Paragraph: Diagnostic Synthesis & Prognosis by Dr. Van Beek
        $pConclusion = 'Concluderend stelt ondergetekende de diagnose '.$archetype['diagnosis'].' ('.$archetype['dsm_code'].') vast. ';
        if ($m['treatment_score'] >= 70) {
            $pConclusion .= 'De behandelscore bedraagt thans '.$m['treatment_score'].'%, hetgeen wijst op gunstige medewerking, al adviseren wij waakzaamheid tegenover geraffineerde schijnaanpassing. ';
        } elseif ($m['treatment_score'] >= 35) {
            $pConclusion .= 'Met een behandelscore van '.$m['treatment_score'].'% bevindt het individu zich in een wankele tussenfase; zelfstandige vrijheden blijven vooralsnog opgeschort. ';
        } else {
            $pConclusion .= 'Wegens een alarmerend lage behandelscore van '.$m['treatment_score'].'% en herhaald verzet is verhoogde penitentiaire waakzaamheid strikt geboden. ';
        }
        $pConclusion .= 'Voorgeschreven maatregel: '.$archetype['treatment_plan'].' '.$archetype['prognosis'];
        $paragraphs[] = $pConclusion;

        return implode("\n\n", $paragraphs);
    }
}

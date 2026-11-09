<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Models\VaultCode;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use App\Support\TreatmentCaseCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    /**
     * All available psychological dilemma questions.
     */
    public const QUESTION_POOL = [
        1 => [
            'id' => 1,
            'doctor' => 'Dr. H. van Beek (Hoofdpsychiater)',
            'theme' => 'Agressie & Zelfbeheersing',
            'question' => 'Tijdens het luchten duwt een mede-patiënt je per ongeluk en jouw sigaretten vallen in de modder. Hoe reageer je?',
            'options' => [
                'a' => [
                    'text' => 'Ik haal diep adem, tel tot tien en geef rustig aan dat dit niet prettig is.',
                    'score' => 25,
                    'note' => 'Toont uitzonderlijke emotieregulatie en zelfbeheersing.',
                    'respect' => 0,
                ],
                'b' => [
                    'text' => 'Ik eis direct dat hij mij twee nieuwe peuken terugbetaalt, anders praten we na de sluiting.',
                    'score' => 5,
                    'note' => 'Grensoverschrijdend maar toont nog enige verbale communicatie.',
                    'respect' => 1,
                ],
                'c' => [
                    'text' => 'Ik sla hem direct tegen het hekwerk. Niemand raakt mijn peuken aan!',
                    'score' => -30,
                    'note' => 'Ernstige impulsdoorbraak en fysieke agressie jegens mede-inmates.',
                    'respect' => 3,
                ],
                'd' => [
                    'text' => 'Ik zeg niets, glimlach vriendelijk, maar onthoud zijn gezicht voor later.',
                    'score' => -10,
                    'note' => 'Verhullend en manipulatief gedrag; mogelijk wrokgevoelens.',
                    'respect' => 1,
                ],
            ],
        ],
        2 => [
            'id' => 2,
            'doctor' => 'Yvonne (Sociaal Psychiatrisch Verpleegkundige)',
            'theme' => 'Medicatietrouw & Eerlijkheid',
            'question' => 'De zaalwacht vermoedt dat sommige patiënten hun voorgeschreven pillen onder de tong houden om te verhandelen. Wat is jouw mening hierover?',
            'options' => [
                'a' => [
                    'text' => 'Mijn medicatie geeft me juist rust in het hoofd. Ik neem het altijd keurig met water in.',
                    'score' => 25,
                    'note' => 'Uitstekend ziekte-inzicht en compliant medicatiegedrag.',
                    'respect' => 0,
                ],
                'b' => [
                    'text' => 'Ik bemoei me alleen met mijn eigen dossier. Wat anderen doen is niet mijn zaak.',
                    'score' => 10,
                    'note' => 'Houdt afstand van andermans delictgedrag, neutraal.',
                    'respect' => 1,
                ],
                'c' => [
                    'text' => 'Die pillen maken me een zombie. Als ik ze kan verhandelen voor vlaflip doe ik dat.',
                    'score' => -25,
                    'note' => 'Openlijke weigering en handel in voorgeschreven psychofarmaca.',
                    'respect' => 2,
                ],
                'd' => [
                    'text' => 'Misschien moeten jullie betere pillen geven die wel lekker smaken.',
                    'score' => -5,
                    'note' => 'Ontwijkend en cynisch tegenover het behandelteam.',
                    'respect' => 0,
                ],
            ],
        ],
        3 => [
            'id' => 3,
            'doctor' => 'Dr. H. van Beek (Hoofdpsychiater)',
            'theme' => 'Gezag & Bewaking',
            'question' => 'Zaalwacht Henk heeft je cel voor de derde keer deze week overhoop gehaald voor een fouillering. Hoe ga je met deze frustratie om?',
            'options' => [
                'a' => [
                    'text' => 'Ik begrijp dat beveiliging nodig is voor onze veiligheid en werk vriendelijk mee.',
                    'score' => 25,
                    'note' => 'Toont respect voor de geldende kliniekregels en protocollen.',
                    'respect' => 0,
                ],
                'b' => [
                    'text' => 'Ik vraag om het klachtenformulier van de Patiëntencommissie en dien een nette klacht in.',
                    'score' => 15,
                    'note' => 'Gebruikt de formele institutionele kanalen voor frustratieverwerking.',
                    'respect' => 0,
                ],
                'c' => [
                    'text' => 'Ik spuug op de grond en vertel Henk dat hij buiten geen grote jongen meer is.',
                    'score' => -30,
                    'note' => 'Verbaal agressief en bedreigend jegens dienstdoend personeel.',
                    'respect' => 3,
                ],
                'd' => [
                    'text' => 'Ik lach hem uit, want mijn spullen vindt hij toch nooit.',
                    'score' => -20,
                    'note' => 'Uitdagend en suggereert het bezit van contrabande in de cel.',
                    'respect' => 2,
                ],
            ],
        ],
        4 => [
            'id' => 4,
            'doctor' => 'Yvonne (Sociaal Psychiatrisch Verpleegkundige)',
            'theme' => 'Dagbesteding & Houtbewerking',
            'question' => 'Tijdens de werktherapie in de houtwerkplaats breek je per ongeluk een zaagblad. Wat doe je?',
            'options' => [
                'a' => [
                    'text' => 'Ik meld het direct bij de werkmeester en vraag hoe ik een nieuw blad veilig monteer.',
                    'score' => 25,
                    'note' => 'Verantwoordelijk en transparant tijdens arbeidsreïntegratie.',
                    'respect' => 0,
                ],
                'b' => [
                    'text' => 'Ik leg het gebroken zaagblad stiekem in de afvalbak en pak stilletjes een nieuwe.',
                    'score' => 5,
                    'note' => 'Angst voor correctie, maar geen kwaadwillende intentie geconstateerd.',
                    'respect' => 0,
                ],
                'c' => [
                    'text' => 'Ik slijp de scherpe punt stiekem bij om als steekwapen in mijn sok te bewaren.',
                    'score' => -35,
                    'note' => 'Extreem gevaarlijk: poging tot vervaardigen van slag-/steekwapens.',
                    'respect' => 4,
                ],
                'd' => [
                    'text' => 'Ik geef de schuld aan de patiënt naast me. Die stond me af te leiden.',
                    'score' => -15,
                    'note' => 'Externaliseert schuldgevoelens en zoekt conflict met celgenoten.',
                    'respect' => 0,
                ],
            ],
        ],
        5 => [
            'id' => 5,
            'doctor' => 'Dr. H. van Beek (Hoofdpsychiater)',
            'theme' => 'Bendes & Crimineel Netwerk',
            'question' => 'Er zijn geruchten over bendevorming en onderlinge loyaliteiten op Afdeling 4. Wat is jouw relatie met de bendes?',
            'options' => [
                'a' => [
                    'text' => 'Ik distantieer me volledig van criminele bendes. Ik focus me 100% op mijn eigen herstel.',
                    'score' => 25,
                    'note' => 'Afstand nemend van criminele subculturen en negatieve invloeden.',
                    'respect' => 0,
                ],
                'b' => [
                    'text' => 'Ik praat met iedereen op de afdeling om de vrede te bewaren, maar ik kies geen partij.',
                    'score' => 15,
                    'note' => 'Zoekt diplomatieke balans binnen de sociale afdelingsstructuur.',
                    'respect' => 1,
                ],
                'c' => [
                    'text' => 'Mijn bende is mijn familie. Zonder ons zou de kliniek allang in brand staan.',
                    'score' => -30,
                    'note' => 'Diepe verwevenheid met georganiseerde bendes en anti-sociaal netwerk.',
                    'respect' => 3,
                ],
                'd' => [
                    'text' => 'Wie niet voor ons is, is tegen ons. Meer hoeft u niet te weten dokter.',
                    'score' => -25,
                    'note' => 'Dreigend en weigert opening van zaken over bendeactiviteiten.',
                    'respect' => 2,
                ],
            ],
        ],
        6 => [
            'id' => 6,
            'doctor' => 'Yvonne (Sociaal Psychiatrisch Verpleegkundige)',
            'theme' => 'Verlof Motivering & Toekomstperspectief',
            'question' => 'Stel dat het behandelteam jou vandaag Begeleid Verlof buiten de hekken toekent, hoe ga je die tijd besteden?',
            'options' => [
                'a' => [
                    'text' => 'Ik wil rustig een frisse neus halen in het park onder begeleiding en genieten van de natuur.',
                    'score' => 25,
                    'note' => 'Gezonde ontspanning en passend reïntegratiedoel geformuleerd.',
                    'respect' => 0,
                ],
                'b' => [
                    'text' => 'Ik wil even langs de supermarkt om wat lekkers en een tijdschrift te kopen.',
                    'score' => 20,
                    'note' => 'Alledaagse sociale vaardigheden oefenen in maatschappelijke context.',
                    'respect' => 0,
                ],
                'c' => [
                    'text' => 'Ik glip direct weg bij de begeleider om mijn oude maten op het station te ontmoeten.',
                    'score' => -35,
                    'note' => 'Expliciet voornemen tot ontvluchting en recidive buiten de kliniek!',
                    'respect' => 4,
                ],
                'd' => [
                    'text' => 'Geld uitgeven en kijken wat er te halen valt in de stad.',
                    'score' => -15,
                    'note' => 'Materiële focus en verhoogd risico op impulsdelicten.',
                    'respect' => 1,
                ],
            ],
        ],
    ];

    /**
     * Outside accompanied leave activities.
     */
    public const LEAVE_ACTIONS = [
        'supermarket' => [
            'id' => 'supermarket',
            'name' => 'Wegglippen bij de Supermarkt',
            'desc' => 'Tijdens het boodschappen doen leid je de sociotherapeut af en scoor je snel contanten en buit.',
            'energy' => 12,
            'min_cash' => 2500,
            'max_cash' => 7500,
            'xp' => 90,
            'respect' => 2,
            'icon' => 'fa-cart-shopping',
            'color' => 'emerald',
        ],
        'station' => [
            'id' => 'station',
            'name' => 'Onderwereld Contact op het Station',
            'desc' => 'Op het stationsplein ontmoet je stiekem een oude contactpersoon voor contanten en zeldzame buit.',
            'energy' => 16,
            'min_stars' => 0,
            'max_stars' => 1,
            'min_cash' => 1500,
            'max_cash' => 4000,
            'xp' => 140,
            'respect' => 4,
            'icon' => 'fa-train-subway',
            'color' => 'purple',
        ],
        'locker' => [
            'id' => 'locker',
            'name' => 'Smokkeltas uit Stationkluisje Ophalen',
            'desc' => 'Haal een vooraf klaargezette smokkeltas of geheime kluiscode voucher op uit kluisje #42.',
            'energy' => 15,
            'min_cash' => 3000,
            'max_cash' => 6000,
            'xp' => 120,
            'respect' => 3,
            'icon' => 'fa-suitcase',
            'color' => 'amber',
        ],
    ];

    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected PrisonService $prison,
        protected CooldownService $cooldownService,
    ) {}

    /**
     * Show treatment consultation room and leave activities.
     */
    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $isOnCooldown = $this->cooldownService->isOnCooldown($character, 'treatment_session');
        $remainingCooldown = $this->cooldownService->getRemainingSeconds($character, 'treatment_session');

        $isLeaveActionOnCooldown = $this->cooldownService->isOnCooldown($character, 'treatment_leave_action');
        $remainingLeaveCooldown = $this->cooldownService->getRemainingSeconds($character, 'treatment_leave_action');

        // Fetch today's unique case from the 365-day psychiatric catalog
        $dayOfYear = (int) now()->dayOfYear;
        $dailyCase = TreatmentCaseCatalog::getCaseForDay($dayOfYear);
        $activeQuestions = $dailyCase['questions'];

        $psychiatricReport = $character->getPsychiatricReport();

        return view('game.treatment.index', [
            'character' => $character,
            'dailyCase' => $dailyCase,
            'activeQuestions' => $activeQuestions,
            'isOnCooldown' => $isOnCooldown,
            'remainingCooldown' => $remainingCooldown,
            'isLeaveActionOnCooldown' => $isLeaveActionOnCooldown,
            'remainingLeaveCooldown' => $remainingLeaveCooldown,
            'psychiatricReport' => $psychiatricReport,
            'leaveActions' => self::LEAVE_ACTIONS,
            'treatmentSkipCost' => $this->cooldownService->getSkipCost('treatment_session'),
        ]);
    }

    /**
     * Submit conversation dialogue answers and evaluate psychological report.
     */
    public function submit(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet op behandelaarsgesprek!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->cooldownService->isOnCooldown($character, 'treatment_session')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'treatment_session');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ Je hebt vandaag al gesproken met je behandelaar! Wacht nog {$remaining}s (~{$mins} min) of reset direct met Sterren ⭐.");
        }

        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:3'],
            'answers.*' => ['required', 'string', 'in:a,b,c,d'],
        ]);

        $answers = $validated['answers'];
        $dayOfYear = (int) now()->dayOfYear;
        $dailyCase = TreatmentCaseCatalog::getCaseForDay($dayOfYear);
        $questions = $dailyCase['questions'];

        $totalScoreChange = 0;
        $totalRespect = 0;
        $doctorNotes = [];

        foreach ($questions as $qId => $question) {
            $selectedOpt = $answers[$qId] ?? 'a';
            $choice = $question['options'][$selectedOpt] ?? $question['options']['a'];

            $totalScoreChange += $choice['score'];
            $totalRespect += $choice['respect'];
            $doctorNotes[] = "• {$question['theme']}: {$choice['note']}";
        }

        return DB::transaction(function () use ($character, $totalScoreChange, $totalRespect, $doctorNotes) {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $locked) {
                return redirect()->route('dashboard');
            }

            if ($locked->isImprisoned()) {
                return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet op behandelaarsgesprek!');
            }

            if ($locked->isComatose() || $locked->needsReviveSerum()) {
                return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if ($this->cooldownService->isOnCooldown($locked, 'treatment_session')) {
                $remaining = $this->cooldownService->getRemainingSeconds($locked, 'treatment_session');
                $mins = ceil($remaining / 60);

                return back()->with('error', "⏳ Je hebt vandaag al gesproken met je behandelaar! Wacht nog {$remaining}s (~{$mins} min) of reset direct met Sterren ⭐.");
            }

            // Base score adjustment (clamped between 0 and 100)
            $currentScore = $locked->treatment_score ?? 50;
            $newScore = max(5, min(100, $currentScore + $totalScoreChange));
            $locked->treatment_score = $newScore;
            $locked->last_treatment_at = now();

            // 2 hours cooldown between evaluations
            $this->cooldownService->setCooldown($locked, 'treatment_session', 7200);

            // Apply respect modifications
            if ($totalRespect > 0) {
                $locked->addRespect($totalRespect);
            }

            // Outcome branch based on performance
            if ($newScore >= 60) {
                // POSITIEF ADVIES: BEGELEID VERLOF TOEGEKEND (3 uur)
                $leaveHours = 3;
                $locked->leave_until = now()->addHours($leaveHours);
                $locked->sedated_until = null;

                $cashReward = 2500;
                $this->economy->addCash($locked, $cashReward);
                $this->level->addXp($locked, 120);

                $locked->treatment_history = [
                    'outcome' => 'positive',
                    'title' => 'Positief Behandeladvies (Verlof Toegekend)',
                    'score' => $newScore,
                    'notes' => $doctorNotes,
                    'date' => now()->toDateTimeString(),
                ];
                $locked->save();

                GameNotification::create([
                    'character_id' => $locked->id,
                    'type' => 'treatment_pass',
                    'icon' => 'fa-brain',
                    'title' => '🎉 Positief Behandeladvies: 3 Uur Begeleid Verlof!',
                    'message' => 'Dr. H. van Beek en het behandelteam zijn onder de indruk van je medewerking! Je hebt 3 uur Begeleid Verlof toegekend gekregen inclusief €2.500 zakgeld toelage.',
                ]);

                session()->forget('treatment_questions');

                return back()->with('success', "🟢 POSITIEF ADVIES! Dr. H. van Beek: 'Patiënt toont uitstekende stabiliteit en ziekte-inzicht.' Je hebt 3 UUR BEGELEID VERLOF toegekend gekregen buiten de hekken! +€2.500 zakgeld, +120 XP.");
            }

            if ($newScore >= 35) {
                // ORANJE STATUS: OBSERVATIE / GEEN VERLOF
                $locked->leave_until = null;
                $this->level->addXp($locked, 50);

                $locked->treatment_history = [
                    'outcome' => 'neutral',
                    'title' => 'Observatiestatus (Verlof Uitgesteld)',
                    'score' => $newScore,
                    'notes' => $doctorNotes,
                    'date' => now()->toDateTimeString(),
                ];
                $locked->save();

                session()->forget('treatment_questions');

                return back()->with('error', "🟡 OBSERVATIESTATUS! Behandelteam: 'Patiënt is nog te wisselvallig voor zelfstandig verlof buiten de hekken.' Geen sanctie, maar blijf meewerken aan je herstelplan. +50 XP troostprijs.");
            }

            // ROOD STATUS: AGRESSIEF / SANCTIE (KALMERING)
            $locked->leave_until = null;
            $locked->sedated_until = now()->addHour();
            // Reduce energy via EnergyService
            $energyToDeduct = min((int) ($locked->energy ?? 0), 30);
            if ($energyToDeduct > 0) {
                $this->energy->consume($locked, $energyToDeduct);
            }

            $locked->treatment_history = [
                'outcome' => 'negative',
                'title' => 'Negatief Behandeladvies & Sanctie (Verplichte Medicatie)',
                'score' => $newScore,
                'notes' => $doctorNotes,
                'date' => now()->toDateTimeString(),
            ];
            $locked->save();

            GameNotification::create([
                'character_id' => $locked->id,
                'type' => 'treatment_fail',
                'icon' => 'fa-triangle-exclamation',
                'title' => '⚠️ Negatief Behandeladvies: Verplichte Kalmeringsinjectie!',
                'message' => 'Wegens opstandig en anti-sociaal gedrag tijdens het behandelaarsgesprek heeft het medisch team 1 uur verplichte kalmering voorgeschreven.',
            ]);

            session()->forget('treatment_questions');

            return back()->with('error', '🔴 NEGATIEF ADVIES! Dr. H. van Beek: "Patiënt vertoont ernstige agressie en impulsdoorbraken." Je hebt een verplichte kalmeringsinjectie gekregen (-30 Energie, 1 uur vermoeidheid).');
        });
    }

    /**
     * Perform an accompanied leave activity outside the clinic walls.
     */
    public function doLeaveAction(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen verlofactiviteiten uitvoeren!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if (! $character->isOnLeave()) {
            return back()->with('error', 'Je hebt momenteel geen actief Begeleid Verlof! Slaag eerst voor je Behandelaarsgesprek.');
        }

        if ($character->isSedated()) {
            return back()->with('error', 'Je bent te versuft door de kalmeringsmedicatie om naar buiten te gaan!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'treatment_leave_action')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'treatment_leave_action');

            return back()->with('error', "⏳ Even wachten tot de begeleider de andere kant op kijkt! Wacht nog {$remaining}s.");
        }

        $validated = $request->validate([
            'action_id' => ['required', 'string', 'in:supermarket,station,locker'],
        ]);

        $action = self::LEAVE_ACTIONS[$validated['action_id']];

        return DB::transaction(function () use ($character, $action) {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $locked) {
                return redirect()->route('dashboard');
            }

            if ($locked->isImprisoned()) {
                return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen verlofactiviteiten uitvoeren!');
            }

            if ($locked->isComatose() || $locked->needsReviveSerum()) {
                return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if (! $locked->isOnLeave()) {
                return back()->with('error', 'Je hebt momenteel geen actief Begeleid Verlof! Slaag eerst voor je Behandelaarsgesprek.');
            }

            if ($locked->isSedated()) {
                return back()->with('error', 'Je bent te versuft door de kalmeringsmedicatie om naar buiten te gaan!');
            }

            if ($this->cooldownService->isOnCooldown($locked, 'treatment_leave_action')) {
                $remaining = $this->cooldownService->getRemainingSeconds($locked, 'treatment_leave_action');

                return back()->with('error', "⏳ Even wachten tot de begeleider de andere kant op kijkt! Wacht nog {$remaining}s.");
            }

            if (! $this->energy->consume($locked, $action['energy'])) {
                return back()->with('error', "Je hebt minimaal {$action['energy']} energie nodig voor deze verlofactiviteit!");
            }

            // Set 3 minute cooldown between leave activities
            $this->cooldownService->setCooldown($locked, 'treatment_leave_action', 180);

            $rewards = [];
            $xp = $action['xp'];
            $respect = $action['respect'];

            if ($action['id'] === 'supermarket') {
                $cash = rand($action['min_cash'], $action['max_cash']);
                $this->economy->addCash($locked, $cash);
                $rewards[] = '€'.number_format($cash, 0, ',', '.').' contant geld gerold 💵';
            } elseif ($action['id'] === 'station') {
                $stars = (rand(1, 100) <= 25) ? 1 : 0;
                $cash = rand($action['min_cash'], $action['max_cash']);
                if ($stars > 0) {
                    $locked->stars = ($locked->stars ?? 0) + $stars;
                    $rewards[] = "{$stars} Exclusieve Ster ⭐";
                }
                $this->economy->addCash($locked, $cash);
                $rewards[] = '€'.number_format($cash, 0, ',', '.').' contanten 💵';
            } elseif ($action['id'] === 'locker') {
                $cash = rand($action['min_cash'], $action['max_cash']);
                $this->economy->addCash($locked, $cash);
                $rewards[] = '€'.number_format($cash, 0, ',', '.').' contanten 💵';

                // 50% chance for vault code, 50% for unopened bag
                if (rand(1, 100) <= 50) {
                    $codeStr = VaultCode::generateUniqueCode('VERLOF');
                    VaultCode::create([
                        'code' => $codeStr,
                        'reward_cash' => rand(3000, 8000),
                        'reward_stars' => (rand(1, 100) <= 20 ? 1 : 0),
                        'reward_tokens' => 2,
                        'character_id' => $locked->id,
                        'is_redeemed' => false,
                    ]);
                    $rewards[] = "Geheime Kluiscode ({$codeStr}) 🔐";
                } else {
                    $locked->unopened_bags = ($locked->unopened_bags ?? 0) + 1;
                    $rewards[] = '1x Smokkelkoffer 🧳';
                }
            }

            $this->level->addXp($locked, $xp);
            $locked->addRespect($respect);
            $locked->leave_trips_count = ($locked->leave_trips_count ?? 0) + 1;
            $locked->save();

            $rewardText = implode(' + ', $rewards);

            return back()->with('success', "🏙️ SUCCESVOL UITGEVOERD! Buit: {$rewardText} + {$xp} XP + {$respect} Respect!");
        });
    }
}

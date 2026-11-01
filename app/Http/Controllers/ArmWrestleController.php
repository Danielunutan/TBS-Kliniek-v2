<?php

namespace App\Http\Controllers;

use App\Models\ArmWrestleMatch;
use App\Models\Character;
use App\Models\CharacterArmWrestleStat;
use App\Models\GameNotification;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArmWrestleController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Get the defined NPC opponents catalog.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getNpcOpponents(): array
    {
        return [
            'sjonnie' => [
                'key' => 'sjonnie',
                'name' => 'Sjonnie de Schoffel',
                'role' => 'Afdelingsveger & Gangveger',
                'description' => 'Veegt al vier jaar de gangen van de kliniek. Beschikt over taaie polsen door het dagelijks hanteren van zware bezems.',
                'quote' => 'Ik veeg al jaren de vloer aan met nieuwkomers. Kom maar op aan de tafel!',
                'min_strength' => 0,
                'npc_strength' => 12,
                'npc_stamina' => 10,
                'bet' => 1500,
                'win_payout' => 3000,
                'xp' => 15,
                'respect' => 2,
                'difficulty' => 'Beginner',
                'difficulty_color' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                'icon' => 'fa-broom',
                'icon_style' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60',
            ],
            'karel' => [
                'key' => 'karel',
                'name' => 'Karel de Kogelstoter',
                'role' => 'Krachthonk Stamgast',
                'description' => 'Heeft in zijn glorietijd zilver gehaald op het districtskampioenschap kogelstoten. Traint dagelijks met betonnen tegels.',
                'quote' => 'Ik buig tralies voor het ontbijt. Leg je contanten maar op het hout.',
                'min_strength' => 20,
                'npc_strength' => 35,
                'npc_stamina' => 30,
                'bet' => 5000,
                'win_payout' => 10000,
                'xp' => 25,
                'respect' => 5,
                'difficulty' => 'Gemiddeld',
                'difficulty_color' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                'icon' => 'fa-dumbbell',
                'icon_style' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/60',
            ],
            'bennie' => [
                'key' => 'bennie',
                'name' => 'Bennie de Beuker',
                'role' => 'Voormalig Portiersbaas',
                'description' => 'Gevreesd op de hele luchtplaats. Zelfs zaalwachten houden liever twee meter afstand wanneer Bennie naar de kantine loopt.',
                'quote' => 'Er zijn er al velen gillend naar Zuster Brenda gestuurd. Durf je nog steeds?',
                'min_strength' => 50,
                'npc_strength' => 75,
                'npc_stamina' => 65,
                'bet' => 20000,
                'win_payout' => 40000,
                'xp' => 40,
                'respect' => 10,
                'difficulty' => 'Zwaar',
                'difficulty_color' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                'icon' => 'fa-hand-fist',
                'icon_style' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800/60',
            ],
            'mike' => [
                'key' => 'mike',
                'name' => 'Big Mike de Portier',
                'role' => 'De Reus van de Buitendeur',
                'description' => 'De onbetwiste koning van de tafel. Met onderarmen zo dik als heipalen heeft nog nooit iemand Mike weten te pinnen.',
                'quote' => 'Wie mijn arm tegen de tafel slaat, verdient voor eeuwig het respect van de hele inrichting.',
                'min_strength' => 100,
                'npc_strength' => 140,
                'npc_stamina' => 120,
                'bet' => 75000,
                'win_payout' => 150000,
                'xp' => 60,
                'respect' => 20,
                'difficulty' => 'Eindbaas',
                'difficulty_color' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                'icon' => 'fa-skull',
                'icon_style' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800/60',
            ],
        ];
    }

    /**
     * Show the arm wrestling arena page.
     */
    public function index(): View
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;
        if (! $character) {
            abort(404, 'Karakter niet gevonden.');
        }

        $stat = CharacterArmWrestleStat::firstOrCreate(
            ['character_id' => $character->id],
            [
                'wins' => 0,
                'losses' => 0,
                'current_streak' => 0,
                'best_streak' => 0,
                'total_profit' => 0,
            ]
        );

        $npcs = self::getNpcOpponents();

        // Open PvP challenges from other players
        $openChallenges = ArmWrestleMatch::with('challenger')
            ->where('type', 'pvp')
            ->where('status', 'open')
            ->where('challenger_id', '!=', $character->id)
            ->latest()
            ->take(8)
            ->get();

        // Own open challenge (if any)
        $myOpenChallenge = ArmWrestleMatch::where('type', 'pvp')
            ->where('status', 'open')
            ->where('challenger_id', $character->id)
            ->first();

        // Recent matches
        $recentMatches = ArmWrestleMatch::with(['challenger', 'opponent', 'winner'])
            ->where('status', 'completed')
            ->latest()
            ->take(8)
            ->get();

        // Top 5 Arena Champions
        $topChampions = CharacterArmWrestleStat::with('character')
            ->where('wins', '>', 0)
            ->orderByDesc('wins')
            ->orderByDesc('best_streak')
            ->take(5)
            ->get();

        $cooldownRemaining = $this->cooldownService->getRemainingSeconds($character, 'arm_wrestling');

        return view('game.armwrestling.index', compact(
            'character',
            'stat',
            'npcs',
            'openChallenges',
            'myOpenChallenge',
            'recentMatches',
            'topChampions',
            'cooldownRemaining'
        ));
    }

    /**
     * Resolve an interactive NPC match.
     */
    public function finishNpc(Request $request): JsonResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;
        if (! $character) {
            return response()->json([
                'success' => false,
                'message' => 'Geen personage gevonden.',
            ], 403);
        }

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je niet armpjedrukken!',
            ], 422);
        }

        if ($character->isComatose()) {
            return response()->json([
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
            ], 422);
        }

        $character->cooldownsMap = null;
        $remaining = $this->cooldownService->getRemainingSeconds($character, 'arm_wrestling');
        if ($remaining > 0) {
            // Check if this is a duplicate submission for a match processed in the last 4 seconds
            $recentMatch = ArmWrestleMatch::where('challenger_id', $character->id)
                ->where('type', 'npc')
                ->where('created_at', '>=', now()->subSeconds(2))
                ->latest()
                ->first();

            if ($recentMatch) {
                $isWinner = $recentMatch->winner_id === $character->id;

                return response()->json([
                    'success' => true,
                    'won' => $isWinner,
                    'title' => $isWinner ? 'KNAL! Tafelwinst!' : 'Verloren aan de Tafel...',
                    'message' => $isWinner ? 'Je hebt dit duel gewonnen en de beloning ontvangen!' : 'Dit duel is verwerkt.',
                    'cash' => $character->cash,
                    'energy' => $character->energy,
                    'cooldown' => $remaining,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Je spieren moeten nog even bijkomen van de vorige partij! Wacht nog {$remaining} seconden.",
            ], 422);
        }

        $validated = $request->validate([
            'npc_key' => ['required', 'string', 'in:sjonnie,karel,bennie,mike'],
            'won' => ['required', 'boolean'],
            'clicks' => ['required', 'integer', 'min:0'],
            'duration_ms' => ['nullable', 'integer'],
            'duration_seconds' => ['nullable', 'integer'],
        ]);

        $npcs = self::getNpcOpponents();
        $npc = $npcs[$validated['npc_key']];

        if ($character->getEffectiveStrength() < $npc['min_strength']) {
            return response()->json([
                'success' => false,
                'message' => "Je hebt minimaal {$npc['min_strength']} Kracht nodig om tegen {$npc['name']} te mogen aantreden!",
            ], 422);
        }

        $bet = (int) $npc['bet'];

        return DB::transaction(function () use ($character, $npc, $validated, $bet) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($lockedCharacter->cash < $bet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt onvoldoende contant geld op zak voor deze inzet (€'.number_format($bet, 0, ',', '.').')!',
                ], 422);
            }

            if (! $this->energyService->consume($lockedCharacter, 5)) {
                // Check if this was caused by a duplicate request after energy was already deducted
                $recentMatch = ArmWrestleMatch::where('challenger_id', $lockedCharacter->id)
                    ->where('type', 'npc')
                    ->where('created_at', '>=', now()->subSeconds(2))
                    ->latest()
                    ->first();

                if ($recentMatch) {
                    $isWinner = $recentMatch->winner_id === $lockedCharacter->id;

                    return response()->json([
                        'success' => true,
                        'won' => $isWinner,
                        'title' => $isWinner ? 'KNAL! Tafelwinst!' : 'Verloren aan de Tafel...',
                        'message' => $isWinner ? 'Je hebt dit duel gewonnen en de beloning ontvangen!' : 'Dit duel is verwerkt.',
                        'cash' => $lockedCharacter->cash,
                        'energy' => $lockedCharacter->energy,
                        'cooldown' => $this->cooldownService->getRemainingSeconds($lockedCharacter, 'arm_wrestling'),
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt minimaal 5 Energie nodig om een armpjedrukduel aan te gaan!',
                ], 422);
            }

            // Deduct cash wager
            $lockedCharacter->decrement('cash', $bet);

            // Set cooldown immediately upon match resolution (20 seconds)
            $this->cooldownService->setCooldown($lockedCharacter, 'arm_wrestling', 20);

            $rawDurationMs = array_key_exists('duration_ms', $validated) && $validated['duration_ms'] !== null
                ? (int) $validated['duration_ms']
                : ((int) ($validated['duration_seconds'] ?? 1) * 1000);
            $durationMs = max(200, $rawDurationMs);
            $clicks = max(0, (int) $validated['clicks']);

            // Validate minimum required clicks and duration based on NPC tier and player strength ratio
            $playerStrength = max(1, (int) ($lockedCharacter->strength ?? 10));
            $npcStrength = max(1, (int) ($npc['npc_strength'] ?? 12));
            $isOverpoweredPlayer = $playerStrength >= ($npcStrength * 2);

            $minClicks = $isOverpoweredPlayer ? 4 : match ($npc['key']) {
                'mike' => 15,
                'bennie' => 12,
                'karel' => 8,
                default => 5,
            };
            $minDuration = $isOverpoweredPlayer ? 500 : match ($npc['key']) {
                'mike' => 2000,
                'bennie' => 1500,
                'karel' => 1000,
                default => 800,
            };

            // Honor client win only when click count and duration satisfy anti-exploit thresholds
            $clientWon = (bool) $validated['won'];
            $isWon = $clientWon && $rawDurationMs >= $minDuration && $clicks >= $minClicks;

            /** @var CharacterArmWrestleStat $stat */
            $stat = CharacterArmWrestleStat::firstOrCreate(['character_id' => $lockedCharacter->id]);

            if ($isWon) {
                // Calculate rewards
                $profit = $npc['win_payout'] - $npc['bet'];
                $lockedCharacter->increment('cash', $npc['win_payout']);
                $lockedCharacter->addRespect($npc['respect']);
                $this->level->addXp($lockedCharacter, $npc['xp']);

                $maxStrength = $lockedCharacter->getMaxStatForLevel();
                $strMessage = '';
                if (($lockedCharacter->strength ?? 10) < $maxStrength && rand(1, 100) <= 50) {
                    $lockedCharacter->increment('strength', 1);
                    $strMessage = ' Tevens +1 Kracht verdiend door deze prestatie!';
                }

                $stat->wins++;
                $stat->current_streak++;
                if ($stat->current_streak > $stat->best_streak) {
                    $stat->best_streak = $stat->current_streak;
                }
                $stat->total_profit += $profit;
                $stat->save();

                ArmWrestleMatch::create([
                    'challenger_id' => $lockedCharacter->id,
                    'type' => 'npc',
                    'npc_key' => $npc['key'],
                    'bet_amount' => $npc['bet'],
                    'winner_id' => $lockedCharacter->id,
                    'status' => 'completed',
                    'xp_earned' => $npc['xp'],
                    'respect_earned' => $npc['respect'],
                    'challenger_clicks' => $clicks,
                    'match_duration_ms' => $durationMs,
                ]);

                $freshCharacter = $lockedCharacter->fresh();

                return response()->json([
                    'success' => true,
                    'won' => true,
                    'title' => 'KNAL! Tafelwinst!',
                    'message' => "Je hebt de arm van {$npc['name']} met brute kracht op het hout geslagen! +€".number_format($profit, 0, ',', '.')." winst, +{$npc['xp']} XP en +{$npc['respect']} Respect!{$strMessage}",
                    'cash' => $freshCharacter->cash,
                    'energy' => $freshCharacter->energy,
                    'strength' => $freshCharacter->getEffectiveStrength(),
                    'wins' => $stat->wins,
                    'streak' => $stat->current_streak,
                    'cooldown' => 20,
                ]);
            }

            // Lost
            $stat->losses++;
            $stat->current_streak = 0;
            $stat->total_profit -= $npc['bet'];
            $stat->save();

            ArmWrestleMatch::create([
                'challenger_id' => $lockedCharacter->id,
                'type' => 'npc',
                'npc_key' => $npc['key'],
                'bet_amount' => $npc['bet'],
                'winner_id' => null,
                'status' => 'completed',
                'xp_earned' => 5,
                'respect_earned' => 0,
                'challenger_clicks' => $clicks,
                'match_duration_ms' => $durationMs,
            ]);

            // Give consolation 5 XP
            $this->level->addXp($lockedCharacter, 5);

            $reason = ($clientWon && ! $isWon)
                ? "{$npc['name']} pareerde je aanval op het allerlaatste moment met zijn stalen pols! Je verliest je inzet van €".number_format($npc['bet'], 0, ',', '.').'.'
                : "{$npc['name']} bleek te sterk en heeft jouw arm tegen het hout gedrukt! Je verliest je inzet van €".number_format($npc['bet'], 0, ',', '.').'. Wel +5 troost-XP.';

            $freshCharacter = $lockedCharacter->fresh();

            return response()->json([
                'success' => true,
                'won' => false,
                'title' => 'Verloren aan de Tafel...',
                'message' => $reason,
                'cash' => $freshCharacter->cash,
                'energy' => $freshCharacter->energy,
                'strength' => $freshCharacter->getEffectiveStrength(),
                'losses' => $stat->losses,
                'streak' => 0,
                'cooldown' => 20,
            ]);
        });
    }

    /**
     * Create an open PvP table challenge.
     */
    public function createChallenge(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;
        if (! $character) {
            return back()->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen uitdagingen plaatsen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'bet_amount' => ['required', 'integer', 'in:2500,5000,10000,25000,50000,100000'],
        ]);

        $bet = (int) $validated['bet_amount'];

        return DB::transaction(function () use ($character, $bet) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            // Limit to 1 open challenge at a time per character under lock
            $existing = ArmWrestleMatch::where('challenger_id', $lockedCharacter->id)
                ->where('type', 'pvp')
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return back()->with('error', 'Je hebt al een open uitdaging op de tafel liggen! Annuleer deze eerst als je een ander bedrag wilt inzetten.');
            }

            if ($lockedCharacter->cash < $bet) {
                return back()->with('error', 'Je hebt onvoldoende contant geld op zak om deze uitdaging op tafel te leggen (€'.number_format($bet, 0, ',', '.').').');
            }

            $lockedCharacter->decrement('cash', $bet);

            ArmWrestleMatch::create([
                'challenger_id' => $lockedCharacter->id,
                'type' => 'pvp',
                'bet_amount' => $bet,
                'status' => 'open',
            ]);

            return back()->with('success', 'Jouw uitdaging van €'.number_format($bet, 0, ',', '.').' ligt open op de armpjedruktafel! Wacht tot een medepatiënt hem accepteert.');
        });
    }

    /**
     * Cancel an open challenge and refund cash.
     */
    public function cancelChallenge(int $id): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;
        if (! $character) {
            return back()->with('error', 'Geen personage gevonden.');
        }

        return DB::transaction(function () use ($character, $id) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            /** @var ArmWrestleMatch|null $match */
            $match = ArmWrestleMatch::where('id', $id)
                ->where('challenger_id', $lockedCharacter->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (! $match) {
                return back()->with('error', 'Deze uitdaging bestaat niet meer of is al geaccepteerd.');
            }

            $match->status = 'cancelled';
            $match->save();

            $lockedCharacter->increment('cash', $match->bet_amount);

            return back()->with('success', 'Je uitdaging is geannuleerd en je inzet van €'.number_format($match->bet_amount, 0, ',', '.').' is teruggestort op zak.');
        });
    }

    /**
     * Accept another player's challenge.
     */
    public function acceptChallenge(Request $request, int $id): RedirectResponse
    {
        /** @var Character|null $opponent */
        $opponent = Auth::user()?->character;
        if (! $opponent) {
            return back()->with('error', 'Geen personage gevonden.');
        }

        if ($opponent->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen uitdagingen aannemen!');
        }

        if ($opponent->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $opponent->cooldownsMap = null;
        $remaining = $this->cooldownService->getRemainingSeconds($opponent, 'arm_wrestling');
        if ($remaining > 0) {
            return back()->with('error', "Je armen moeten nog even bijkomen van de vorige partij! Wacht nog {$remaining} seconden.");
        }

        if ($opponent->energy < 5) {
            return back()->with('error', 'Je hebt minimaal 5 Energie nodig om deze uitdaging aan te gaan!');
        }

        return DB::transaction(function () use ($opponent, $id) {
            /** @var ArmWrestleMatch|null $match */
            $match = ArmWrestleMatch::with('challenger')
                ->where('id', $id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (! $match) {
                return back()->with('error', 'Deze uitdaging is zojuist al geaccepteerd of geannuleerd door de uitdager.');
            }

            if ($match->challenger_id === $opponent->id) {
                return back()->with('error', 'Je kunt je eigen uitdaging niet accepteren!');
            }

            $bet = (int) $match->bet_amount;
            $pot = $bet * 2;

            $lockIds = [$opponent->id, $match->challenger_id];
            sort($lockIds);
            $lockedChars = Character::whereIn('id', $lockIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            /** @var Character|null $lockedOpponent */
            $lockedOpponent = $lockedChars->get($opponent->id);
            /** @var Character|null $challenger */
            $challenger = $lockedChars->get($match->challenger_id);

            if (! $lockedOpponent || ! $challenger) {
                return back()->with('error', 'Uitdager niet gevonden.');
            }

            if ($lockedOpponent->cash < $bet) {
                return back()->with('error', 'Je hebt onvoldoende contant geld op zak om deze uitdaging aan te gaan (€'.number_format($bet, 0, ',', '.').').');
            }

            if (! $this->energyService->consume($lockedOpponent, 5)) {
                return back()->with('error', 'Je hebt minimaal 5 Energie nodig om deze uitdaging aan te gaan!');
            }

            $match->status = 'completed';
            $match->save();

            $lockedOpponent->decrement('cash', $bet);

            // Set cooldown for opponent
            $this->cooldownService->setCooldown($lockedOpponent, 'arm_wrestling', 20);

            // Calculate battle scores: Strength (60%) + Stamina (25%) + Discipline (15%) + RNG variance
            $challengerScore = ($challenger->getEffectiveStrength() * 1.5) + ($challenger->getEffectiveStamina() * 0.5) + ($challenger->getEffectiveDiscipline() * 0.4) + rand(1, 20);
            $opponentScore = ($lockedOpponent->getEffectiveStrength() * 1.5) + ($lockedOpponent->getEffectiveStamina() * 0.5) + ($lockedOpponent->getEffectiveDiscipline() * 0.4) + rand(1, 20);

            $winner = $opponentScore >= $challengerScore ? $lockedOpponent : $challenger;
            $loser = $winner->id === $lockedOpponent->id ? $challenger : $lockedOpponent;

            // Payout winner
            $winner->increment('cash', $pot);
            $winner->addRespect(15);
            $this->level->addXp($winner, 50);

            $maxStrength = $winner->getMaxStatForLevel();
            if (($winner->strength ?? 10) < $maxStrength) {
                $winner->increment('strength', 1);
            }

            // Loser gets consolation XP
            $this->level->addXp($loser, 15);

            // Update stats
            $winnerStat = CharacterArmWrestleStat::firstOrCreate(['character_id' => $winner->id]);
            $winnerStat->wins++;
            $winnerStat->current_streak++;
            if ($winnerStat->current_streak > $winnerStat->best_streak) {
                $winnerStat->best_streak = $winnerStat->current_streak;
            }
            $winnerStat->total_profit += $match->bet_amount;
            $winnerStat->save();

            $loserStat = CharacterArmWrestleStat::firstOrCreate(['character_id' => $loser->id]);
            $loserStat->losses++;
            $loserStat->current_streak = 0;
            $loserStat->total_profit -= $match->bet_amount;
            $loserStat->save();

            $match->update([
                'opponent_id' => $lockedOpponent->id,
                'winner_id' => $winner->id,
                'status' => 'completed',
                'xp_earned' => 50,
                'respect_earned' => 15,
            ]);

            // Notify challenger
            GameNotification::create([
                'character_id' => $challenger->id,
                'type' => 'arm_wrestling',
                'icon' => 'fa-hand-back-fist',
                'title' => 'Armpjedruk Uitdaging Beslecht!',
                'message' => 'Jouw uitdaging van €'.number_format($match->bet_amount, 0, ',', '.')." is aangenomen door {$lockedOpponent->name}. Winnaar: {$winner->name}!",
            ]);

            if ($winner->id === $lockedOpponent->id) {
                return back()->with('success', "KNAL! Je hebt {$challenger->name} tegen de tafel gedrukt en wint de pot van €".number_format($pot, 0, ',', '.').'! (+50 XP, +15 Respect)');
            }

            return back()->with('error', "{$challenger->name} bleek net iets te sterk aan de tafel en pakt de pot van €".number_format($pot, 0, ',', '.').'. (-€'.number_format($match->bet_amount, 0, ',', '.').', wel +15 XP)');
        });
    }
}

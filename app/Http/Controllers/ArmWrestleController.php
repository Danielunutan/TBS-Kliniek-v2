<?php

namespace App\Http\Controllers;

use App\Models\ArmWrestleMatch;
use App\Models\Character;
use App\Models\CharacterArmWrestleStat;
use App\Models\GameNotification;
use App\Services\CooldownService;
use App\Services\EconomyService;
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
                'difficulty_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon' => 'fa-broom',
                'icon_style' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
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
                'difficulty_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'icon' => 'fa-dumbbell',
                'icon_style' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
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
                'difficulty_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'fa-hand-fist',
                'icon_style' => 'bg-amber-50 text-amber-600 border-amber-200',
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
                'difficulty_color' => 'bg-rose-50 text-rose-700 border-rose-200',
                'icon' => 'fa-skull',
                'icon_style' => 'bg-rose-50 text-rose-600 border-rose-200',
            ],
        ];
    }

    /**
     * Show the arm wrestling arena page.
     */
    public function index(): View
    {
        /** @var Character $character */
        $character = Auth::user()->character;

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
        /** @var Character $character */
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je niet armpjedrukken!',
            ], 422);
        }

        $remaining = $this->cooldownService->getRemainingSeconds($character, 'arm_wrestling');
        if ($remaining > 0) {
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

        if ($character->strength < $npc['min_strength']) {
            return response()->json([
                'success' => false,
                'message' => "Je hebt minimaal {$npc['min_strength']} Kracht nodig om tegen {$npc['name']} te mogen aantreden!",
            ], 422);
        }

        $bet = (int) $npc['bet'];

        return DB::transaction(function () use ($character, $npc, $validated, $bet) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            $totalAvailable = (int) ($lockedCharacter->cash + $lockedCharacter->bank);
            if ($totalAvailable < $bet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt onvoldoende saldo (contant + bank) op zak voor deze inzet (€'.number_format($bet, 0, ',', '.').')!',
                ], 422);
            }

            if ($lockedCharacter->energy < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt minimaal 5 Energie nodig om een armpjedrukduel aan te gaan!',
                ], 422);
            }

            // Deduct energy & inzet (pay from cash first, cover remaining from bank)
            $lockedCharacter->decrement('energy', 5);
            if ($lockedCharacter->cash >= $bet) {
                $lockedCharacter->decrement('cash', $bet);
            } else {
                $diff = $bet - $lockedCharacter->cash;
                $lockedCharacter->cash = 0;
                $lockedCharacter->save();
                $lockedCharacter->decrement('bank', $diff);
            }

            // Set cooldown immediately upon match resolution (20 seconds)
            $this->cooldownService->setCooldown($lockedCharacter, 'arm_wrestling', 20);

            $durationMs = max(200, (int) ($validated['duration_ms'] ?? ((int) ($validated['duration_seconds'] ?? 1) * 1000)));
            $durationSec = max(1, (int) round($durationMs / 1000));
            $clicks = max(0, (int) $validated['clicks']);

            // Anti-cheat: Cap click rate to human max (20 clicks per second + initial buffer)
            $maxPossibleClicks = (int) ceil(($durationMs / 1000) * 20) + 3;
            $effectiveClicks = min($clicks, $maxPossibleClicks);

            // Calculate contest power server-side (strength + stamina + click effort)
            $playerPower = ($lockedCharacter->strength * 1.5) + (($lockedCharacter->stamina ?? 10) * 0.8) + ($effectiveClicks * 2.0);
            $npcPower = ($npc['npc_strength'] * 1.3) + ($npc['npc_stamina'] * 0.8);

            // Fair win verification:
            // 1. Client pushed to victory (won == true)
            // 2. Player must have made at least 1 click or survived a 10s+ dominance contest
            // 3. Power sanity check: strong players can pin opponents in few clicks, while low-stat cheaters are rejected
            $clientWon = (bool) $validated['won'];
            $isWon = $clientWon
                && ($effectiveClicks >= 1 || $durationMs >= 10000)
                && ($playerPower >= ($npcPower * 0.35) || $lockedCharacter->strength >= ($npc['npc_strength'] * 0.5));

            /** @var CharacterArmWrestleStat $stat */
            $stat = CharacterArmWrestleStat::firstOrCreate(['character_id' => $lockedCharacter->id]);

            if ($isWon) {
                // Calculate rewards
                $profit = $npc['win_payout'] - $npc['bet'];
                $lockedCharacter->increment('cash', $npc['win_payout']);
                $lockedCharacter->increment('respect', $npc['respect']);
                $this->level->addXp($lockedCharacter, $npc['xp']);

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

                return response()->json([
                    'success' => true,
                    'won' => true,
                    'title' => 'KNAL! Tafelwinst!',
                    'message' => "Je hebt de arm van {$npc['name']} met brute kracht op het hout geslagen! +€".number_format($profit, 0, ',', '.')." winst, +{$npc['xp']} XP en +{$npc['respect']} Respect!",
                    'cash' => $lockedCharacter->fresh()->cash,
                    'energy' => $lockedCharacter->fresh()->energy,
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

            return response()->json([
                'success' => true,
                'won' => false,
                'title' => 'Verloren aan de Tafel...',
                'message' => $reason,
                'cash' => $lockedCharacter->fresh()->cash,
                'energy' => $lockedCharacter->fresh()->energy,
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
        /** @var Character $character */
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen uitdagingen plaatsen!');
        }

        // Limit to 1 open challenge at a time per character
        $existing = ArmWrestleMatch::where('challenger_id', $character->id)
            ->where('type', 'pvp')
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return back()->with('error', 'Je hebt al een open uitdaging op de tafel liggen! Annuleer deze eerst als je een ander bedrag wilt inzetten.');
        }

        $validated = $request->validate([
            'bet_amount' => ['required', 'integer', 'in:2500,5000,10000,25000,50000,100000'],
        ]);

        $bet = (int) $validated['bet_amount'];

        return DB::transaction(function () use ($character, $bet) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            $totalAvailable = (int) ($lockedCharacter->cash + $lockedCharacter->bank);
            if ($totalAvailable < $bet) {
                return back()->with('error', 'Je hebt onvoldoende geld (contant + bank) om deze uitdaging op tafel te leggen (€'.number_format($bet, 0, ',', '.').').');
            }

            if ($lockedCharacter->cash >= $bet) {
                $lockedCharacter->decrement('cash', $bet);
            } else {
                $diff = $bet - $lockedCharacter->cash;
                $lockedCharacter->cash = 0;
                $lockedCharacter->save();
                $lockedCharacter->decrement('bank', $diff);
            }

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
        /** @var Character $character */
        $character = Auth::user()->character;

        return DB::transaction(function () use ($character, $id) {
            /** @var ArmWrestleMatch|null $match */
            $match = ArmWrestleMatch::where('id', $id)
                ->where('challenger_id', $character->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (! $match) {
                return back()->with('error', 'Deze uitdaging bestaat niet meer of is al geaccepteerd.');
            }

            $match->status = 'cancelled';
            $match->save();

            $character->increment('cash', $match->bet_amount);

            return back()->with('success', 'Je uitdaging is geannuleerd en je inzet van €'.number_format($match->bet_amount, 0, ',', '.').' is teruggestort op zak.');
        });
    }

    /**
     * Accept another player's challenge.
     */
    public function acceptChallenge(Request $request, int $id): RedirectResponse
    {
        /** @var Character $opponent */
        $opponent = Auth::user()->character;

        if ($opponent->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen uitdagingen aannemen!');
        }

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

            /** @var Character $lockedOpponent */
            $lockedOpponent = Character::where('id', $opponent->id)->lockForUpdate()->first();
            $totalAvailable = (int) ($lockedOpponent->cash + $lockedOpponent->bank);

            if ($totalAvailable < $bet) {
                return back()->with('error', 'Je hebt onvoldoende geld (contant + bank) om deze uitdaging aan te gaan (€'.number_format($bet, 0, ',', '.').').');
            }

            /** @var Character $challenger */
            $challenger = Character::where('id', $match->challenger_id)->lockForUpdate()->first();
            if (! $challenger) {
                return back()->with('error', 'Uitdager niet gevonden.');
            }

            $match->status = 'completed';
            $match->save();

            $lockedOpponent->decrement('energy', 5);
            if ($lockedOpponent->cash >= $bet) {
                $lockedOpponent->decrement('cash', $bet);
            } else {
                $diff = $bet - $lockedOpponent->cash;
                $lockedOpponent->cash = 0;
                $lockedOpponent->save();
                $lockedOpponent->decrement('bank', $diff);
            }

            // Set cooldown for opponent
            $this->cooldownService->setCooldown($lockedOpponent, 'arm_wrestling', 20);

            // Calculate battle scores: Strength (60%) + Stamina (25%) + Discipline (15%) + RNG variance
            $challengerScore = ($challenger->strength * 1.5) + (($challenger->stamina ?? 10) * 0.5) + (($challenger->discipline ?? 10) * 0.4) + rand(1, 20);
            $opponentScore = ($lockedOpponent->strength * 1.5) + (($lockedOpponent->stamina ?? 10) * 0.5) + (($lockedOpponent->discipline ?? 10) * 0.4) + rand(1, 20);

            $winner = $opponentScore >= $challengerScore ? $lockedOpponent : $challenger;
            $loser = $winner->id === $lockedOpponent->id ? $challenger : $lockedOpponent;

            // Payout winner
            $winner->increment('cash', $pot);
            $winner->increment('respect', 15);
            $this->level->addXp($winner, 50);

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

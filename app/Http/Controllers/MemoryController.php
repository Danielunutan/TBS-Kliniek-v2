<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterMemoryStat;
use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemoryController extends Controller
{
    public const COOLDOWN_WIN_SECONDS = 900; // 15 minuten

    public const COOLDOWN_LOSS_SECONDS = 600; // 10 minuten

    public const DAILY_GAME_LIMIT = 30; // Maximaal 30 spellen per dag om bot-exploitatie te voorkomen

    public const CARD_CATALOG = [
        [
            'id' => 'smokkelkoffer',
            'name' => 'Smokkelkoffer',
            'category' => 'Buit',
            'icon' => 'fa-briefcase',
            'color' => 'purple',
            'bg_gradient' => 'from-purple-600 to-indigo-700',
            'bg_style' => 'linear-gradient(135deg, #9333ea 0%, #4338ca 100%)',
            'border_color' => 'border-purple-400',
            'border_hex' => '#c084fc',
            'badge_bg' => 'rgba(168, 85, 247, 0.15)',
            'badge_text' => '#9333ea',
            'glow_color' => 'rgba(168, 85, 247, 0.45)',
            'desc' => 'Gevuld met wit geld en kluiscodes',
        ],
        [
            'id' => 'medicijnspuit',
            'name' => 'Haloperidol Spuit',
            'category' => 'Medisch',
            'icon' => 'fa-syringe',
            'color' => 'emerald',
            'bg_gradient' => 'from-emerald-600 to-teal-700',
            'bg_style' => 'linear-gradient(135deg, #059669 0%, #0f766e 100%)',
            'border_color' => 'border-emerald-400',
            'border_hex' => '#34d399',
            'badge_bg' => 'rgba(16, 185, 129, 0.15)',
            'badge_text' => '#059669',
            'glow_color' => 'rgba(16, 185, 129, 0.45)',
            'desc' => 'Krachtige kliniek verdoving',
        ],
        [
            'id' => 'handboeien',
            'name' => 'Stalen Handboeien',
            'category' => 'Beveiliging',
            'icon' => 'fa-handcuffs',
            'color' => 'slate',
            'bg_gradient' => 'from-slate-700 to-slate-900',
            'bg_style' => 'linear-gradient(135deg, #334155 0%, #0f172a 100%)',
            'border_color' => 'border-slate-400',
            'border_hex' => '#64748b',
            'badge_bg' => 'rgba(100, 116, 139, 0.15)',
            'badge_text' => '#475569',
            'glow_color' => 'rgba(100, 116, 139, 0.45)',
            'desc' => 'Zware transportboeien',
        ],
        [
            'id' => 'bende_masker',
            'name' => 'Heist Masker',
            'category' => 'Bende',
            'icon' => 'fa-masks-theater',
            'color' => 'rose',
            'bg_gradient' => 'from-rose-600 to-pink-700',
            'bg_style' => 'linear-gradient(135deg, #e11d48 0%, #be185d 100%)',
            'border_color' => 'border-rose-400',
            'border_hex' => '#fb7185',
            'badge_bg' => 'rgba(244, 63, 94, 0.15)',
            'badge_text' => '#e11d48',
            'glow_color' => 'rgba(244, 63, 94, 0.45)',
            'desc' => 'Voor geheime overvallen',
        ],
        [
            'id' => 'dodelijke_pillen',
            'name' => 'Clandestiene Pillen',
            'category' => 'Verdovend',
            'icon' => 'fa-pills',
            'color' => 'amber',
            'bg_gradient' => 'from-amber-500 to-orange-600',
            'bg_style' => 'linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)',
            'border_color' => 'border-amber-400',
            'border_hex' => '#fbbf24',
            'badge_bg' => 'rgba(245, 158, 11, 0.15)',
            'badge_text' => '#d97706',
            'glow_color' => 'rgba(245, 158, 11, 0.45)',
            'desc' => 'Gesmokkelde capsules',
        ],
        [
            'id' => 'sleutelbos',
            'name' => 'Loper Sleutels',
            'category' => 'Ontsnapping',
            'icon' => 'fa-key',
            'color' => 'indigo',
            'bg_gradient' => 'from-indigo-600 to-blue-700',
            'bg_style' => 'linear-gradient(135deg, #4f46e5 0%, #2563eb 100%)',
            'border_color' => 'border-indigo-400',
            'border_hex' => '#818cf8',
            'badge_bg' => 'rgba(99, 102, 241, 0.15)',
            'badge_text' => '#4f46e5',
            'glow_color' => 'rgba(99, 102, 241, 0.5)',
            'desc' => 'Opent alle afdelingsdeuren',
        ],
        [
            'id' => 'steekwapen',
            'name' => 'Kliniek Shank',
            'category' => 'Wapen',
            'icon' => 'fa-screwdriver-wrench',
            'color' => 'red',
            'bg_gradient' => 'from-red-600 to-red-800',
            'bg_style' => 'linear-gradient(135deg, #dc2626 0%, #991b1b 100%)',
            'border_color' => 'border-red-400',
            'border_hex' => '#f87171',
            'badge_bg' => 'rgba(239, 68, 68, 0.15)',
            'badge_text' => '#dc2626',
            'glow_color' => 'rgba(239, 68, 68, 0.45)',
            'desc' => 'Geslepen tandwiel wapen',
        ],
        [
            'id' => 'kluis',
            'name' => 'Centrale Kluis',
            'category' => 'Jackpot',
            'icon' => 'fa-vault',
            'color' => 'yellow',
            'bg_gradient' => 'from-yellow-500 to-amber-600',
            'bg_style' => 'linear-gradient(135deg, #eab308 0%, #d97706 100%)',
            'border_color' => 'border-yellow-400',
            'border_hex' => '#facc15',
            'badge_bg' => 'rgba(234, 179, 8, 0.18)',
            'badge_text' => '#b45309',
            'glow_color' => 'rgba(234, 179, 8, 0.45)',
            'desc' => 'Kluisdeur met geheime code',
        ],
        [
            'id' => 'portofoon',
            'name' => 'Bewakers Walkie',
            'category' => 'Alarm',
            'icon' => 'fa-walkie-talkie',
            'color' => 'cyan',
            'bg_gradient' => 'from-cyan-600 to-teal-700',
            'bg_style' => 'linear-gradient(135deg, #06b6d4 0%, #0d9488 100%)',
            'border_color' => 'border-cyan-400',
            'border_hex' => '#22d3ee',
            'badge_bg' => 'rgba(6, 182, 212, 0.15)',
            'badge_text' => '#0891b2',
            'glow_color' => 'rgba(6, 182, 212, 0.5)',
            'desc' => 'Onderschept bewakersberichten',
        ],
        [
            'id' => 'traliecel',
            'name' => 'Isoleercel Deur',
            'category' => 'Straf',
            'icon' => 'fa-door-closed',
            'color' => 'orange',
            'bg_gradient' => 'from-orange-600 to-red-700',
            'bg_style' => 'linear-gradient(135deg, #ea580c 0%, #c2410c 100%)',
            'border_color' => 'border-orange-400',
            'border_hex' => '#fb923c',
            'badge_bg' => 'rgba(249, 115, 22, 0.15)',
            'badge_text' => '#c2410c',
            'glow_color' => 'rgba(249, 115, 22, 0.5)',
            'desc' => 'Gepantserde afzondering',
        ],
    ];

    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    /**
     * Show the Memory minigame arena.
     */
    public function index(): View
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        /** @var CharacterMemoryStat $stat */
        $stat = CharacterMemoryStat::firstOrCreate(
            ['character_id' => $character->id],
            [
                'games_played' => 0,
                'games_won' => 0,
                'total_cash_won' => 0,
                'best_time_seconds' => null,
                'best_moves' => null,
                'cooldown_until' => null,
            ]
        );

        $cooldownRemaining = $stat->getCooldownSecondsRemaining();
        $dailyGamesPlayed = $stat->getDailyGamesCount();
        $dailyGamesLimit = self::DAILY_GAME_LIMIT;

        // Generate 16 card deck (8 pairs)
        $deck = $this->generateDeck();

        return view('game.memory.index', compact(
            'character',
            'stat',
            'cooldownRemaining',
            'deck',
            'dailyGamesPlayed',
            'dailyGamesLimit'
        ));
    }

    /**
     * Generate fresh shuffled 8-pair deck (16 cards) for the 4x4 memory grid.
     */
    public function generateDeck(): array
    {
        $allCards = self::CARD_CATALOG;
        shuffle($allCards);
        $selectedCards = array_slice($allCards, 0, 8);

        $deck = [];
        $uniqueId = 1;

        foreach ($selectedCards as $card) {
            // First card of pair
            $deck[] = array_merge($card, [
                'card_uid' => $uniqueId++,
                'matched' => false,
                'flipped' => false, // starts face-down until game starts
            ]);
            // Second card of pair
            $deck[] = array_merge($card, [
                'card_uid' => $uniqueId++,
                'matched' => false,
                'flipped' => false, // starts face-down until game starts
            ]);
        }

        shuffle($deck);

        return $deck;
    }

    /** Start or resume the server-owned round. */
    public function start(): JsonResponse
    {
        $character = Auth::user()->character;

        return DB::transaction(function () use ($character): JsonResponse {
            $locked = Character::whereKey($character->id)->lockForUpdate()->firstOrFail();
            if ($locked->isImprisoned() || $locked->isComatose() || $locked->needsReviveSerum()) {
                return response()->json(['success' => false, 'message' => 'Je kunt nu geen Memory spelen.'], 422);
            }
            $stat = CharacterMemoryStat::firstOrCreate(['character_id' => $locked->id]);
            if ($stat->hasReachedDailyLimit(self::DAILY_GAME_LIMIT)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt vandaag je dagelijkse limiet van '.self::DAILY_GAME_LIMIT.' Memory sessies bereikt. Morgen om 00:00 uur kun je weer spelen!',
                    'daily_limit_reached' => true,
                ], 422);
            }
            if ($stat->isOnCooldown()) {
                return response()->json(['success' => false, 'message' => 'Wacht eerst de cooldown af.', 'cooldown' => $stat->getCooldownSecondsRemaining()], 422);
            }

            $game = $stat->active_game;
            if (! is_array($game)) {
                $game = [
                    'id' => (string) Str::uuid(),
                    'deck' => $this->generateDeck(),
                    'started_at' => now()->timestamp,
                    'matched' => [],
                    'first' => null,
                    'moves' => 0,
                    'lives' => 3,
                    'completed_at' => null,
                ];
                $stat->active_game = $game;
                $stat->save();
            }

            return response()->json(['success' => true, 'game' => $game, 'server_time' => now()->timestamp]);
        });
    }

    /** Record one card selection; the server owns the board and match state. */
    public function move(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_id' => ['required', 'uuid'],
            'card' => ['required', 'integer', 'between:0,15'],
        ]);
        $character = Auth::user()->character;

        return DB::transaction(function () use ($character, $validated): JsonResponse {
            $locked = Character::whereKey($character->id)->lockForUpdate()->firstOrFail();
            if ($locked->isImprisoned() || $locked->isComatose() || $locked->needsReviveSerum()) {
                return response()->json(['success' => false, 'message' => 'Je kunt nu geen Memory spelen.'], 422);
            }
            $stat = CharacterMemoryStat::where('character_id', $locked->id)->lockForUpdate()->first();
            $game = $stat?->active_game;
            if (! is_array($game) || $game['id'] !== $validated['game_id']) {
                return response()->json(['success' => false, 'message' => 'Deze ronde is niet actief.'], 422);
            }
            $now = now()->timestamp;
            if ($game['completed_at'] !== null || $now < $game['started_at'] + 5 || $now >= $game['started_at'] + 65) {
                return response()->json(['success' => false, 'message' => 'De ronde is nog niet begonnen of is afgelopen.'], 422);
            }
            $card = (int) $validated['card'];
            if (in_array($card, $game['matched'], true) || $game['first'] === $card) {
                return response()->json(['success' => false, 'message' => 'Deze kaart is al gekozen.'], 422);
            }

            $match = null;
            if ($game['first'] === null) {
                $game['first'] = $card;
            } else {
                $first = $game['first'];
                $match = $game['deck'][$first]['id'] === $game['deck'][$card]['id'];
                $game['moves']++;
                if ($match) {
                    $game['matched'][] = $first;
                    $game['matched'][] = $card;
                } else {
                    $game['lives']--;
                }
                $game['first'] = null;
                if (count($game['matched']) === 16 || $game['lives'] === 0) {
                    $game['completed_at'] = $now;
                }
            }
            $stat->active_game = $game;
            $stat->save();

            return response()->json(['success' => true, 'match' => $match, 'game' => $game, 'server_time' => $now]);
        });
    }

    /** Settle a completed or timed-out round exactly once. */
    public function finish(Request $request): JsonResponse
    {
        $validated = $request->validate(['game_id' => ['required', 'uuid']]);
        $character = Auth::user()->character;

        return DB::transaction(function () use ($character, $validated): JsonResponse {
            $lockedChar = Character::whereKey($character->id)->lockForUpdate()->firstOrFail();
            if ($lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return response()->json(['success' => false, 'message' => 'Je kunt nu geen Memory spelen.'], 422);
            }
            $stat = CharacterMemoryStat::where('character_id', $lockedChar->id)->lockForUpdate()->first();
            $game = $stat?->active_game;
            if (! is_array($game) || $game['id'] !== $validated['game_id']) {
                return response()->json(['success' => false, 'message' => 'Deze ronde is niet actief.'], 422);
            }
            if ($game['completed_at'] === null && now()->timestamp < $game['started_at'] + 65) {
                return response()->json(['success' => false, 'message' => 'Deze ronde is nog niet afgelopen.'], 422);
            }
            // Anti-exploit: realistic minimum reaction time and move count verification.
            // Matching 8 distinct pairs across 16 cards requires at least 8 moves and at least 3 seconds.
            $timeTaken = max(1, min(60, ($game['completed_at'] ?? now()->timestamp) - $game['started_at'] - 5));
            $livesLeft = $game['lives'];
            $moves = $game['moves'];
            $won = count($game['matched']) === 16 && $game['lives'] > 0 && $moves >= 8 && $timeTaken >= 3;
            $stat->active_game = null;
            $stat->incrementDailyGames();

            $rewardCash = 0;
            $rewardXp = 0;
            $rewardRespect = 0;
            $droppedBag = false;

            $stat->games_played++;

            if ($won && $livesLeft > 0) {
                $stat->games_won++;
                $rewardCash = 35000;
                $rewardXp = 60;
                $rewardRespect = 15;

                // 25% chance of dropping a Smokkelkoffer
                if (random_int(1, 100) <= 25) {
                    $lockedChar->increment('unopened_bags', 1);
                    $droppedBag = true;
                }

                // Best score tracking
                if ($stat->best_time_seconds === null || $timeTaken < $stat->best_time_seconds) {
                    $stat->best_time_seconds = $timeTaken;
                }
                if ($stat->best_moves === null || $moves < $stat->best_moves) {
                    $stat->best_moves = $moves;
                }

                $stat->cooldown_until = now()->addSeconds(self::COOLDOWN_WIN_SECONDS);
            } else {
                // Loss / timeout / 0 lives
                $rewardCash = 3000;
                $rewardXp = 15;
                $stat->cooldown_until = now()->addSeconds(self::COOLDOWN_LOSS_SECONDS);
            }

            if ($rewardCash > 0) {
                $lockedChar->increment('cash', $rewardCash);
                $stat->total_cash_won += $rewardCash;
            }

            if ($rewardRespect > 0) {
                $lockedChar->addRespect($rewardRespect);
                $lockedChar->save();
            }

            if ($rewardXp > 0) {
                $this->level->addXp($lockedChar, $rewardXp);
            }

            $stat->save();

            $freshChar = $lockedChar->fresh();

            return response()->json([
                'success' => true,
                'won' => $won && $livesLeft > 0,
                'reward_cash' => $rewardCash,
                'reward_xp' => $rewardXp,
                'reward_respect' => $rewardRespect,
                'dropped_bag' => $droppedBag,
                'time_taken' => $timeTaken,
                'lives_left' => $livesLeft,
                'moves' => $moves,
                'cash' => $freshChar->cash,
                'unopened_bags' => $freshChar->unopened_bags,
                'cooldown' => $stat->getCooldownSecondsRemaining(),
                'games_played' => $stat->games_played,
                'games_won' => $stat->games_won,
                'daily_games_played' => $stat->getDailyGamesCount(),
                'daily_games_limit' => self::DAILY_GAME_LIMIT,
            ]);
        });
    }

    /**
     * Skip cooldown using Stars (10 Stars).
     */
    public function skipCooldownWithStars(): RedirectResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        return DB::transaction(function () use ($character): RedirectResponse {
            /** @var Character $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var CharacterMemoryStat $stat */
            $stat = CharacterMemoryStat::where('character_id', $lockedChar->id)
                ->lockForUpdate()
                ->firstOrCreate(['character_id' => $lockedChar->id]);

            if (! $stat->isOnCooldown()) {
                return redirect()->route('memory.index')
                    ->with('info', 'Er is momenteel geen actieve wachttijd om over te slaan.');
            }

            if ($stat->hasReachedDailyLimit(self::DAILY_GAME_LIMIT)) {
                return back()->with('error', 'Je hebt vandaag je dagelijkse limiet van '.self::DAILY_GAME_LIMIT.' Memory sessies bereikt. Morgen kun je weer spelen!');
            }

            if (($lockedChar->stars ?? 0) < 10) {
                return back()->with('error', 'Je hebt minimaal 10 Sterren nodig om de wachttijd over te slaan!');
            }

            $lockedChar->stars = ($lockedChar->stars ?? 0) - 10;
            $lockedChar->save();

            $stat->update([
                'cooldown_until' => null,
            ]);

            return redirect()->route('memory.index')
                ->with('success', '⭐ Wachttijd overgeslagen voor 10 Sterren! Je kunt direct weer een nieuwe Memory sessie starten!');
        });
    }

    /**
     * Skip cooldown via Sponsor Ad watch.
     */
    public function skipCooldownWithAd(Request $request): RedirectResponse
    {
        if (! SponsorAdController::areAdsEnabled()) {
            return redirect()->route('memory.index')
                ->with('error', 'Sponsor-advertenties zijn momenteel uitgeschakeld. Gebruik Sterren ⭐ om de wachttijd over te slaan.');
        }

        /** @var Character|null $character */
        $character = Auth::user()?->character;
        if (! $character) {
            return redirect()->route('onboarding.index');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        /** @var CharacterMemoryStat $stat */
        $stat = CharacterMemoryStat::firstOrCreate(['character_id' => $character->id]);

        if (! $stat->isOnCooldown()) {
            return redirect()->route('memory.index')
                ->with('info', 'Er is momenteel geen actieve wachttijd om over te slaan.');
        }

        if ($stat->hasReachedDailyLimit(self::DAILY_GAME_LIMIT)) {
            return redirect()->route('memory.index')
                ->with('error', 'Je hebt vandaag je dagelijkse limiet van '.self::DAILY_GAME_LIMIT.' Memory sessies bereikt. Morgen kun je weer spelen!');
        }

        $request->merge(['reward_type' => 'cooldown_memory']);
        $claimResponse = app(SponsorAdController::class)->claim($request);
        $data = $claimResponse->getData(true);

        if ($claimResponse->getStatusCode() !== 200 || empty($data['success']) || ($data['reward_type'] ?? null) !== 'cooldown_memory') {
            return redirect()->route('memory.index')
                ->with('error', $data['message'] ?? 'Bekijk eerst de volledige sponsor-advertentie om de wachttijd te resetten.');
        }

        return redirect()->route('memory.index')
            ->with('success', $data['reward_details'] ?? '⚡ Dankzij de sponsor-ad is je geheugen weer 100% gefocust en kun je direct weer een ronde spelen!');
    }
}

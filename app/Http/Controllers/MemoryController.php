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
use Illuminate\View\View;

class MemoryController extends Controller
{
    public const COOLDOWN_WIN_SECONDS = 900; // 15 minuten

    public const COOLDOWN_LOSS_SECONDS = 600; // 10 minuten

    public const CARD_CATALOG = [
        [
            'id' => 'smokkelkoffer',
            'name' => 'Smokkelkoffer',
            'category' => 'Buit',
            'icon' => 'fa-briefcase',
            'color' => 'purple',
            'bg_gradient' => 'from-purple-600 to-indigo-700',
            'border_color' => 'border-purple-400',
            'glow_color' => 'rgba(168, 85, 247, 0.4)',
            'desc' => 'Gevuld met wit geld en kluiscodes',
        ],
        [
            'id' => 'medicijnspuit',
            'name' => 'Haloperidol Spuit',
            'category' => 'Medisch',
            'icon' => 'fa-syringe',
            'color' => 'emerald',
            'bg_gradient' => 'from-emerald-600 to-teal-700',
            'border_color' => 'border-emerald-400',
            'glow_color' => 'rgba(16, 185, 129, 0.4)',
            'desc' => 'Krachtige kliniek verdoving',
        ],
        [
            'id' => 'handboeien',
            'name' => 'Stalen Handboeien',
            'category' => 'Beveiliging',
            'icon' => 'fa-handcuffs',
            'color' => 'slate',
            'bg_gradient' => 'from-slate-700 to-slate-900',
            'border_color' => 'border-slate-400',
            'glow_color' => 'rgba(148, 163, 184, 0.4)',
            'desc' => 'Zware transportboeien',
        ],
        [
            'id' => 'bende_masker',
            'name' => 'Heist Masker',
            'category' => 'Bende',
            'icon' => 'fa-masks-theater',
            'color' => 'rose',
            'bg_gradient' => 'from-rose-600 to-pink-700',
            'border_color' => 'border-rose-400',
            'glow_color' => 'rgba(244, 63, 94, 0.4)',
            'desc' => 'Voor geheime overvallen',
        ],
        [
            'id' => 'dodelijke_pillen',
            'name' => 'Clandestiene Pillen',
            'category' => 'Verdovend',
            'icon' => 'fa-pills',
            'color' => 'amber',
            'bg_gradient' => 'from-amber-500 to-orange-600',
            'border_color' => 'border-amber-400',
            'glow_color' => 'rgba(245, 158, 11, 0.4)',
            'desc' => 'Gesmokkelde capsules',
        ],
        [
            'id' => 'sleutelbos',
            'name' => 'Loper Sleutels',
            'category' => 'Ontsnapping',
            'icon' => 'fa-key',
            'color' => 'indigo',
            'bg_gradient' => 'from-indigo-600 to-blue-700',
            'border_color' => 'border-indigo-400',
            'glow_color' => 'rgba(99, 102, 241, 0.4)',
            'desc' => 'Opent alle afdelingsdeuren',
        ],
        [
            'id' => 'steekwapen',
            'name' => 'Kliniek Shank',
            'category' => 'Wapen',
            'icon' => 'fa-screwdriver-wrench',
            'color' => 'red',
            'bg_gradient' => 'from-red-600 to-red-800',
            'border_color' => 'border-red-400',
            'glow_color' => 'rgba(239, 68, 68, 0.4)',
            'desc' => 'Geslepen tandwiel wapen',
        ],
        [
            'id' => 'kluis',
            'name' => 'Centrale Kluis',
            'category' => 'Jackpot',
            'icon' => 'fa-vault',
            'color' => 'yellow',
            'bg_gradient' => 'from-yellow-500 to-amber-600',
            'border_color' => 'border-yellow-400',
            'glow_color' => 'rgba(234, 179, 8, 0.4)',
            'desc' => 'Kluisdeur met geheime code',
        ],
        [
            'id' => 'portofoon',
            'name' => 'Bewakers Walkie',
            'category' => 'Alarm',
            'icon' => 'fa-walkie-talkie',
            'color' => 'cyan',
            'bg_gradient' => 'from-cyan-600 to-teal-700',
            'border_color' => 'border-cyan-400',
            'glow_color' => 'rgba(6, 182, 212, 0.4)',
            'desc' => 'Onderschept bewakersberichten',
        ],
        [
            'id' => 'traliecel',
            'name' => 'Isoleercel Deur',
            'category' => 'Straf',
            'icon' => 'fa-dungeon',
            'color' => 'orange',
            'bg_gradient' => 'from-orange-600 to-red-700',
            'border_color' => 'border-orange-400',
            'glow_color' => 'rgba(249, 115, 22, 0.4)',
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

        // Generate 16 card deck (8 pairs)
        $deck = $this->generateDeck();

        return view('game.memory.index', compact(
            'character',
            'stat',
            'cooldownRemaining',
            'deck'
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
                'flipped' => true, // starts true for 5s inkijk time
            ]);
            // Second card of pair
            $deck[] = array_merge($card, [
                'card_uid' => $uniqueId++,
                'matched' => false,
                'flipped' => true, // starts true for 5s inkijk time
            ]);
        }

        shuffle($deck);

        return $deck;
    }

    /**
     * Handle game completion (win or loss) and distribute rewards.
     */
    public function finish(Request $request): JsonResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        /** @var CharacterMemoryStat $stat */
        $stat = CharacterMemoryStat::firstOrCreate(['character_id' => $character->id]);

        if ($stat->isOnCooldown()) {
            return response()->json([
                'success' => false,
                'message' => 'Je brein moet nog even bijkomen. Wacht de cooldown af of bekijk een ad!',
                'cooldown' => $stat->getCooldownSecondsRemaining(),
            ], 422);
        }

        $validated = $request->validate([
            'won' => ['required', 'boolean'],
            'time_taken' => ['required', 'integer', 'min:1', 'max:65'],
            'lives_left' => ['required', 'integer', 'min:0', 'max:3'],
            'moves' => ['required', 'integer', 'min:1'],
        ]);

        $won = (bool) $validated['won'];
        $timeTaken = (int) $validated['time_taken'];
        $livesLeft = (int) $validated['lives_left'];
        $moves = (int) $validated['moves'];

        return DB::transaction(function () use ($character, $stat, $won, $timeTaken, $livesLeft, $moves) {
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();

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
                $lockedChar->increment('respect', $rewardRespect);
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
            ]);
        });
    }

    /**
     * Skip cooldown via Sponsor Ad watch.
     */
    public function skipCooldownWithAd(Request $request): RedirectResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        /** @var CharacterMemoryStat $stat */
        $stat = CharacterMemoryStat::firstOrCreate(['character_id' => $character->id]);

        $stat->update([
            'cooldown_until' => null,
        ]);

        return redirect()->route('memory.index')
            ->with('success', '⚡ Dankzij de sponsor-ad is je geheugen weer 100% gefocust en kun je direct weer een ronde spelen!');
    }
}

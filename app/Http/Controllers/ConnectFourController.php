<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConnectFourController extends Controller
{
    public const TOURNAMENT_MATCHES = 5;

    public const COOLDOWN_SECONDS = 600; // 10 minuten

    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    /**
     * Display the 4 op 'n Rij arena against OOHPA Arjan.
     */
    public function index(): View
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        /** @var CharacterConnectFourStat $stat */
        $stat = CharacterConnectFourStat::firstOrCreate(
            ['character_id' => $character->id],
            [
                'matches_played' => 0,
                'current_player_wins' => 0,
                'current_arjan_wins' => 0,
                'current_draws' => 0,
                'total_tournaments_played' => 0,
                'total_tournaments_won' => 0,
                'total_games_won' => 0,
                'total_cash_won' => 0,
                'cooldown_until' => null,
            ]
        );

        $cooldownRemaining = $stat->getCooldownSecondsRemaining();

        $quotes = [
            'intro' => 'Gezellig dat je aanschuift! Ik speel al jaren 4 op \'n rij in de recreatieruimte. Vijf potjes om te bepalen wie de echte meester is. Zet \'m op!',
            'win' => 'Hoppa! Vier op \'n rij voor OOHPA! Je moet scherper opletten op de diagonalen, makker!',
            'lose' => 'Potdikkie, die zag ik even niet aankomen! Netjes gespeeld, jij pakt dit potje!',
            'draw' => 'Een gelijkspel aan het bord! We zaten elkaar aardig in de weg, haha!',
            'tournament_win' => 'Gefeliciteerd man! Jij bent de terechte 4 op \'n rij kampioen van de recreatiezaal. Pak die dikke toernooibonus!',
            'tournament_loss' => 'Goed gestreden, maar OOHPA houdt de toernooiwinst binnenboord! Rust even uit of bekijk een ad om direct revanche te nemen!',
        ];

        return view('game.connectfour.index', compact(
            'character',
            'stat',
            'cooldownRemaining',
            'quotes'
        ));
    }

    /**
     * Submit outcome of 1 match in the tournament against OOHPA Arjan.
     */
    public function finishGame(Request $request): JsonResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je geen spelletjes spelen met OOHPA Arjan!',
            ], 422);
        }

        $validated = $request->validate([
            'winner' => ['required', 'string', 'in:player,arjan,draw'],
            'moves' => ['required', 'integer', 'min:4'],
        ]);

        return DB::transaction(function () use ($character, $validated) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            /** @var CharacterConnectFourStat $stat */
            $stat = CharacterConnectFourStat::where('character_id', $lockedCharacter->id)
                ->lockForUpdate()
                ->firstOrCreate(['character_id' => $lockedCharacter->id]);

            if ($stat->isOnCooldown()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OOHPA Arjan drinkt even een kop koffie! Wacht op de cooldown of kijk een sponsor-ad om direct door te gaan.',
                ], 422);
            }

            $winner = $validated['winner'];
            $rewardCash = 0;
            $rewardXp = 0;
            $rewardRespect = 0;

            if ($winner === 'player') {
                $stat->current_player_wins++;
                $stat->total_games_won++;
                $rewardCash += 5000;
                $rewardXp += 10;
                $rewardRespect += 3;
            } elseif ($winner === 'arjan') {
                $stat->current_arjan_wins++;
                $rewardXp += 5; // troost-XP
            } else {
                $stat->current_draws++;
                $rewardCash += 2000;
                $rewardXp += 8;
                $rewardRespect += 1;
            }

            $stat->matches_played++;

            // Process per-game rewards
            if ($rewardCash > 0) {
                $lockedCharacter->increment('cash', $rewardCash);
                $stat->total_cash_won += $rewardCash;
            }
            if ($rewardRespect > 0) {
                $lockedCharacter->increment('respect', $rewardRespect);
            }
            if ($rewardXp > 0) {
                $this->level->addXp($lockedCharacter, $rewardXp);
            }

            $tournamentFinished = false;
            $tournamentSummary = null;
            $bonusCash = 0;
            $bonusXp = 0;
            $bonusRespect = 0;
            $bonusStars = 0;

            // Check if tournament of 5 games is complete
            if ($stat->matches_played >= self::TOURNAMENT_MATCHES) {
                $tournamentFinished = true;
                $stat->total_tournaments_played++;

                $playerWins = $stat->current_player_wins;
                $arjanWins = $stat->current_arjan_wins;

                if ($playerWins >= 3) {
                    $stat->total_tournaments_won++;

                    if ($playerWins === 5) {
                        // Grand Slam 5-0
                        $bonusCash = 100000;
                        $bonusXp = 150;
                        $bonusRespect = 50;
                        $bonusStars = 10;
                        $summaryTitle = 'GRAND SLAM OVERWINNING! (5 - 0)';
                        $summaryMsg = 'Ongelofelijk! Je hebt OOHPA Arjan met 5-0 van het bord geveegd! Hij overhandigt jou de ultieme toernooibonus van €100.000, +150 XP, +50 Respect en 10 Sterren ⭐!';
                    } elseif ($playerWins === 4) {
                        $bonusCash = 50000;
                        $bonusXp = 100;
                        $bonusRespect = 30;
                        $summaryTitle = 'TOERNOOIWINNAAR! (4 - 1)';
                        $summaryMsg = 'Geweldig gespeeld! Met een 4-1 overwinning win je het toernooi van OOHPA Arjan. Bonus: €50.000, +100 XP en +30 Respect!';
                    } else {
                        // 3-2
                        $bonusCash = 25000;
                        $bonusXp = 60;
                        $bonusRespect = 15;
                        $summaryTitle = 'SERIE OVERWINNING! (3 - 2)';
                        $summaryMsg = 'Een spannende 3-2 strijd! Jij trekt aan het langste eind. Bonus: €25.000, +60 XP en +15 Respect!';
                    }
                } else {
                    $bonusCash = 5000;
                    $bonusXp = 25;
                    $summaryTitle = 'OOHPA ARJAN PAKT DE SERIE!';
                    $summaryMsg = 'Arjan wint deze 5-potjes serie met '.$arjanWins.' tegen '.$playerWins.'! Troostprijs: €5.000 en +25 XP.';
                }

                if ($bonusCash > 0) {
                    $lockedCharacter->increment('cash', $bonusCash);
                    $stat->total_cash_won += $bonusCash;
                }
                if ($bonusRespect > 0) {
                    $lockedCharacter->increment('respect', $bonusRespect);
                }
                if ($bonusXp > 0) {
                    $this->level->addXp($lockedCharacter, $bonusXp);
                }
                if ($bonusStars > 0) {
                    $lockedCharacter->increment('stars', $bonusStars);
                }

                $tournamentSummary = [
                    'won' => $playerWins >= 3,
                    'title' => $summaryTitle,
                    'message' => $summaryMsg,
                    'player_wins' => $playerWins,
                    'arjan_wins' => $arjanWins,
                    'draws' => $stat->current_draws,
                    'bonus_cash' => $bonusCash,
                    'bonus_xp' => $bonusXp,
                    'bonus_respect' => $bonusRespect,
                    'bonus_stars' => $bonusStars,
                ];

                // Set cooldown
                $stat->cooldown_until = now()->addSeconds(self::COOLDOWN_SECONDS);
                // Reset tournament current round
                $stat->matches_played = 0;
                $stat->current_player_wins = 0;
                $stat->current_arjan_wins = 0;
                $stat->current_draws = 0;
            }

            $stat->save();

            return response()->json([
                'success' => true,
                'winner' => $winner,
                'reward_cash' => $rewardCash,
                'reward_xp' => $rewardXp,
                'reward_respect' => $rewardRespect,
                'matches_played' => $stat->matches_played,
                'current_player_wins' => $stat->current_player_wins,
                'current_arjan_wins' => $stat->current_arjan_wins,
                'current_draws' => $stat->current_draws,
                'tournament_finished' => $tournamentFinished,
                'tournament_summary' => $tournamentSummary,
                'cooldown' => $stat->getCooldownSecondsRemaining(),
                'cash' => $lockedCharacter->fresh()->cash,
            ]);
        });
    }

    /**
     * Skip tournament cooldown via Sponsor Ad watch.
     */
    public function skipCooldownWithAd(Request $request): RedirectResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        /** @var CharacterConnectFourStat $stat */
        $stat = CharacterConnectFourStat::firstOrCreate(['character_id' => $character->id]);

        $stat->update([
            'cooldown_until' => null,
            'matches_played' => 0,
            'current_player_wins' => 0,
            'current_arjan_wins' => 0,
            'current_draws' => 0,
        ]);

        return redirect()->route('connect_four.index')->with('success', 'Dankzij de sponsor-advertentie heeft OOHPA Arjan direct zijn koffie op en staat hij te popelen voor een nieuw 5-potjes toernooi!');
    }
}

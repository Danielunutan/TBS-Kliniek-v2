<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Services\EconomyService;
use App\Services\LevelService;
use App\Support\ConnectFourEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConnectFourController extends Controller
{
    public const TOURNAMENT_MATCHES = 5;

    public const COOLDOWN_SECONDS = 600; // 10 minuten

    /**
     * How long an unfinished board stays on the server.
     */
    private const MATCH_TTL_HOURS = 2;

    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    /**
     * Display the 4 op 'n Rij arena against OOHPA Arjan.
     */
    public function index(): View
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;
        if (! $character) {
            abort(404, 'Karakter niet gevonden.');
        }

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
     * Cache key for the unfinished board of one character.
     */
    public static function matchCacheKey(int $characterId): string
    {
        return "connect_four_match:{$characterId}";
    }

    /**
     * Start a new match against OOHPA Arjan. The server creates the board and decides who opens.
     */
    public function startMatch(): JsonResponse
    {
        $characterOrResponse = $this->playableCharacter();
        if ($characterOrResponse instanceof JsonResponse) {
            return $characterOrResponse;
        }

        return DB::transaction(function () use ($characterOrResponse): JsonResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $characterOrResponse->id)->lockForUpdate()->first();
            $stat = $this->lockedStat($lockedCharacter);

            if ($stat->isOnCooldown()) {
                return $this->cooldownResponse();
            }

            $existingMatch = Cache::get(self::matchCacheKey($lockedCharacter->id));
            if (is_array($existingMatch) && isset($existingMatch['board']) && is_array($existingMatch['board'])) {
                $hasTokens = false;
                foreach ($existingMatch['board'] as $row) {
                    if (is_array($row) && (in_array(ConnectFourEngine::PLAYER, $row, true) || in_array(ConnectFourEngine::ARJAN, $row, true))) {
                        $hasTokens = true;
                        break;
                    }
                }
                if ($hasTokens) {
                    return response()->json([
                        'success' => true,
                        'resumed' => true,
                        'starter' => $existingMatch['starter'] ?? 'player',
                        'board' => $existingMatch['board'],
                        'ai' => null,
                        'matches_played' => $stat->matches_played,
                        'current_player_wins' => $stat->current_player_wins,
                        'current_arjan_wins' => $stat->current_arjan_wins,
                        'current_draws' => $stat->current_draws,
                    ]);
                }
            }

            $board = ConnectFourEngine::emptyBoard();
            $starter = $this->takeStarter($lockedCharacter->id);
            $arjanTurn = $starter === ConnectFourEngine::ARJAN ? $this->playArjanTurn($board) : null;

            Cache::put(self::matchCacheKey($lockedCharacter->id), ['board' => $board, 'starter' => $starter], now()->addHours(self::MATCH_TTL_HOURS));

            return response()->json([
                'success' => true,
                'starter' => $starter === ConnectFourEngine::PLAYER ? 'player' : 'arjan',
                'board' => $board,
                'ai' => $arjanTurn,
                'matches_played' => $stat->matches_played,
                'current_player_wins' => $stat->current_player_wins,
                'current_arjan_wins' => $stat->current_arjan_wins,
                'current_draws' => $stat->current_draws,
            ]);
        });
    }

    /**
     * Play one column for the player. The server replies with Arjan's move and settles the match when it ends.
     */
    public function playMove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'column' => ['required', 'integer', 'min:0', 'max:'.(ConnectFourEngine::COLUMNS - 1)],
        ]);

        $characterOrResponse = $this->playableCharacter();
        if ($characterOrResponse instanceof JsonResponse) {
            return $characterOrResponse;
        }

        return DB::transaction(function () use ($characterOrResponse, $validated): JsonResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $characterOrResponse->id)->lockForUpdate()->first();
            $stat = $this->lockedStat($lockedCharacter);

            if ($stat->isOnCooldown()) {
                return $this->cooldownResponse();
            }

            $state = Cache::get(self::matchCacheKey($lockedCharacter->id));
            if (! is_array($state) || ! isset($state['board']) || ! is_array($state['board']) || count($state['board']) !== ConnectFourEngine::ROWS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Er is geen actief potje. Start eerst een nieuw potje.',
                ], 422);
            }

            $board = $state['board'];
            $column = (int) $validated['column'];
            $playerRow = ConnectFourEngine::dropToken($board, $column, ConnectFourEngine::PLAYER);
            if ($playerRow === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deze kolom is vol. Kies een andere kolom.',
                ], 422);
            }

            $turn = [
                'player' => ['col' => $column, 'row' => $playerRow],
                'ai' => null,
                'win_coords' => ConnectFourEngine::winningCells($board, $playerRow, $column, ConnectFourEngine::PLAYER),
            ];

            if ($turn['win_coords'] !== null) {
                return $this->settleMatch($lockedCharacter, $stat, 'player', $board, $turn);
            }

            if (ConnectFourEngine::isFull($board)) {
                return $this->settleMatch($lockedCharacter, $stat, 'draw', $board, $turn);
            }

            $turn['ai'] = $this->playArjanTurn($board);
            $arjanCells = ConnectFourEngine::winningCells($board, $turn['ai']['row'], $turn['ai']['col'], ConnectFourEngine::ARJAN);
            if ($arjanCells !== null) {
                $turn['win_coords'] = $arjanCells;

                return $this->settleMatch($lockedCharacter, $stat, 'arjan', $board, $turn);
            }

            if (ConnectFourEngine::isFull($board)) {
                return $this->settleMatch($lockedCharacter, $stat, 'draw', $board, $turn);
            }

            Cache::put(self::matchCacheKey($lockedCharacter->id), ['board' => $board], now()->addHours(self::MATCH_TTL_HOURS));

            return response()->json([
                'success' => true,
                'status' => 'ongoing',
                'board' => $board,
                'player' => $turn['player'],
                'ai' => $turn['ai'],
                'win_coords' => null,
            ]);
        });
    }

    /**
     * Return the character when it may play, or a rejection response when it may not.
     */
    private function playableCharacter(): Character|JsonResponse
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
                'message' => 'Vanuit de isoleercel kun je geen spelletjes spelen met OOHPA Arjan!',
            ], 422);
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return response()->json([
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
            ], 422);
        }

        return $character;
    }

    private function lockedStat(Character $lockedCharacter): CharacterConnectFourStat
    {
        /** @var CharacterConnectFourStat $stat */
        $stat = CharacterConnectFourStat::where('character_id', $lockedCharacter->id)
            ->lockForUpdate()
            ->firstOrCreate(['character_id' => $lockedCharacter->id]);

        return $stat;
    }

    private function cooldownResponse(): JsonResponse
    {
        $adHint = SponsorAdController::areAdsEnabled() ? ' of kijk een sponsor-ad' : '';

        return response()->json([
            'success' => false,
            'message' => "OOHPA Arjan drinkt even een kop koffie! Wacht op de cooldown{$adHint} om direct door te gaan.",
        ], 422);
    }

    /**
     * Return who opens this match, then store the other player as the opener of the next match.
     */
    private function takeStarter(int $characterId): int
    {
        $cacheKey = "connect_four_starter:{$characterId}";
        $starter = (int) Cache::get($cacheKey, ConnectFourEngine::PLAYER);
        $nextStarter = $starter === ConnectFourEngine::PLAYER ? ConnectFourEngine::ARJAN : ConnectFourEngine::PLAYER;
        Cache::put($cacheKey, $nextStarter, now()->addDays(7));

        return $starter;
    }

    /**
     * Play Arjan's move on the board and return the column and row.
     *
     * @param  array<int, array<int, int>>  $board
     * @return array{col: int, row: int|null}
     */
    private function playArjanTurn(array &$board): array
    {
        $column = ConnectFourEngine::chooseArjanMove($board);

        return [
            'col' => $column,
            'row' => ConnectFourEngine::dropToken($board, $column, ConnectFourEngine::ARJAN),
        ];
    }

    /**
     * Settle a finished match, pay the rewards, and remove the board.
     *
     * @param  array<int, array<int, int>>  $board
     * @param  array{player: array{col: int, row: int}, ai: array{col: int, row: int|null}|null, win_coords: array<int, array{0: int, 1: int}>|null}  $turn
     */
    private function settleMatch(Character $lockedCharacter, CharacterConnectFourStat $stat, string $winner, array $board, array $turn): JsonResponse
    {
        Cache::forget(self::matchCacheKey($lockedCharacter->id));

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
            $lockedCharacter->addRespect($rewardRespect);
            $lockedCharacter->save();
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
                    $summaryTitle = 'TOERNOOIWINNAAR! ('.$playerWins.' - '.$arjanWins.')';
                    $summaryMsg = 'Geweldig gespeeld! Met een '.$playerWins.'-'.$arjanWins.' overwinning win je het toernooi van OOHPA Arjan. Bonus: €50.000, +100 XP en +30 Respect!';
                } else {
                    // 3-2
                    $bonusCash = 25000;
                    $bonusXp = 60;
                    $bonusRespect = 15;
                    $summaryTitle = 'SERIE OVERWINNING! ('.$playerWins.' - '.$arjanWins.')';
                    $summaryMsg = 'Een spannende '.$playerWins.'-'.$arjanWins.' strijd! Jij trekt aan het langste eind. Bonus: €25.000, +60 XP en +15 Respect!';
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
                $lockedCharacter->addRespect($bonusRespect);
                $lockedCharacter->save();
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
            'status' => $winner,
            'winner' => $winner,
            'board' => $board,
            'player' => $turn['player'],
            'ai' => $turn['ai'],
            'win_coords' => $turn['win_coords'],
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
    }

    /**
     * Skip tournament cooldown using Stars (10 Stars).
     */
    public function skipCooldownWithStars(): RedirectResponse
    {
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

        return DB::transaction(function () use ($character): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var CharacterConnectFourStat $stat */
            $stat = CharacterConnectFourStat::where('character_id', $lockedCharacter->id)
                ->lockForUpdate()
                ->firstOrCreate(['character_id' => $lockedCharacter->id]);

            if (! $stat->isOnCooldown()) {
                return redirect()->route('connect_four.index')
                    ->with('info', 'Er is momenteel geen actieve wachttijd om over te slaan.');
            }

            if (($lockedCharacter->stars ?? 0) < 10) {
                return back()->with('error', 'Je hebt minimaal 10 Sterren nodig om de wachttijd over te slaan!');
            }

            $lockedCharacter->stars = ($lockedCharacter->stars ?? 0) - 10;
            $lockedCharacter->save();

            $stat->update([
                'cooldown_until' => null,
                'matches_played' => 0,
                'current_player_wins' => 0,
                'current_arjan_wins' => 0,
                'current_draws' => 0,
            ]);

            return redirect()->route('connect_four.index')->with('success', '⭐ Wachttijd overgeslagen voor 10 Sterren! OOHPA Arjan staat direct klaar voor een nieuw 5-potjes toernooi!');
        });
    }

    /**
     * Skip tournament cooldown via Sponsor Ad watch.
     */
    public function skipCooldownWithAd(Request $request): RedirectResponse
    {
        if (! SponsorAdController::areAdsEnabled()) {
            return redirect()->route('connect_four.index')
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

        $stat = CharacterConnectFourStat::firstOrCreate(['character_id' => $character->id]);
        if (! $stat->isOnCooldown()) {
            return redirect()->route('connect_four.index')
                ->with('info', 'Er is momenteel geen actieve wachttijd om over te slaan.');
        }

        $request->merge(['reward_type' => 'cooldown_connect_four']);
        $claimResponse = app(SponsorAdController::class)->claim($request);
        $data = $claimResponse->getData(true);

        if ($claimResponse->getStatusCode() !== 200 || empty($data['success']) || ($data['reward_type'] ?? null) !== 'cooldown_connect_four') {
            return redirect()->route('connect_four.index')
                ->with('error', $data['message'] ?? 'Bekijk eerst de volledige sponsor-advertentie om de wachttijd te resetten.');
        }

        return redirect()->route('connect_four.index')
            ->with('success', $data['reward_details'] ?? 'Dankzij de sponsor-advertentie heeft OOHPA Arjan direct zijn koffie op en staat hij te popelen voor een nieuw 5-potjes toernooi!');
    }
}

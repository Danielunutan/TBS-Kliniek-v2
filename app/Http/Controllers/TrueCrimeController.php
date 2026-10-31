<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterTrueCrimeState;
use App\Models\TrueCrimeCase;
use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrueCrimeController extends Controller
{
    public const COOLDOWN_HOURS = 24;

    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    /**
     * Display the True Crime investigation room with Caroline.
     */
    public function index(): View
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        /** @var CharacterTrueCrimeState $state */
        $state = CharacterTrueCrimeState::firstOrCreate(
            ['character_id' => $character->id],
            [
                'solved_case_ids' => [],
                'failed_case_ids' => [],
                'cases_solved_count' => 0,
                'cases_failed_count' => 0,
                'total_rewards_cash' => 0,
                'total_rewards_respect' => 0,
                'cooldown_until' => null,
            ]
        );

        $cooldownRemaining = $state->getCooldownSecondsRemaining();
        $totalCasesCount = TrueCrimeCase::count();

        // Determine active case for this 24-hour cycle or next unsolved case
        $solvedIds = $state->solved_case_ids ?? [];
        $failedIds = $state->failed_case_ids ?? [];
        $allAttempted = array_merge($solvedIds, $failedIds);

        // Daily case number based on day of year (1-500)
        $dayOfYear = now()->dayOfYear;
        $dailyCaseNum = (($dayOfYear - 1) % max(1, $totalCasesCount)) + 1;

        $activeCase = null;
        if (! $state->isOnCooldown()) {
            // Prefer current unsolved case that has not been attempted yet
            if ($state->current_case_id && ! in_array($state->current_case_id, $allAttempted)) {
                $activeCase = TrueCrimeCase::find($state->current_case_id);
            }
            if (! $activeCase) {
                // Find next case not yet solved
                $activeCase = TrueCrimeCase::whereNotIn('id', $solvedIds)->orderBy('case_number')->first();
                if (! $activeCase) {
                    // Loop back to daily case
                    $activeCase = TrueCrimeCase::where('case_number', $dailyCaseNum)->first() ?? TrueCrimeCase::first();
                }
                if ($activeCase) {
                    $state->update(['current_case_id' => $activeCase->id]);
                }
            }
        } else {
            // If on cooldown, display the last attempted case as solved/reviewed
            if ($state->current_case_id) {
                $activeCase = TrueCrimeCase::find($state->current_case_id);
            }
        }

        $adsWatched = (int) ($state->cooldown_ads_watched ?? 0);

        return view('game.truecrime.index', compact(
            'character',
            'state',
            'activeCase',
            'cooldownRemaining',
            'totalCasesCount',
            'adsWatched'
        ));
    }

    /**
     * Submit suspect verdict for the active True Crime case.
     */
    public function solve(Request $request): JsonResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je geen politiedossiers onderzoeken!',
            ], 422);
        }

        $validated = $request->validate([
            'case_id' => ['required', 'integer', 'exists:true_crime_cases,id'],
            'suspect_index' => ['required', 'integer', 'in:0,1,2,3'],
        ]);

        return DB::transaction(function () use ($character, $validated) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            /** @var CharacterTrueCrimeState $state */
            $state = CharacterTrueCrimeState::where('character_id', $lockedCharacter->id)
                ->lockForUpdate()
                ->firstOrCreate(['character_id' => $lockedCharacter->id]);

            if ($state->isOnCooldown()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Caroline is het archief nog aan het bijwerken! Wacht op de 24-uurs reset of kijk een sponsor-ad om direct een nieuw dossier te openen.',
                ], 422);
            }

            if ($state->current_case_id !== null && (int) $validated['case_id'] !== (int) $state->current_case_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dit dossier is momenteel niet jouw actieve onderzoek!',
                ], 422);
            }

            /** @var TrueCrimeCase $case */
            $case = TrueCrimeCase::findOrFail($validated['case_id']);

            $isCorrect = ((int) $validated['suspect_index'] === (int) $case->culprit_index);

            $solvedIds = $state->solved_case_ids ?? [];
            $failedIds = $state->failed_case_ids ?? [];

            $rewardCash = 0;
            $rewardXp = 0;
            $rewardRespect = 0;

            if ($isCorrect) {
                $rewardCash = $case->reward_cash;
                $rewardXp = $case->reward_xp;
                $rewardRespect = $case->reward_respect;

                $state->cases_solved_count++;
                $state->total_rewards_cash += $rewardCash;
                $state->total_rewards_respect += $rewardRespect;

                if (! in_array($case->id, $solvedIds)) {
                    $solvedIds[] = $case->id;
                }

                $lockedCharacter->increment('cash', $rewardCash);
                $lockedCharacter->increment('respect', $rewardRespect);
                $this->level->addXp($lockedCharacter, $rewardXp);

                $title = 'ZAAK OPGELOST! GEWELDIG RECHERCHEWERK!';
                $message = "Caroline glundert van trots: 'Fantastisch speurwerk, collega! Jij hebt de dader feilloos ontmaskerd.' +€".number_format($rewardCash, 0, ',', '.').", +{$rewardXp} XP en +{$rewardRespect} Respect zijn direct bijgeschreven!";
            } else {
                // Wrong suspect picked
                $rewardCash = 5000;
                $rewardXp = 15;

                $state->cases_failed_count++;
                if (! in_array($case->id, $failedIds)) {
                    $failedIds[] = $case->id;
                }

                $lockedCharacter->increment('cash', $rewardCash);
                $this->level->addXp($lockedCharacter, $rewardXp);

                $suspects = $case->suspects;
                $correctSuspect = $suspects[$case->culprit_index]['name'] ?? 'de werkelijke dader';

                $title = 'FOUTIEVE BESCHULDIGING...';
                $message = "Helaas! De verdachte had een waterdicht alibi. De werkelijke dader was {$correctSuspect}. Wel +€5.000 en +15 XP troostbeloning voor het sporenonderzoek.";
            }

            $state->solved_case_ids = $solvedIds;
            $state->failed_case_ids = $failedIds;
            // 24 hour cooldown (keep current_case_id so review screen renders solved case during cooldown)
            $state->cooldown_until = now()->addHours(self::COOLDOWN_HOURS);
            $state->cooldown_ads_watched = 0;
            $state->current_case_id = $case->id;
            $state->save();

            return response()->json([
                'success' => true,
                'is_correct' => $isCorrect,
                'title' => $title,
                'message' => $message,
                'explanation' => $case->solution_explanation,
                'reward_cash' => $rewardCash,
                'reward_xp' => $rewardXp,
                'reward_respect' => $rewardRespect,
                'cases_solved' => $state->cases_solved_count,
                'cooldown' => $state->getCooldownSecondsRemaining(),
                'cash' => $lockedCharacter->fresh()->cash,
            ]);
        });
    }

    /**
     * Skip 24-hour cooldown using Stars (15 Stars).
     */
    public function skipCooldownWithStars(): RedirectResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        return DB::transaction(function () use ($character): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var CharacterTrueCrimeState $state */
            $state = CharacterTrueCrimeState::where('character_id', $lockedCharacter->id)
                ->lockForUpdate()
                ->firstOrCreate(['character_id' => $lockedCharacter->id]);

            if (! $state->isOnCooldown()) {
                return redirect()->route('true_crime.index')
                    ->with('info', 'Er is momenteel geen actieve wachttijd om over te slaan.');
            }

            if (($lockedCharacter->stars ?? 0) < 15) {
                return back()->with('error', 'Je hebt minimaal 15 Sterren nodig om Caroline\'s wachttijd over te slaan!');
            }

            $lockedCharacter->stars = ($lockedCharacter->stars ?? 0) - 15;
            $lockedCharacter->save();

            $state->update([
                'cooldown_until' => null,
                'cooldown_ads_watched' => 0,
                'current_case_id' => null,
            ]);

            return redirect()->route('true_crime.index')->with('success', '⭐ Wachttijd succesvol overgeslagen voor 15 Sterren! Caroline heeft direct een nieuw dossier voor je klaarstaan.');
        });
    }

    /**
     * Skip 24-hour cooldown using Sponsor Ad (5 ads required).
     */
    public function skipCooldownWithAd(Request $request): RedirectResponse
    {
        if (! SponsorAdController::areAdsEnabled()) {
            return redirect()->route('true_crime.index')
                ->with('error', 'Sponsor-advertenties zijn momenteel uitgeschakeld. Gebruik Sterren ⭐ om de wachttijd over te slaan.');
        }

        /** @var Character $character */
        $character = Auth::user()->character;

        /** @var CharacterTrueCrimeState $state */
        $state = CharacterTrueCrimeState::firstOrCreate(['character_id' => $character->id]);

        if (! $state->isOnCooldown()) {
            return redirect()->route('true_crime.index')
                ->with('info', 'Er is momenteel geen actieve wachttijd om over te slaan.');
        }

        $current = ($state->cooldown_ads_watched ?? 0) + 1;
        if ($current >= 5) {
            $state->update([
                'cooldown_until' => null,
                'cooldown_ads_watched' => 0,
                'current_case_id' => null,
            ]);

            return redirect()->route('true_crime.index')->with('success', '🎉 [5/5 Ads] Gefeliciteerd! Je hebt 5 advertenties bekeken. Caroline heeft direct een nieuw dossier voor je ontgrendeld!');
        }

        $state->update([
            'cooldown_ads_watched' => $current,
        ]);
        $left = 5 - $current;

        return redirect()->route('true_crime.index')->with('success', "🔍 [{$current}/5 Ads] Advertentie geregistreerd! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' om Caroline\'s 24-uurs wachttijd te omzeilen.');
    }
}

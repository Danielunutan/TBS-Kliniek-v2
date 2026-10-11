<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\TurnstileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FightController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
        protected DailyBattleService $battleService,
        protected TurnstileService $turnstileService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $today = now()->format('Y-m-d');
        if ($character->daily_attacks_date !== $today) {
            $character->daily_attacks_count = 0;
            $character->daily_attacks_date = $today;
            $character->save();
        }

        $attacksToday = $character->today_attacks_count;
        $maxDailyAttacks = 100;
        $attacksRemaining = $character->remaining_daily_attacks;

        $cooldownSeconds = $this->cooldownService->getRemainingSeconds($character, 'fight_attack');

        // Check if captcha is required (only if session flag is set, no random surprises on cooldown)
        $isCaptchaRequired = (bool) session('fight_captcha_required', false);

        $turnstileSiteKey = config('services.turnstile.site_key');

        $opponents = Character::with('gang')
            ->where('id', '!=', $character->id)
            ->where('is_in_prison', false)
            ->orderByDesc('respect')
            ->paginate(15);

        $topRankings = Character::orderByDesc('respect')->take(10)->get();

        return view('game.fight.index', compact(
            'character',
            'opponents',
            'topRankings',
            'attacksToday',
            'maxDailyAttacks',
            'attacksRemaining',
            'cooldownSeconds',
            'isCaptchaRequired',
            'turnstileSiteKey'
        ));
    }

    /**
     * Show animated suspense transition screen for cash theft / ambush on the luchtplaats.
     */
    public function arena(Character $defender): View|RedirectResponse
    {
        $attacker = Auth::user()->character;
        $arenaBattle = session('arena_battle');

        if (! $arenaBattle || $arenaBattle['defender_id'] !== $defender->id) {
            return redirect()->route('fight.index');
        }

        return view('game.fight.arena', compact('attacker', 'defender', 'arenaBattle'));
    }

    public function attack(Request $request): RedirectResponse
    {
        $attacker = Auth::user()->character;
        $this->energy->refresh($attacker);

        if ($attacker->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niemand beroven!');
        }

        // 1. 3-second cooldown check
        if ($this->cooldownService->isOnCooldown($attacker, 'fight_attack')) {
            $remaining = $this->cooldownService->getRemainingSeconds($attacker, 'fight_attack');

            return back()->with('error', "⏳ Even ademhalen! Wacht nog {$remaining}s voor je volgende beroving.");
        }

        // 2. 100 total attacks/thefts per day limit
        $today = now()->format('Y-m-d');
        if ($attacker->daily_attacks_date !== $today) {
            $attacker->daily_attacks_count = 0;
            $attacker->daily_attacks_date = $today;
            $attacker->save();
        }

        if ($attacker->daily_attacks_count >= 100) {
            return back()->with('error', '⛔ Je hebt je dagelijkse limiet van 100 aanvallen bereikt! Morgen kun je weer geld stelen.');
        }

        // 3. Cloudflare Turnstile Captcha check if required
        if (session('fight_captcha_required')) {
            $turnstileToken = $request->input('cf-turnstile-response') ?: $request->header('X-Turnstile-Token');

            if (! $this->turnstileService->verify($turnstileToken, $request->ip())) {
                return back()->with('error', '🛡️ Beveiligingscontrole mislukt! Bevestig via de Cloudflare Turnstile verificatie dat je geen bot bent.');
            }

            // Captcha passed
            session()->forget('fight_captcha_required');
        }

        if ($attacker->health < 15) {
            return back()->with('error', 'Je bent te zwaar gewond om te beroven. Rust eerst uit of gebruik EHBO spullen.');
        }

        $validated = $request->validate([
            'opponent_id' => 'required|exists:characters,id',
            'cf-turnstile-response' => 'nullable|string',
        ]);

        $defender = Character::findOrFail($validated['opponent_id']);

        if ($defender->id === $attacker->id) {
            return back()->with('error', 'Je kunt niet uit je eigen zakken stelen!');
        }

        // 4. Max 100 attacks/thefts against the same person per day
        $targetKey = "daily_attacks_{$attacker->id}_{$defender->id}_{$today}";
        $targetAttacks = (int) cache()->get($targetKey, 0);
        if ($targetAttacks >= 100) {
            return back()->with('error', "⛔ Je hebt {$defender->name} vandaag al 100 keer geprobeerd te bestelen! Zoek een ander doelwit.");
        }

        if ($defender->isImprisoned()) {
            return back()->with('error', 'Deze patiënt zit veilig in de isoleercel en kan niet worden bestolen.');
        }

        if ($defender->isProtected()) {
            return back()->with('error', "{$defender->name} zit in isolatie of heeft cashbescherming en kan niet worden bestolen!");
        }

        if (! $this->energy->consume($attacker, 20)) {
            return back()->with('error', 'Je hebt niet genoeg energie (20 nodig) om geld te stelen.');
        }

        // Apply 3-second cooldown
        $this->cooldownService->setCooldown($attacker, 'fight_attack', 3);

        // Increment daily attacks
        $attacker->daily_attacks_count += 1;
        $attacker->daily_attacks_date = $today;
        $attacker->save();

        cache()->put($targetKey, $targetAttacks + 1, now()->endOfDay());

        // Combat calculation using strength & defense to determine if theft succeeds
        $attackerPower = $attacker->strength + rand(5, 15);
        $defenderPower = $defender->defense + rand(5, 15);

        if ($attackerPower >= $defenderPower) {
            // Attacker succeeds: Steal between 10% and 25% of cash on hand (just like diefstal / zakkenrollen)
            $defenderCash = max(0, (int) $defender->cash);
            $stolenCash = 0;

            if ($defenderCash > 0) {
                $stolenRatio = random_int(10, 25) / 100;
                $stolenCash = max(15, (int) round($defenderCash * $stolenRatio));
                if ($stolenCash > $defenderCash) {
                    $stolenCash = $defenderCash;
                }

                $this->economy->deductCash($defender, $stolenCash);
                $this->economy->addCash($attacker, $stolenCash);
            }

            $attacker->thefts_succeeded = ($attacker->thefts_succeeded ?? 0) + 1;
            $attacker->fights_won += 1;
            $defender->fights_lost += 1;
            $defender->health = max(0, $defender->health - rand(10, 25));
            $defender->save();

            $this->level->addXp($attacker, 25);
            $attacker->respect += 2;
            $attacker->save();

            // Record in 24 Hour Battle
            $this->battleService->record($attacker, 'kills', 1);
            $this->battleService->record($attacker, 'slay', 2);

            $notificationMessage = $stolenCash > 0
                ? "{$attacker->name} heeft je overmeesterd op de luchtplaats en €".number_format($stolenCash, 0, ',', '.').' contant uit je zakken geroofd!'
                : "{$attacker->name} heeft je overmeesterd op de luchtplaats om je te beroven, maar je had gelukkig geen contanten op zak!";

            GameNotification::create([
                'character_id' => $defender->id,
                'type' => 'theft',
                'icon' => 'fa-sack-dollar',
                'title' => 'Bestolen op de luchtplaats!',
                'message' => $notificationMessage,
            ]);

            session()->flash('arena_battle', [
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'defender_name' => $defender->name,
                'defender_avatar' => $defender->avatar_url,
                'defender_level' => $defender->level,
                'won' => true,
                'stolen_cash' => $stolenCash,
                'defender_had_no_cash' => ($defenderCash <= 0),
                'damage' => 0,
                'xp' => 25,
                'respect' => 2,
                'left_today' => 100 - $attacker->daily_attacks_count,
            ]);

            return redirect()->route('fight.arena', $defender);
        } else {
            // Attacker fails: Defender repels theft attempt
            $attacker->fights_lost += 1;
            $defender->fights_won += 1;
            $damage = rand(10, 20);
            $attacker->health = max(0, $attacker->health - $damage);
            $attacker->save();
            $defender->save();

            GameNotification::create([
                'character_id' => $defender->id,
                'type' => 'theft',
                'icon' => 'fa-shield-halved',
                'title' => 'Berovingspoging afgeslagen!',
                'message' => "{$attacker->name} probeerde je contanten te stelen op de luchtplaats, maar jij hebt hem hard teruggeslagen!",
            ]);

            session()->flash('arena_battle', [
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'defender_name' => $defender->name,
                'defender_avatar' => $defender->avatar_url,
                'defender_level' => $defender->level,
                'won' => false,
                'stolen_cash' => 0,
                'defender_had_no_cash' => false,
                'damage' => $damage,
                'xp' => 0,
                'respect' => 0,
                'left_today' => 100 - $attacker->daily_attacks_count,
            ]);

            return redirect()->route('fight.arena', $defender);
        }
    }
}

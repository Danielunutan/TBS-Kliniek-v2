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

        // Check if captcha is required
        $isCaptchaRequired = session('fight_captcha_required', false) || ($cooldownSeconds > 0 && rand(1, 100) <= 25);
        if ($isCaptchaRequired) {
            session(['fight_captcha_required' => true]);
        }

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
     * Show animated 5-second fight suspense transition arena screen.
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
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet vechten!');
        }

        // 1. 3-second cooldown check
        if ($this->cooldownService->isOnCooldown($attacker, 'fight_attack')) {
            $remaining = $this->cooldownService->getRemainingSeconds($attacker, 'fight_attack');

            return back()->with('error', "⏳ Even ademhalen! Wacht nog {$remaining}s voor je volgende aanval.");
        }

        // 2. 100 total attacks per day limit
        $today = now()->format('Y-m-d');
        if ($attacker->daily_attacks_date !== $today) {
            $attacker->daily_attacks_count = 0;
            $attacker->daily_attacks_date = $today;
            $attacker->save();
        }

        if ($attacker->daily_attacks_count >= 100) {
            return back()->with('error', '⛔ Je hebt je dagelijkse limiet van 100 aanvallen bereikt! Morgen kun je weer vechten.');
        }

        // 3. Cloudflare Turnstile Captcha check if required
        if (session('fight_captcha_required')) {
            $turnstileToken = $request->input('cf-turnstile-response');

            if (! $this->turnstileService->verify($turnstileToken, $request->ip())) {
                return back()->with('error', '🛡️ Beveiligingscontrole mislukt! Bevestig via de Cloudflare Turnstile verificatie dat je geen bot bent.');
            }

            // Captcha passed
            session()->forget('fight_captcha_required');
        }

        if ($attacker->health < 15) {
            return back()->with('error', 'Je bent te zwaar gewond om te vechten. Rust eerst uit of gebruik EHBO spullen.');
        }

        $validated = $request->validate([
            'opponent_id' => 'required|exists:characters,id',
            'cf-turnstile-response' => 'nullable|string',
        ]);

        $defender = Character::findOrFail($validated['opponent_id']);

        if ($defender->id === $attacker->id) {
            return back()->with('error', 'Je kunt jezelf niet aanvallen!');
        }

        // 4. Max 100 attacks against the same person per day
        $targetKey = "daily_attacks_{$attacker->id}_{$defender->id}_{$today}";
        $targetAttacks = (int) cache()->get($targetKey, 0);
        if ($targetAttacks >= 100) {
            return back()->with('error', "⛔ Je hebt {$defender->name} vandaag al 100 keer aangevallen! Zoek een ander doelwit.");
        }

        if ($defender->isImprisoned()) {
            return back()->with('error', 'Deze patiënt zit veilig in de isoleercel en kan niet aangevallen worden.');
        }

        if ($defender->isProtected()) {
            return back()->with('error', "{$defender->name} zit in vrijwillige isolatie of heeft cashbescherming en kan niet worden aangevallen!");
        }

        if (! $this->energy->consume($attacker, 20)) {
            return back()->with('error', 'Je hebt niet genoeg energie (20 nodig) om aan te vallen.');
        }

        // Apply 3-second cooldown
        $this->cooldownService->setCooldown($attacker, 'fight_attack', 3);

        // Increment daily attacks
        $attacker->daily_attacks_count += 1;
        $attacker->daily_attacks_date = $today;
        $attacker->save();

        cache()->put($targetKey, $targetAttacks + 1, now()->endOfDay());

        // Trigger Turnstile occasionally (e.g. ~12% chance or every 15 attacks)
        if (rand(1, 100) <= 12 || $attacker->daily_attacks_count % 15 === 0) {
            session(['fight_captcha_required' => true]);
        }

        // Combat calculation using strength & defense
        $attackerPower = $attacker->strength + rand(5, 15);
        $defenderPower = $defender->defense + rand(5, 15);

        if ($attackerPower >= $defenderPower) {
            // Attacker wins
            $stolenCash = min($defender->cash, rand(50, 250));
            if ($stolenCash > 0) {
                $this->economy->deductCash($defender, $stolenCash);
                $this->economy->addCash($attacker, $stolenCash);
            }

            $attacker->fights_won += 1;
            $defender->fights_lost += 1;
            $defender->health = max(0, $defender->health - rand(15, 30));
            $defender->save();

            $this->level->addXp($attacker, 25);
            $attacker->respect += 2;
            $attacker->save();

            // Record in 24 Hour Battle
            $this->battleService->record($attacker, 'kills', 1);
            $this->battleService->record($attacker, 'slay', 2);

            GameNotification::create([
                'character_id' => $defender->id,
                'type' => 'fight',
                'icon' => 'fa-hand-fist',
                'title' => 'Aangevallen op de luchtplaats!',
                'message' => "{$attacker->name} heeft je aangevallen en verslagen! Je verloor €".number_format($stolenCash, 0, ',', '.').'.',
            ]);

            session()->flash('arena_battle', [
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'defender_name' => $defender->name,
                'defender_avatar' => $defender->avatar_url,
                'defender_level' => $defender->level,
                'won' => true,
                'stolen_cash' => $stolenCash,
                'damage' => 0,
                'xp' => 25,
                'respect' => 2,
                'left_today' => 100 - $attacker->daily_attacks_count,
            ]);

            return redirect()->route('fight.arena', $defender);
        } else {
            // Attacker loses
            $attacker->fights_lost += 1;
            $defender->fights_won += 1;
            $damage = rand(15, 25);
            $attacker->health = max(0, $attacker->health - $damage);
            $attacker->save();
            $defender->save();

            GameNotification::create([
                'character_id' => $defender->id,
                'type' => 'fight',
                'icon' => 'fa-shield-halved',
                'title' => 'Aanval afgeslagen!',
                'message' => "{$attacker->name} probeerde je aan te vallen op de luchtplaats, maar jij hebt hem verrot geschopt!",
            ]);

            session()->flash('arena_battle', [
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'defender_name' => $defender->name,
                'defender_avatar' => $defender->avatar_url,
                'defender_level' => $defender->level,
                'won' => false,
                'stolen_cash' => 0,
                'damage' => $damage,
                'xp' => 0,
                'respect' => 0,
                'left_today' => 100 - $attacker->daily_attacks_count,
            ]);

            return redirect()->route('fight.arena', $defender);
        }
    }
}

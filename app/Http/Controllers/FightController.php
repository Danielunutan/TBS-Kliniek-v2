<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Character;
use App\Models\GameNotification;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\TurnstileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function index(Request $request): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);
        $character->isImprisoned(); // auto-release if expired

        $today = now()->format('Y-m-d');
        if ($character->daily_attacks_date !== $today) {
            $character->daily_attacks_count = 0;
            $character->daily_attacks_date = $today;
            $character->save();
        }

        $attacksToday = $character->today_attacks_count;
        $maxDailyAttacks = $character->isVip() ? 1000 : 100;
        $attacksRemaining = $character->remaining_daily_attacks;

        $cooldownSeconds = $this->cooldownService->getRemainingSeconds($character, 'fight_attack');

        // VIP members are exempt from routine captcha interruptions
        if ($character->isVip()) {
            session()->forget('fight_captcha_required');
            session(['fight_consecutive_attacks' => 0]);
        }

        // Check if captcha is required (only for non-VIPs if session flag is set)
        $isCaptchaRequired = ! $character->isVip() && (bool) session('fight_captcha_required', false);

        $turnstileSiteKey = config('services.turnstile.site_key');

        $sort = $request->query('sort', 'respect');

        $query = Character::with('gang')
            ->where('id', '!=', $character->id)
            ->where('is_in_prison', false);

        if ($sort === 'cash') {
            $query->orderByDesc('cash')->orderByDesc('respect');
        } elseif ($sort === 'online') {
            $query->orderByDesc('last_active_at')->orderByDesc('respect');
        } else {
            $sort = 'respect';
            $query->orderByDesc('respect');
        }

        $opponents = $query->paginate(15)->withQueryString();

        return view('game.fight.index', compact(
            'character',
            'opponents',
            'attacksToday',
            'maxDailyAttacks',
            'attacksRemaining',
            'cooldownSeconds',
            'isCaptchaRequired',
            'turnstileSiteKey',
            'sort'
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

        if ($attacker->isComatose()) {
            $mins = ceil($attacker->getComaRemainingSeconds() / 60);

            return back()->with('error', "⚠️ Je ligt in Intensive Care Coma (nog {$mins} min)! Ontwaak direct via een sponsor advertentie.");
        }

        // 1. 3-second cooldown check
        if ($this->cooldownService->isOnCooldown($attacker, 'fight_attack')) {
            $remaining = $this->cooldownService->getRemainingSeconds($attacker, 'fight_attack');

            return back()->with('error', "⏳ Even ademhalen! Wacht nog {$remaining}s voor je volgende beroving.");
        }

        // 2. Daily attacks/thefts limit: 100 normal, 1000 for VIP
        $today = now()->format('Y-m-d');
        if ($attacker->daily_attacks_date !== $today) {
            $attacker->daily_attacks_count = 0;
            $attacker->daily_attacks_date = $today;
            $attacker->save();
        }

        $dailyLimit = $attacker->isVip() ? 1000 : 100;
        if ($attacker->daily_attacks_count >= $dailyLimit) {
            return back()->with('error', "⛔ Je hebt je dagelijkse limiet van {$dailyLimit} aanvallen bereikt! Morgen kun je weer geld stelen.");
        }

        // 3. Cloudflare Turnstile Captcha check if required (VIPs are exempt)
        if (! $attacker->isVip() && session('fight_captcha_required')) {
            $turnstileToken = $request->input('cf-turnstile-response') ?: $request->header('X-Turnstile-Token');

            if (! $this->turnstileService->verify($turnstileToken, $request->ip())) {
                return redirect()->route('fight.index')->with('error', '🛡️ Zaalwacht controle vereist! Bevestig via de Cloudflare Turnstile verificatie op de luchtplaats dat je een echte speler bent om te kunnen beroven.');
            }

            // Captcha passed
            session()->forget('fight_captcha_required');
            session(['fight_consecutive_attacks' => 0]);
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

        if ($defender->isComatose()) {
            return back()->with('error', "{$defender->name} ligt in Intensive Care Coma en kan niet worden bestolen.");
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

        // Anti-bot trigger: require Cloudflare Turnstile verification after consecutive attacks (VIPs are exempt)
        if (! $attacker->isVip()) {
            $consecutive = (int) session('fight_consecutive_attacks', 0) + 1;
            if ($consecutive >= 35) {
                session(['fight_captcha_required' => true]);
                session(['fight_consecutive_attacks' => 0]);
            } else {
                session(['fight_consecutive_attacks' => $consecutive]);
            }
        }

        // Combat calculation using effective strength & defense (including backpack equipment)
        $attackerPower = $attacker->getEffectiveStrength() + rand(5, 15);
        $defenderPower = $defender->getEffectiveDefense() + rand(5, 15);

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

            // Damage mitigated by defender's defense
            $damageTaken = max(5, rand(12, 25) - (int) floor($defender->getEffectiveDefense() / 15));
            $defender->health = max(0, $defender->health - $damageTaken);
            $defender->save();

            $this->level->addXp($attacker, 25);
            $attacker->respect += 2;

            // 20% chance to gain +1 permanent Kracht from winning a real brawl
            if (rand(1, 100) <= 20) {
                $attacker->strength = ($attacker->strength ?? 10) + 1;
            }
            $attacker->save();

            // Record in 24 Hour Battle
            $this->battleService->record($attacker, 'kills', 1);
            $this->battleService->record($attacker, 'slay', 2);
            HalloweenService::record($attacker, 'brawl', 1);
            HalloweenService::record($attacker, 'brawl_win', 1);
            if ($stolenCash > 0) {
                HalloweenService::record($attacker, 'cash_earned', $stolenCash);
            }

            // Check for and claim any active bounties on defender atomically
            $claimedBountyCash = 0;
            $claimedBounties = DB::transaction(function () use ($defender, $attacker, &$claimedBountyCash) {
                $bountiesToClaim = Bounty::where('target_character_id', $defender->id)
                    ->where('is_claimed', false)
                    ->lockForUpdate()
                    ->get();

                if ($bountiesToClaim->isNotEmpty()) {
                    $claimedBountyCash = (int) $bountiesToClaim->sum('amount');
                    $this->economy->addCash($attacker, $claimedBountyCash);
                    foreach ($bountiesToClaim as $bounty) {
                        $bounty->update([
                            'is_claimed' => true,
                            'claimed_by_character_id' => $attacker->id,
                            'claimed_at' => now(),
                        ]);

                        GameNotification::create([
                            'character_id' => $bounty->placed_by_character_id,
                            'type' => 'bounty_claimed',
                            'icon' => 'fa-crosshairs',
                            'title' => 'Kopgeld Geïncasseerd!',
                            'message' => 'Het kopgeld van €'.number_format($bounty->amount, 0, ',', '.')." dat je op {$defender->name} had gezet is zojuist geclaimd door {$attacker->name}!",
                            'is_read' => false,
                        ]);
                    }
                }

                return $bountiesToClaim;
            });

            $notificationMessage = $stolenCash > 0
                ? "{$attacker->name} heeft je overmeesterd op de luchtplaats en €".number_format($stolenCash, 0, ',', '.').' contant uit je zakken geroofd!'
                : "{$attacker->name} heeft je overmeesterd op de luchtplaats om je te beroven, maar je had gelukkig geen contanten op zak!";

            if ($claimedBountyCash > 0) {
                $notificationMessage .= ' Tevens is het openstaande kopgeld van €'.number_format($claimedBountyCash, 0, ',', '.').' op jouw hoofd geïncasseerd!';
            }

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
                'claimed_bounty' => $claimedBountyCash,
                'defender_had_no_cash' => ($defenderCash <= 0),
                'damage' => 0,
                'xp' => 25,
                'respect' => 2,
                'left_today' => max(0, $dailyLimit - $attacker->daily_attacks_count),
                'from_profile' => $request->boolean('from_profile'),
            ]);

            return redirect()->route('fight.arena', $defender);
        } else {
            // Attacker fails: Defender repels theft attempt
            $attacker->fights_lost += 1;
            $defender->fights_won += 1;
            $damage = rand(10, 20);
            $attacker->health = max(0, $attacker->health - $damage);
            $attacker->save();

            // 20% chance for defender to gain +1 defense from defending their ground
            if (rand(1, 100) <= 20) {
                $defender->defense = ($defender->defense ?? 10) + 1;
            }
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
                'left_today' => max(0, $dailyLimit - $attacker->daily_attacks_count),
                'from_profile' => $request->boolean('from_profile'),
            ]);

            return redirect()->route('fight.arena', $defender);
        }
    }
}

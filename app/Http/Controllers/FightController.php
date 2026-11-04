<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\TurnstileService;
use Illuminate\Http\JsonResponse;
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

        if (session()->has('pending_arena_battle')) {
            $this->commitPendingBattle($character);
            $character->refresh();
        }

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
        $arenaBattle = session('pending_arena_battle') ?? session('arena_battle');

        if (! $arenaBattle || $arenaBattle['defender_id'] !== $defender->id) {
            return redirect()->route('fight.index');
        }

        return view('game.fight.arena', compact('attacker', 'defender', 'arenaBattle'));
    }

    /**
     * Resolve pending arena theft upon countdown completion or skip.
     */
    public function resolveArena(Character $defender): JsonResponse
    {
        $attacker = Auth::user()->character;
        $battle = session('pending_arena_battle') ?? session('arena_battle');

        if (! $battle || $battle['defender_id'] !== $defender->id) {
            return response()->json([
                'success' => false,
                'message' => 'Geen actieve beroving gevonden.',
            ], 404);
        }

        if (empty($battle['resolved'])) {
            $battle = $this->commitPendingBattle($attacker);
        }

        $attacker->refresh();

        $stats = [
            'cash' => (int) $attacker->cash,
            'cashFormatted' => '€'.number_format($attacker->cash, 0, ',', '.'),
            'cash_formatted' => '€'.number_format($attacker->cash, 0, ',', '.'),
            'bank' => (int) $attacker->bank,
            'bankFormatted' => '€'.number_format($attacker->bank, 0, ',', '.'),
            'bank_formatted' => '€'.number_format($attacker->bank, 0, ',', '.'),
            'total' => (int) ($attacker->cash + $attacker->bank),
            'totalFormatted' => '€'.number_format($attacker->cash + $attacker->bank, 0, ',', '.'),
            'total_formatted' => '€'.number_format($attacker->cash + $attacker->bank, 0, ',', '.'),
            'energy' => (int) $attacker->energy,
            'max_energy' => (int) $attacker->max_energy,
            'energy_pct' => min(100, max(0, round(($attacker->energy / max(1, $attacker->max_energy)) * 100))),
            'health' => (int) $attacker->health,
            'max_health' => (int) $attacker->max_health,
            'health_pct' => min(100, max(0, round(($attacker->health / max(1, $attacker->max_health)) * 100))),
            'xp' => (int) $attacker->xp,
            'respect' => $attacker->formatted_respect,
            'formatted_respect' => $attacker->formatted_respect,
        ];

        return response()->json([
            'success' => true,
            'won' => (bool) ($battle['won'] ?? false),
            'stolen_cash' => (int) ($battle['stolen_cash'] ?? 0),
            'claimed_bounty' => (int) ($battle['claimed_bounty'] ?? 0),
            'xp' => (int) ($battle['xp'] ?? 0),
            'respect' => (int) ($battle['respect'] ?? 0),
            'damage' => (int) ($battle['damage'] ?? 0),
            'gained_strength' => (bool) ($battle['gained_strength'] ?? false),
            'stats' => $stats,
        ]);
    }

    /**
     * Atomically finalize and execute database changes for an arena battle.
     */
    public function commitPendingBattle(Character $attacker): ?array
    {
        $battle = session('pending_arena_battle') ?? session('arena_battle');
        if (! $battle || ! empty($battle['resolved'])) {
            return $battle;
        }

        if (! empty($battle['attacker_id']) && (int) $battle['attacker_id'] !== (int) $attacker->id) {
            return null;
        }

        $defender = Character::find($battle['defender_id']);
        if (! $defender) {
            session()->forget(['pending_arena_battle', 'arena_battle']);

            return null;
        }

        $battle = DB::transaction(function () use ($attacker, $defender, $battle): array {
            $ids = [(int) $attacker->id, (int) $defender->id];
            sort($ids);
            Character::whereIn('id', $ids)->lockForUpdate()->get();

            $lockedAttacker = Character::find($attacker->id);
            $lockedDefender = Character::find($defender->id);

            if ($battle['won']) {
                $defenderCash = max(0, (int) $lockedDefender->cash);
                $stolenCash = (int) ($battle['stolen_cash'] ?? 0);
                if ($lockedDefender->isProtected()) {
                    $stolenCash = 0;
                } else {
                    if ($lockedDefender->hasCashProtection() && empty($battle['cash_shield_applied']) && $stolenCash > 0) {
                        $stolenCash = max(1, (int) floor($stolenCash * 0.50));
                    }
                    if ($stolenCash > $defenderCash) {
                        $stolenCash = $defenderCash;
                    }
                }

                if ($stolenCash > 0) {
                    // Only credit the attacker when the defender really paid, so cash is never created from nothing.
                    if ($this->economy->deductCash($lockedDefender, $stolenCash)) {
                        $this->economy->addCash($lockedAttacker, $stolenCash);
                    } else {
                        $stolenCash = 0;
                    }
                }

                $lockedAttacker->thefts_succeeded = ($lockedAttacker->thefts_succeeded ?? 0) + 1;
                $lockedAttacker->fights_won += 1;
                $lockedDefender->fights_lost += 1;

                $damageTaken = (int) ($battle['damage_taken'] ?? 10);
                $lockedDefender->health = max(0, $lockedDefender->health - $damageTaken);
                $lockedDefender->save();

                $this->level->addXp($lockedAttacker, (int) ($battle['xp'] ?? 25));
                $lockedAttacker->addRespect((int) ($battle['respect'] ?? 2));

                if (! empty($battle['gained_strength'])) {
                    $maxStrength = $lockedAttacker->getMaxStatForLevel();
                    if (($lockedAttacker->strength ?? 10) < $maxStrength) {
                        $lockedAttacker->strength = ($lockedAttacker->strength ?? 10) + 1;
                    }
                }
                $lockedAttacker->save();

                // Record in 24 Hour Battle & events
                $this->battleService->record($lockedAttacker, 'kills', 1);
                $this->battleService->record($lockedAttacker, 'slay', 2);
                HalloweenService::record($lockedAttacker, 'brawl', 1);
                HalloweenService::record($lockedAttacker, 'brawl_win', 1);
                if ($stolenCash > 0) {
                    HalloweenService::record($lockedAttacker, 'cash_earned', $stolenCash);
                }

                // Check for and claim any active bounties on defender atomically
                $claimedBountyCash = 0;
                $bountiesToClaim = Bounty::where('target_character_id', $lockedDefender->id)
                    ->where('is_claimed', false)
                    ->lockForUpdate()
                    ->get();

                if ($bountiesToClaim->isNotEmpty()) {
                    $claimedBountyCash = (int) $bountiesToClaim->sum('amount');
                    $this->economy->addCash($lockedAttacker, $claimedBountyCash);
                    foreach ($bountiesToClaim as $bounty) {
                        $bounty->update([
                            'is_claimed' => true,
                            'claimed_by_character_id' => $lockedAttacker->id,
                            'claimed_at' => now(),
                        ]);

                        GameNotification::create([
                            'character_id' => $bounty->placed_by_character_id,
                            'type' => 'bounty_claimed',
                            'icon' => 'fa-crosshairs',
                            'title' => 'Kopgeld Geïncasseerd!',
                            'message' => 'Het kopgeld van €'.number_format($bounty->amount, 0, ',', '.')." dat je op {$lockedDefender->name} had gezet is zojuist geclaimd door {$lockedAttacker->name}!",
                            'is_read' => false,
                        ]);
                    }
                }

                $notificationMessage = $stolenCash > 0
                    ? "{$lockedAttacker->name} heeft je overmeesterd op de luchtplaats en €".number_format($stolenCash, 0, ',', '.').' contant uit je zakken geroofd!'
                    : "{$lockedAttacker->name} heeft je overmeesterd op de luchtplaats om je te beroven, maar je had gelukkig geen contanten op zak!";

                if ($claimedBountyCash > 0) {
                    $notificationMessage .= ' Tevens is het openstaande kopgeld van €'.number_format($claimedBountyCash, 0, ',', '.').' op jouw hoofd geïncasseerd!';
                }

                GameNotification::create([
                    'character_id' => $lockedDefender->id,
                    'type' => 'theft',
                    'icon' => 'fa-sack-dollar',
                    'title' => 'Bestolen op de luchtplaats!',
                    'message' => $notificationMessage,
                ]);

                if ($stolenCash > 0) {
                    GameMessage::create([
                        'recipient_id' => $lockedDefender->id,
                        'sender_id' => $lockedAttacker->id,
                        'sender_name' => 'Kliniek Beveiliging Alert',
                        'subject' => '🚨 ALARM: Bestolen op de Luchtplaats!',
                        'content' => "Patiënt {$lockedDefender->name},\n\nTerwijl je op de Luchtplaats stond, heeft {$lockedAttacker->name} je zojuist overmeesterd en €"
                            .number_format($stolenCash, 0, ',', '.').' contant uit je zakken geroofd!'.($claimedBountyCash > 0 ? "\n\nTevens is het openstaande kopgeld van €".number_format($claimedBountyCash, 0, ',', '.').' op jouw hoofd geïncasseerd!' : '')."\n\nTip: Breng je geld regelmatig in veiligheid bij de Geldmaat.",
                        'type' => 'theft',
                    ]);
                }

                $battle['stolen_cash'] = $stolenCash;
                $battle['claimed_bounty'] = $claimedBountyCash;
            } else {
                $lockedAttacker->fights_lost += 1;
                $lockedDefender->fights_won += 1;
                $damage = (int) ($battle['damage'] ?? 10);
                $lockedAttacker->health = max(0, $lockedAttacker->health - $damage);
                $lockedAttacker->save();

                $maxDefense = $lockedDefender->getMaxStatForLevel();
                if (($lockedDefender->defense ?? 10) < $maxDefense && rand(1, 100) <= 35) {
                    $lockedDefender->defense = ($lockedDefender->defense ?? 10) + 1;
                }
                $lockedDefender->save();

                GameNotification::create([
                    'character_id' => $lockedDefender->id,
                    'type' => 'theft',
                    'icon' => 'fa-shield-halved',
                    'title' => 'Berovingspoging afgeslagen!',
                    'message' => "{$lockedAttacker->name} probeerde je contanten te stelen op de luchtplaats, maar jij hebt hem hard teruggeslagen!",
                ]);
            }

            return $battle;
        });

        $battle['resolved'] = true;
        session(['arena_battle' => $battle]);
        session()->forget('pending_arena_battle');

        return $battle;
    }

    public function attack(Request $request): RedirectResponse
    {
        $attacker = Auth::user()->character;

        if (session()->has('pending_arena_battle')) {
            $this->commitPendingBattle($attacker);
            $attacker->refresh();
        }

        $this->energy->refresh($attacker);

        if ($attacker->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niemand beroven!');
        }

        $wasInIsolation = $attacker->isInVoluntaryIsolation();
        if ($wasInIsolation && ! $request->boolean('confirm_break_isolation')) {
            $remaining = $attacker->protection_until->diffForHumans(null, true);

            return back()
                ->with('error', "🔒 Je cel is vergrendeld in Vrijwillige Isolatie (nog {$remaining})! Bevestig dat je je isolatie wilt beëindigen om een aanval uit te voeren.")
                ->with('error_url', route('isolation.index'));
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
            return back()->with('error', "{$defender->name} zit in isolatie en kan niet worden bestolen!");
        }

        if (! $this->energy->consume($attacker, 20)) {
            return back()->with('error', 'Je hebt niet genoeg energie (20 nodig) om geld te stelen.');
        }

        // Attacker breaks own protection when initiating attack
        $attacker->breakProtection();

        if ($wasInIsolation) {
            GameNotification::create([
                'character_id' => $attacker->id,
                'type' => 'theft',
                'icon' => 'fa-door-open',
                'title' => 'Isolatie Vroegtijdig Beëindigd',
                'message' => 'Je hebt je cel verlaten om een beroving uit te voeren. Je isolatiebescherming is beëindigd.',
            ]);
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
            $defenderCash = max(0, (int) $defender->cash);
            $stolenCash = 0;
            $hasCashShield = $defender->hasCashProtection();

            if ($defenderCash > 0) {
                $stolenRatio = random_int(10, 25) / 100;
                $stolenCash = max(15, (int) round($defenderCash * $stolenRatio));
                if ($hasCashShield) {
                    $stolenCash = max(1, (int) floor($stolenCash * 0.50));
                }
                if ($stolenCash > $defenderCash) {
                    $stolenCash = $defenderCash;
                }
            }

            $damageTaken = max(5, rand(12, 25) - (int) floor($defender->getEffectiveDefense() / 15));
            $maxStrength = $attacker->getMaxStatForLevel();
            $gainedStrength = (($attacker->strength ?? 10) < $maxStrength && rand(1, 100) <= 35);

            $battleData = [
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'defender_name' => $defender->name,
                'defender_avatar' => $defender->avatar_url,
                'defender_level' => $defender->level,
                'won' => true,
                'stolen_cash' => $stolenCash,
                'cash_shield_applied' => $hasCashShield,
                'claimed_bounty' => 0,
                'defender_had_no_cash' => ($defenderCash <= 0),
                'damage_taken' => $damageTaken,
                'damage' => 0,
                'xp' => 25,
                'respect' => 2,
                'gained_strength' => $gainedStrength,
                'left_today' => max(0, $dailyLimit - $attacker->daily_attacks_count),
                'max_today' => $dailyLimit,
                'from_profile' => $request->boolean('from_profile'),
                'resolved' => false,
            ];
        } else {
            $damage = rand(10, 20);
            $battleData = [
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'defender_name' => $defender->name,
                'defender_avatar' => $defender->avatar_url,
                'defender_level' => $defender->level,
                'won' => false,
                'stolen_cash' => 0,
                'claimed_bounty' => 0,
                'defender_had_no_cash' => false,
                'damage' => $damage,
                'xp' => 0,
                'respect' => 0,
                'left_today' => max(0, $dailyLimit - $attacker->daily_attacks_count),
                'max_today' => $dailyLimit,
                'from_profile' => $request->boolean('from_profile'),
                'resolved' => false,
            ];
        }

        session(['pending_arena_battle' => $battleData]);
        session(['arena_battle' => $battleData]);

        if ($request->boolean('auto_resolve')) {
            $this->commitPendingBattle($attacker);
        }

        return redirect()->route('fight.arena', $defender);
    }
}

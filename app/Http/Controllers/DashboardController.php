<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterMission;
use App\Models\EscapeTunnel;
use App\Models\GameNotification;
use App\Models\Item;
use App\Models\LotteryRound;
use App\Models\Mission;
use App\Models\NewsArticle;
use App\Models\TheftRecord;
use App\Models\TrainingLog;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\AllowanceService;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\EventVaultCrackingService;
use App\Services\LevelService;
use App\Services\NuNlNewsService;
use App\Services\ReferralService;
use App\Services\RookieService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Permanent unlock prices for premium dashboard widgets in stars.
     *
     * @var array<string, int>
     */
    public const WIDGET_PRICES = [
        'weather' => 15,
        'nunl' => 15,
        'radio' => 20,
        'tunnel' => 20,
        'treatment' => 20,
        'packages' => 20,
    ];

    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
        protected AllowanceService $allowanceService,
        protected ReferralService $referralService,
        protected NuNlNewsService $nuNlNewsService,
        protected WeatherService $weatherService,
        protected EconomyService $economyService,
        protected CooldownService $cooldownService,
        protected RookieService $rookieService,
    ) {}

    /**
     * Show player dashboard.
     */
    public function index(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return redirect()->route('login');
        }

        // Refresh energy/health timestamps
        $this->energyService->refresh($character);

        $allowanceDetails = $this->allowanceService->getAllowanceDetails($character);

        $nextLevelXp = $this->levelService->getXpRequiredForNextLevel($character->level);
        $xpPercentage = min(100, max(0, (int) round(($character->xp / max(1, $nextLevelXp)) * 100)));

        // Actieve en relevante missies voor het karakter
        $completedMissionCounts = $character->missions()->pluck('completed_count', 'mission_id')->toArray();
        $completedMissionIds = array_keys(array_filter($completedMissionCounts, fn ($c) => $c > 0));

        // 1. Eerst nog niet voltooide missies voor het huidige level
        $activeMissions = Mission::where('required_level', '<=', $character->level)
            ->whereNotIn('id', $completedMissionIds)
            ->orderBy('required_level')
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        // 2. Indien minder dan 3, aanvullen met eerstvolgende of recent voltooide
        if ($activeMissions->count() < 3) {
            $needed = 3 - $activeMissions->count();
            $nextMissions = Mission::where('required_level', '>', $character->level)
                ->orderBy('required_level')
                ->orderBy('sort_order')
                ->take($needed)
                ->get();

            $quickMissions = $activeMissions->concat($nextMissions);

            if ($quickMissions->count() < 3) {
                $recentlyCompleted = Mission::whereIn('id', $completedMissionIds)
                    ->orderByDesc('required_level')
                    ->take(3 - $quickMissions->count())
                    ->get();
                $quickMissions = $quickMissions->concat($recentlyCompleted);
            }
        } else {
            $quickMissions = $activeMissions;
        }

        if (EventVaultCrackingService::hasEnded() && ! GameNotification::where('type', EventVaultCrackingService::NOTIFICATION_TYPE)->exists()) {
            try {
                EventVaultCrackingService::resolveWinner();
            } catch (\Throwable) {
                // Ignore event resolution errors during dashboard load
            }
        }

        $notifications = $character->notifications()->take(5)->get();
        $recentGuestbook = $character->guestbookEntries()->with('author')->take(3)->get();

        $latestNews = NewsArticle::published()
            ->orderByDesc('is_pinned')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        // Dynamische Recente Activiteiten samenstellen
        $activities = collect();

        // 1. Voltooide missies
        $missions = CharacterMission::where('character_id', $character->id)
            ->where('completed_count', '>', 0)
            ->with('mission')
            ->latest('last_attempted_at')
            ->take(5)
            ->get();

        foreach ($missions as $m) {
            if ($m->mission) {
                $activities->push([
                    'title' => "Je hebt de missie '{$m->mission->title}' voltooid!",
                    'badge' => '+ €'.number_format($m->mission->min_cash_reward, 0, ',', '.')."  ·  + {$m->mission->xp_reward} XP",
                    'badge_color' => 'text-emerald-600 dark:text-emerald-400',
                    'icon' => 'fa-check',
                    'icon_bg' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400',
                    'time' => $m->last_attempted_at ?: $m->created_at,
                ]);
            }
        }

        // 2. Krachttraining & Vechtoefening logs
        $trainings = TrainingLog::where('character_id', $character->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($trainings as $t) {
            $activities->push([
                'title' => "Je hebt {$t->energy_cost} energie verbruikt bij training van ".ucfirst($t->stat).'.',
                'badge' => "+ {$t->stat_gain} ".ucfirst($t->stat).'  ·  - €'.number_format($t->cash_cost, 0, ',', '.'),
                'badge_color' => 'text-purple-600 dark:text-purple-400',
                'icon' => 'fa-dumbbell',
                'icon_bg' => 'bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400',
                'time' => $t->created_at,
            ]);
        }

        // 3. Diefstallen en overvallen
        $thefts = TheftRecord::where('thief_id', $character->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($thefts as $th) {
            $activities->push([
                'title' => $th->log_message ?: ($th->success ? "Buit binnengehaald bij {$th->target_name}!" : 'Overval mislukt.'),
                'badge' => $th->success
                    ? '+ €'.number_format($th->cash_stolen, 0, ',', '.')."  ·  + {$th->xp_gained} XP"
                    : 'Mislukt (0 buit)',
                'badge_color' => $th->success ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400',
                'icon' => $th->success ? 'fa-mask' : 'fa-handcuffs',
                'icon_bg' => $th->success ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400',
                'time' => $th->created_at,
            ]);
        }

        // 4. Notificaties en promoties (Level ups)
        $notifs = GameNotification::where('character_id', $character->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($notifs as $n) {
            $activities->push([
                'title' => $n->title,
                'badge' => Str::limit($n->message, 80),
                'badge_color' => 'text-indigo-600 dark:text-indigo-400',
                'icon' => $n->icon ?: 'fa-trophy',
                'icon_bg' => 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400',
                'time' => $n->created_at,
            ]);
        }

        // Sorteer op recentste datum en neem de top 5
        $recentActivities = $activities->sortByDesc('time')->take(5)->values();

        $referralStats = $this->referralService->getStats($character);
        $lotteryRound = LotteryRound::current();

        $isLoginBonusActive = Character::isLoginBonusWindowActive();
        $hasClaimedStreakToday = $character->hasClaimedStreakToday();
        $secondsUntilLoginBonus = Character::secondsUntilNextLoginBonus();
        $secondsRemainingInLoginBonus = Character::secondsRemainingInLoginBonus();
        $nextStreakDay = $character->getNextStreakDay();

        $nuNlNews = $this->nuNlNewsService->getLatestNews(5);

        // Load custom GPS weather if user previously detected location (persisted in DB or session)
        $userLat = $character->weather_lat ?? session('user_weather_lat');
        $userLon = $character->weather_lon ?? session('user_weather_lon');
        $userLoc = $character->weather_location ?? null;

        $clinicWeather = ($userLat !== null && $userLon !== null)
            ? $this->weatherService->getWeather((float) $userLat, (float) $userLon, $userLoc)
            : $this->weatherService->getClinicWeather();

        // ⛏️ 1. Ontsnappingstunnel (Echte graafvoortgang, zandsokken & lepels)
        $tunnel = EscapeTunnel::firstOrCreate(['character_id' => $character->id], [
            'stage' => 1,
            'progress_pct' => 0,
            'sand_in_socks' => 0,
            'spoons_used' => 1,
            'wood_planks' => 0,
            'is_completed' => false,
        ]);
        $tunnelDigCooldown = $this->cooldownService->getRemainingSeconds($character, 'tunnel_dig');
        $tunnelDumpCooldown = $this->cooldownService->getRemainingSeconds($character, 'tunnel_dump_sand');
        $tunnelStages = EscapeTunnel::STAGES;
        $tunnelStageInfo = $tunnelStages[$tunnel->stage] ?? $tunnelStages[1];

        // 🛋️ 2. Behandeling & Verloftraject (Dossier, behandelscore & psychiater sessies)
        $treatmentScore = (int) ($character->treatment_score ?? 50);
        $isOnLeave = $character->leave_until && $character->leave_until->isFuture();
        $leaveRemainingMinutes = $isOnLeave ? (int) ceil(now()->diffInSeconds($character->leave_until) / 60) : 0;
        $treatmentCooldown = $this->cooldownService->getRemainingSeconds($character, 'treatment_session');

        // 📦 3. Koffers & Kluiscodes (Stats, kluiscodes, tassen, xxl koffers & kluisjes)
        $unopenedBags = (int) ($character->unopened_bags ?? 0);
        $xxlCases = (int) ($character->xxl_cases ?? 0);
        $vaultTokens = (int) ($character->vault_tokens ?? 0);
        $packageCooldown = $this->cooldownService->getRemainingSeconds($character, 'package');
        $lockerCooldown = $this->cooldownService->getRemainingSeconds($character, 'locker');
        $vaultCodeStats = VaultCode::where('character_id', $character->id)
            ->selectRaw('count(case when is_redeemed = 0 then 1 end) as active_count, count(case when is_redeemed = 1 then 1 end) as redeemed_count')
            ->first();
        $activeVaultCodes = (int) ($vaultCodeStats->active_count ?? 0);
        $redeemedVaultCodes = (int) ($vaultCodeStats->redeemed_count ?? 0);

        $unlockedWidgets = $character->unlocked_widgets ?? [];
        if (in_array('peuken_market', $unlockedWidgets, true) && ! in_array('tunnel', $unlockedWidgets, true)) {
            $unlockedWidgets[] = 'tunnel';
        }
        if (in_array('security', $unlockedWidgets, true) && ! in_array('treatment', $unlockedWidgets, true)) {
            $unlockedWidgets[] = 'treatment';
        }
        if (in_array('roulette', $unlockedWidgets, true) && ! in_array('packages', $unlockedWidgets, true)) {
            $unlockedWidgets[] = 'packages';
        }
        $widgetPrices = self::WIDGET_PRICES;
        $rookieData = $this->rookieService->getQuests($character);

        return view('game.dashboard', compact(
            'character',
            'rookieData',
            'nextLevelXp',
            'xpPercentage',
            'quickMissions',
            'completedMissionCounts',
            'notifications',
            'recentGuestbook',
            'allowanceDetails',
            'latestNews',
            'recentActivities',
            'referralStats',
            'lotteryRound',
            'isLoginBonusActive',
            'hasClaimedStreakToday',
            'secondsUntilLoginBonus',
            'secondsRemainingInLoginBonus',
            'nextStreakDay',
            'nuNlNews',
            'clinicWeather',
            'tunnel',
            'tunnelDigCooldown',
            'tunnelDumpCooldown',
            'tunnelStageInfo',
            'treatmentScore',
            'isOnLeave',
            'leaveRemainingMinutes',
            'treatmentCooldown',
            'unopenedBags',
            'xxlCases',
            'vaultTokens',
            'packageCooldown',
            'lockerCooldown',
            'activeVaultCodes',
            'redeemedVaultCodes',
            'unlockedWidgets',
            'widgetPrices',
        ));
    }

    /**
     * Fetch real-time weather by browser GPS coordinates or city name.
     */
    public function weatherByCoords(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        // Reset to default clinic location
        if ($request->boolean('reset') || $request->input('city') === 'vught_reset') {
            session()->forget(['user_weather_lat', 'user_weather_lon']);
            if ($character) {
                $character->update([
                    'weather_location' => null,
                    'weather_lat' => null,
                    'weather_lon' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'weather' => $this->weatherService->getClinicWeather(),
            ]);
        }

        $city = $request->input('city');
        if (! empty($city) && is_string($city)) {
            $weather = $this->weatherService->getWeatherByCity($city);
            $lat = $weather['latitude'] ?? null;
            $lon = $weather['longitude'] ?? null;
            $resolvedLoc = $weather['location'] ?? $city;

            if ($lat !== null && $lon !== null) {
                session([
                    'user_weather_lat' => $lat,
                    'user_weather_lon' => $lon,
                ]);

                if ($character) {
                    $character->update([
                        'weather_location' => $resolvedLoc,
                        'weather_lat' => $lat,
                        'weather_lon' => $lon,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'weather' => $weather,
            ]);
        }

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'location' => 'nullable|string|max:100',
        ]);

        $lat = (float) $validated['lat'];
        $lon = (float) $validated['lon'];
        $customLoc = $validated['location'] ?? null;

        session([
            'user_weather_lat' => $lat,
            'user_weather_lon' => $lon,
        ]);

        $weather = $this->weatherService->getWeather($lat, $lon, $customLoc);

        if ($character) {
            $character->update([
                'weather_location' => $weather['location'] ?? $customLoc,
                'weather_lat' => $lat,
                'weather_lon' => $lon,
            ]);
        }

        return response()->json([
            'success' => true,
            'weather' => $weather,
        ]);
    }

    /**
     * Unlock a premium widget permanently with character stars.
     */
    public function unlockWidget(Request $request): JsonResponse|RedirectResponse
    {
        $key = (string) ($request->input('widget_key') ?? '');

        if (empty($key) || ! array_key_exists($key, self::WIDGET_PRICES)) {
            if (! $request->expectsJson() && ! $request->ajax()) {
                return back()->with('error', 'Ongeldige of niet-gespecificeerde widget.');
            }

            return response()->json(['error' => 'Ongeldige widget.'], 422);
        }

        $cost = self::WIDGET_PRICES[$key];

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            if (! $request->expectsJson() && ! $request->ajax()) {
                return back()->with('error', 'Geen personage gevonden.');
            }

            return response()->json(['error' => 'Geen karakter gevonden.'], 404);
        }

        return DB::transaction(function () use ($character, $key, $cost, $request) {
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            $unlocked = $lockedCharacter->unlocked_widgets;
            if (is_string($unlocked)) {
                $unlocked = json_decode($unlocked, true) ?: [];
            }
            if (! is_array($unlocked)) {
                $unlocked = [];
            }

            // Use the alias-aware check so a widget bought under its legacy key is not charged a second time.
            if (in_array($key, $unlocked, true) || $lockedCharacter->isWidgetUnlocked($key)) {
                if (! $request->expectsJson() && ! $request->ajax()) {
                    return back()->with('info', 'Deze widget is al ontgrendeld!');
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Deze widget is al ontgrendeld!',
                    'stars' => (int) $lockedCharacter->stars,
                    'unlocked_widgets' => $unlocked,
                    'widget_key' => $key,
                ]);
            }

            if (($lockedCharacter->stars ?? 0) < $cost) {
                $errMsg = "Niet genoeg sterren! Deze widget kost {$cost} sterren (je hebt er {$lockedCharacter->stars}).";
                if (! $request->expectsJson() && ! $request->ajax()) {
                    return back()->with('error', $errMsg);
                }

                return response()->json(['error' => $errMsg], 400);
            }

            $lockedCharacter->stars = max(0, (int) $lockedCharacter->stars - $cost);
            $unlocked[] = $key;
            $lockedCharacter->unlocked_widgets = array_values(array_unique($unlocked));
            $lockedCharacter->save();

            $successMsg = "Widget succesvol ontgrendeld voor {$cost} sterren! ⭐";
            if (! $request->expectsJson() && ! $request->ajax()) {
                return back()->with('success', $successMsg);
            }

            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'stars' => (int) $lockedCharacter->stars,
                'unlocked_widgets' => $lockedCharacter->unlocked_widgets,
                'widget_key' => $key,
            ]);
        });
    }

    /**
     * Get real-time stats for the authenticated player.
     */
    public function realtimeStats(): JsonResponse
    {
        // Prevent background AJAX polling from consuming flash messages (like 'success' / 'error')
        if (request()->hasSession()) {
            request()->session()->reflash();
        }

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['error' => 'Geen karakter gevonden.'], 404);
        }

        $cacheKey = "char_realtime_stats_{$character->id}";
        $data = Cache::remember($cacheKey, 3, function () use ($character) {
            $this->energyService->refresh($character);

            // remainingPrisonSeconds() releases an expired sentence, so read it before the prison flag.
            $prisonSeconds = (int) $character->remainingPrisonSeconds();
            $isInPrison = $prisonSeconds > 0 && (bool) $character->is_in_prison;

            $nextLevelXp = $this->levelService->getXpRequiredForNextLevel($character->level);
            $xpPercentage = min(100, max(0, (int) round(($character->xp / max(1, $nextLevelXp)) * 100)));

            return [
                'cash' => (int) $character->cash,
                'bank' => (int) $character->bank,
                'total' => (int) ($character->cash + $character->bank),
                'total_money' => (int) ($character->cash + $character->bank),
                'cash_formatted' => '€'.number_format($character->cash, 0, ',', '.'),
                'cashFormatted' => '€'.number_format($character->cash, 0, ',', '.'),
                'bank_formatted' => '€'.number_format($character->bank, 0, ',', '.'),
                'bankFormatted' => '€'.number_format($character->bank, 0, ',', '.'),
                'total_formatted' => '€'.number_format($character->cash + $character->bank, 0, ',', '.'),
                'totalFormatted' => '€'.number_format($character->cash + $character->bank, 0, ',', '.'),
                'energy' => (int) $character->energy,
                'max_energy' => (int) $character->max_energy,
                'energy_percent' => min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))),
                'health' => (int) $character->health,
                'max_health' => (int) $character->max_health,
                'health_percent' => min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))),
                'stars' => (int) ($character->stars ?? 0),
                'stars_formatted' => number_format($character->stars ?? 0, 0, ',', '.'),
                'starsFormatted' => number_format($character->stars ?? 0, 0, ',', '.'),
                'vip_points' => (int) ($character->vip_points ?? 0),
                'vip_points_formatted' => number_format($character->vip_points ?? 0, 0, ',', '.'),
                'vipPointsFormatted' => number_format($character->vip_points ?? 0, 0, ',', '.'),
                'respect' => (string) $character->formatted_respect,
                'formatted_respect' => (string) $character->formatted_respect,
                'level' => (int) $character->level,
                'xp' => (int) $character->xp,
                'next_level_xp' => (int) $nextLevelXp,
                'xpFormatted' => number_format($character->xp, 0, ',', '.').' / '.number_format($nextLevelXp, 0, ',', '.').' XP',
                'xp_percentage' => $xpPercentage,
                'onlineCount' => Character::getOnlineCount(),
                'is_in_prison' => $isInPrison,
                'prison_seconds' => $prisonSeconds,
            ];
        });

        // Check for urgent unread notifications (e.g. attacked/robbed on Luchtplaats, prison, hitman)
        $unreadAlerts = GameNotification::where('character_id', $character->id)
            ->where('is_read', false)
            ->whereIn('type', ['theft', 'bounty_claimed', 'prison', 'hitman_hit', 'attack'])
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('id')
            ->get();

        if ($unreadAlerts->isNotEmpty()) {
            $latestAlert = $unreadAlerts->first();
            $timeStr = $latestAlert->created_at ? $latestAlert->created_at->format('H:i') : '';
            $message = (string) $latestAlert->message;

            if ($timeStr && ! str_contains($message, 'om ')) {
                $message .= " (om {$timeStr} uur)";
            }

            $count = $unreadAlerts->count();
            if ($count > 1) {
                $message .= ' — (+'.($count - 1).' eerdere meldingen)';
            }

            $data['alert'] = [
                'id' => (int) $latestAlert->id,
                'title' => (string) $latestAlert->title,
                'message' => $message,
                'icon' => (string) $latestAlert->icon,
                'type' => match ($latestAlert->type) {
                    'theft', 'hitman_hit', 'attack' => 'danger',
                    'prison' => 'warning',
                    'bounty_claimed' => 'warning',
                    default => 'info',
                },
            ];

            // Mark all pending alerts as read so they don't trickle in 1-by-1 every 10 seconds causing confusion
            GameNotification::whereIn('id', $unreadAlerts->pluck('id'))->update(['is_read' => true]);
        }

        $data['unread_notifications'] = $character->notifications()->where('is_read', false)->count();

        return response()->json($data);
    }

    /**
     * Sell cigarettes directly from the Peuken Beurs widget.
     */
    public function sellPeuken(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['error' => 'Geen personage gevonden.'], 404);
        }

        if ($character->isImprisoned()) {
            $msg = 'In de isoleercel heb je geen toegang tot de Peuken Beurs!';

            return $request->expectsJson() || $request->ajax()
                ? response()->json(['error' => $msg], 403)
                : back()->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->expectsJson() || $request->ajax()
                ? response()->json(['error' => $msg], 403)
                : back()->with('error', $msg);
        }

        // The Peuken Beurs is a paid widget (legacy key peuken_market, now part of the tunnel widget).
        if (! $character->isWidgetUnlocked('tunnel')) {
            $msg = 'Ontgrendel eerst de Peuken Beurs widget op je dashboard!';

            return $request->expectsJson() || $request->ajax()
                ? response()->json(['error' => $msg], 403)
                : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'amount' => ['required', 'string', 'in:5,10,all'],
        ]);

        $sigItem = Item::where('slug', 'sigaretten')->first();
        if (! $sigItem) {
            return response()->json(['error' => 'Sigaretten item niet geconfigureerd.'], 500);
        }

        $hour = (int) now()->format('H');
        $wave = (sin($hour * 0.52) * 11) + (cos($hour * 0.3) * 5);
        $peukPrice = max(28, (int) round(42 + $wave));

        $requestedAmount = $validated['amount'];

        $soldData = DB::transaction(function () use ($character, $sigItem, $requestedAmount, $peukPrice) {
            $charItem = CharacterItem::where('character_id', $character->id)
                ->where('item_id', $sigItem->id)
                ->where('quantity', '>', 0)
                ->lockForUpdate()
                ->first();

            $currentStock = $charItem ? (int) $charItem->quantity : 0;
            $toSell = $requestedAmount === 'all' ? $currentStock : (int) $requestedAmount;

            if ($toSell <= 0 || $currentStock < $toSell) {
                return ['error' => "Je hebt minimaal {$toSell} peuken nodig om te verkopen (je hebt er {$currentStock})!"];
            }

            $totalCash = $toSell * $peukPrice;
            $xpGain = max(10, (int) round($toSell * 3.5));
            $respectGain = max(1, (int) floor($toSell / 5));

            $charItem->decrement('quantity', $toSell);
            if ($charItem->fresh()?->quantity <= 0) {
                $charItem->delete();
            }

            $this->economyService->addCash($character, $totalCash);
            $this->levelService->addXp($character, $xpGain);
            $character->addRespect($respectGain);
            $character->save();

            return [
                'toSell' => $toSell,
                'currentStock' => $currentStock,
                'totalCash' => $totalCash,
                'xpGain' => $xpGain,
                'respectGain' => $respectGain,
            ];
        });

        if (isset($soldData['error'])) {
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['error' => $soldData['error']], 400)
                : back()->with('error', $soldData['error']);
        }

        $toSell = $soldData['toSell'];
        $currentStock = $soldData['currentStock'];
        $totalCash = $soldData['totalCash'];
        $xpGain = $soldData['xpGain'];
        $respectGain = $soldData['respectGain'];

        if (class_exists(DailyBattleService::class)) {
            app(DailyBattleService::class)->record($character, 'drugs_sold', $toSell);
        }

        $msg = "🚬 DEAL GESLOTEN! {$toSell}x Peuken verkocht op de zwarte markt voor €".number_format($totalCash, 0, ',', '.')." (+{$xpGain} XP, +{$respectGain} Respect)!";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'cash' => (int) $character->cash,
                'cash_formatted' => '€'.number_format($character->cash, 0, ',', '.'),
                'peuken_left' => max(0, $currentStock - $toSell),
                'cash_gained' => $totalCash,
                'xp_gained' => $xpGain,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Perform an active blind-spot radar sweep to uncover contraband/cash.
     */
    public function radarSweep(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['error' => 'Geen personage gevonden.'], 404);
        }

        // New purchases store the key 'treatment'; isWidgetUnlocked() maps it to the legacy key 'security'.
        if (! $character->isWidgetUnlocked('treatment')) {
            $msg = 'Ontgrendel eerst de Bewakingsradar widget voor 20 sterren!';

            return $request->expectsJson() || $request->ajax()
                ? response()->json(['error' => $msg], 403)
                : back()->with('error', $msg);
        }

        if ($character->isImprisoned()) {
            $msg = 'Je zit in de isoleercel en kunt de bewakingsradar niet bedienen!';

            return $request->expectsJson() || $request->ajax()
                ? response()->json(['error' => $msg], 403)
                : back()->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->expectsJson() || $request->ajax()
                ? response()->json(['error' => $msg], 403)
                : back()->with('error', $msg);
        }

        $sweepResult = DB::transaction(function () use ($character) {
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked) {
                return ['error' => 'Geen personage gevonden.', 'code' => 404];
            }

            if ($this->cooldownService->isOnCooldown($locked, 'radar_sweep')) {
                $rem = $this->cooldownService->getRemainingSeconds($locked, 'radar_sweep');
                $mins = ceil($rem / 60);

                return [
                    'error' => "⏳ Radar scanner herlaadt zijn frequenties. Wacht nog {$rem}s (~{$mins} min)!",
                    'code' => 400,
                    'rem' => $rem,
                ];
            }

            // Radar sweep success!
            $cashFound = mt_rand(180, 480);
            $peukenFound = mt_rand(1, 3);
            $xpGain = 30;

            $this->cooldownService->setCooldown($locked, 'radar_sweep', 1800);

            $this->economyService->addCash($locked, $cashFound);
            $this->levelService->addXp($locked, $xpGain);
            $locked->addRespect(1);
            $locked->save();

            // Add cigarettes to stash
            $sigItem = Item::where('slug', 'sigaretten')->first();
            if ($sigItem) {
                $ci = CharacterItem::firstOrCreate(
                    ['character_id' => $locked->id, 'item_id' => $sigItem->id],
                    ['quantity' => 0]
                );
                $ci->increment('quantity', $peukenFound);
            }

            return [
                'cashFound' => $cashFound,
                'peukenFound' => $peukenFound,
                'xpGain' => $xpGain,
                'cash' => (int) $locked->cash,
            ];
        });

        if (isset($sweepResult['error'])) {
            $rem = $sweepResult['rem'] ?? null;
            $data = ['error' => $sweepResult['error']];
            if ($rem !== null) {
                $data['remaining_seconds'] = $rem;
            }

            return $request->expectsJson() || $request->ajax()
                ? response()->json($data, $sweepResult['code'])
                : back()->with('error', $sweepResult['error']);
        }

        $cashFound = $sweepResult['cashFound'];
        $peukenFound = $sweepResult['peukenFound'];
        $xpGain = $sweepResult['xpGain'];
        $currentCash = $sweepResult['cash'];

        $msg = '📡 RADAR SWEEP SUCCES! Dode hoek gescand bij container 4. Buit: €'.number_format($cashFound, 0, ',', '.')." contant en {$peukenFound}x Sigaretten meegesmokkeld (+{$xpGain} XP)!";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'cash' => $currentCash,
                'cash_formatted' => '€'.number_format($currentCash, 0, ',', '.'),
                'cash_found' => $cashFound,
                'peuken_found' => $peukenFound,
                'cooldown_seconds' => 1800,
            ]);
        }

        return back()->with('success', $msg);
    }
}

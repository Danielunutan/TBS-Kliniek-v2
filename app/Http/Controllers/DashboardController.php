<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterMission;
use App\Models\GameNotification;
use App\Models\LotteryRound;
use App\Models\Mission;
use App\Models\NewsArticle;
use App\Models\TheftRecord;
use App\Models\TrainingLog;
use App\Models\User;
use App\Services\AllowanceService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\NuNlNewsService;
use App\Services\ReferralService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        'peuken_market' => 20,
        'roulette' => 25,
        'security' => 20,
    ];

    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
        protected AllowanceService $allowanceService,
        protected ReferralService $referralService,
        protected NuNlNewsService $nuNlNewsService,
        protected WeatherService $weatherService,
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

        $notifications = $character->notifications()->take(5)->get();
        $recentGuestbook = $character->guestbookEntries()->with('author')->take(3)->get();

        $latestNews = NewsArticle::where('is_published', true)
            ->orderByDesc('is_pinned')
            ->latest()
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
                    'badge_color' => 'text-emerald-600',
                    'icon' => 'fa-check',
                    'icon_bg' => 'bg-emerald-100 text-emerald-600',
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
                'badge_color' => 'text-purple-600',
                'icon' => 'fa-dumbbell',
                'icon_bg' => 'bg-purple-100 text-purple-600',
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
                'badge_color' => $th->success ? 'text-amber-600' : 'text-rose-600',
                'icon' => $th->success ? 'fa-mask' : 'fa-handcuffs',
                'icon_bg' => $th->success ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600',
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
                'badge_color' => 'text-indigo-600',
                'icon' => $n->icon ?: 'fa-trophy',
                'icon_bg' => 'bg-indigo-100 text-indigo-600',
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

        // Load custom GPS weather if user previously detected location
        $sessionLat = session('user_weather_lat');
        $sessionLon = session('user_weather_lon');
        $clinicWeather = ($sessionLat !== null && $sessionLon !== null)
            ? $this->weatherService->getWeather((float) $sessionLat, (float) $sessionLon)
            : $this->weatherService->getClinicWeather();

        // 🚬 Peuken Beurs (Zwarte markt koers index)
        $peukenMarket = [
            'status' => 'Schaarste op Afdeling B (Prijzen stijgen!)',
            'status_type' => 'bullish',
            'items' => [
                [
                    'name' => 'Van Nelle Zware Shag (50g)',
                    'price' => '€ 15,20',
                    'change' => '+14.2%',
                    'is_up' => true,
                    'tip' => 'Zeer gewild op de luchtplaats',
                    'icon' => 'fa-smoking text-amber-500',
                ],
                [
                    'name' => 'Slof Marlboro Smokkel (200 stuks)',
                    'price' => '€ 88,50',
                    'change' => '-4.1%',
                    'is_up' => false,
                    'tip' => 'Aanvoer via drone geslaagd',
                    'icon' => 'fa-box-archive text-rose-500',
                ],
                [
                    'name' => 'Mascotte / Rizla Vloeitjes',
                    'price' => '€ 2,60',
                    'change' => '+8.5%',
                    'is_up' => true,
                    'tip' => 'Kantine voorraad is uitgeput',
                    'icon' => 'fa-scroll text-blue-400',
                ],
                [
                    'name' => 'Aansteker met Vuursteentje',
                    'price' => '€ 5,00',
                    'change' => '0.0%',
                    'is_up' => true,
                    'tip' => 'Vaste ruilwaarde tegen 2 peuken',
                    'icon' => 'fa-fire-flame-curved text-orange-500',
                ],
            ],
        ];

        // 🚨 Bewakingsradar & Patrouille Alarm
        $hour = (int) now()->format('H');
        $isHighAlert = $hour >= 22 || $hour < 6;
        $securityRadar = [
            'threat_level' => $isHighAlert ? 'Code Rood (Nachtlockdown)' : 'Code Geel (Regulier Toezicht)',
            'threat_color' => $isHighAlert ? 'rose' : 'amber',
            'guard_count' => $isHighAlert ? 14 : 8,
            'dog_patrol' => ($hour % 3 === 0) ? 'Actief op Binnenplaats' : 'In Kennel (Rustig)',
            'cell_check_risk' => min(95, max(12, (($character->respect ?? 50) % 35) + 15)),
            'camera_coverage' => '94% (Dode hoek bij container & linnenkamer)',
        ];

        $unlockedWidgets = $character->unlocked_widgets ?? [];
        $widgetPrices = self::WIDGET_PRICES;

        return view('game.dashboard', compact(
            'character',
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
            'peukenMarket',
            'securityRadar',
            'unlockedWidgets',
            'widgetPrices',
        ));
    }

    /**
     * Fetch real-time weather by browser GPS coordinates or city name.
     */
    public function weatherByCoords(Request $request): JsonResponse
    {
        $city = $request->input('city');
        if (! empty($city) && is_string($city)) {
            $weather = $this->weatherService->getWeatherByCity($city);
            session([
                'user_weather_lat' => $weather['latitude'] ?? null,
                'user_weather_lon' => $weather['longitude'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'weather' => $weather,
            ]);
        }

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $validated['lat'];
        $lon = (float) $validated['lon'];

        session([
            'user_weather_lat' => $lat,
            'user_weather_lon' => $lon,
        ]);

        $weather = $this->weatherService->getWeather($lat, $lon);

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

            if (in_array($key, $unlocked, true)) {
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

        $this->energyService->refresh($character);

        $nextLevelXp = $this->levelService->getXpRequiredForNextLevel($character->level);
        $xpPercentage = min(100, max(0, (int) round(($character->xp / max(1, $nextLevelXp)) * 100)));

        return response()->json([
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
            'is_in_prison' => (bool) $character->is_in_prison,
            'prison_seconds' => (int) $character->remainingPrisonSeconds(),
        ]);
    }
}

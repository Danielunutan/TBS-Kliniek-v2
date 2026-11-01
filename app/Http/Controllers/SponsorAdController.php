<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\CharacterCooldown;
use App\Models\CharacterMemoryStat;
use App\Models\CharacterTrueCrimeState;
use App\Models\GameMessage;
use App\Models\LotteryRound;
use App\Models\LotteryTicket;
use App\Models\VaultCode;
use App\Services\ChatBotService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SponsorAdController extends Controller
{
    public const ADS_ENABLED = true;

    public static function areAdsEnabled(): bool
    {
        return (bool) config('services.robinads.enabled', self::ADS_ENABLED);
    }

    public const DEFAULT_SMARTLINK = null;

    public const DAILY_LIMIT = 500;

    public const WATCH_DURATION_SECONDS = 15;

    public const COOLDOWN_SECONDS = 0;

    /**
     * Map common aliases to canonical reward types.
     *
     * @var array<string, string>
     */
    public const REWARD_ALIASES = [
        'bullet' => 'bullets',
        'bullets' => 'bullets',
        'bullets_50' => 'bullets',
        'kogels' => 'bullets',
        'kogel' => 'bullets',
        'vest' => 'vest',
        'vest_12h' => 'vest',
        'kogelvest' => 'vest',
        'kogel_vest' => 'vest',
        'armor' => 'vest',
        'armour' => 'vest',
        'coma' => 'coma_revive',
        'ic' => 'coma_revive',
        'intensive_care' => 'coma_revive',
        'coma_revive' => 'coma_revive',
        'revive' => 'coma_revive',
        'star' => 'daily_stars',
        'stars' => 'daily_stars',
        'cooldown_true_crime' => 'true_crime',
        'cooldown:true_crime' => 'true_crime',
        'cooldown_connect_four' => 'cooldown_connect_four',
        'cooldown:connect_four' => 'cooldown_connect_four',
        'cooldown_memory' => 'cooldown_memory',
        'cooldown:memory' => 'cooldown_memory',
    ];

    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $levelService,
    ) {}

    /**
     * Normalize reward type.
     */
    public function normalizeRewardType(?string $rawType): string
    {
        $raw = strtolower(trim((string) $rawType));
        if (isset(self::REWARD_ALIASES[$raw])) {
            return self::REWARD_ALIASES[$raw];
        }

        $validTypes = [
            'cash', 'bullets', 'vest', 'coma_revive', 'prison', 'mask',
            'respect', 'vault_code', 'cooldown', 'daily_xp', 'daily_cash',
            'daily_stars', 'lottery_ticket', 'true_crime',
            'cooldown_connect_four', 'cooldown_memory',
        ];
        if (str_starts_with($raw, 'cooldown:')) {
            return $raw;
        }

        return in_array($raw, $validTypes, true) ? $raw : 'cash';
    }

    /**
     * Show the Sponsorkamer dashboard.
     */
    public function index(): View|RedirectResponse
    {
        if (! self::areAdsEnabled()) {
            return redirect()->route('shop.index')->with('info', 'De sponsorkamer en advertenties zijn vervangen door de nieuwe TBS Donaties & VIP Winkel!');
        }

        /** @var Character $character */
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $adsToday = $character->getEffectiveAdsWatchedToday();
        $adsLeftToday = $character->getRemainingAdsToday();
        $cooldown = $character->getRemainingAdCooldownSeconds();
        $totalAds = $character->ads_watched_total;
        $vipProgress = $character->getVipAdMilestoneProgress();
        $adsUntilVip = $character->getAdsUntilNextVip();
        $adDailyData = $character->getAdDailyData();

        $smartlinkUrl = route('sponsor.redirect');

        return view('game.sponsor.index', compact(
            'character',
            'adsToday',
            'adsLeftToday',
            'cooldown',
            'totalAds',
            'vipProgress',
            'adsUntilVip',
            'adDailyData',
            'smartlinkUrl'
        ));
    }

    /**
     * Initialize an ad watch session and return session token + smartlink URL.
     */
    public function start(Request $request): JsonResponse
    {
        if (! self::areAdsEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Advertenties zijn momenteel tijdelijk uitgeschakeld.',
            ], 403);
        }

        /** @var Character $character */
        $character = Auth::user()->character;

        if ($character->getRemainingAdsToday() <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Je hebt vandaag je maximale limiet van '.self::DAILY_LIMIT.' advertenties bereikt! Morgen om 00:00 uur reset de teller.',
            ], 422);
        }

        $rawType = $request->input('reward_type') ?: $request->input('type');
        $rewardType = $this->normalizeRewardType($rawType);

        $dailyData = $character->getAdDailyData();

        if ($rewardType === 'coma_revive') {
            if (! $character->isComatose() && ! $character->needsReviveSerum()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je ligt momenteel niet in de Intensive Care Coma.',
                ], 422);
            }
        } elseif ($character->isComatose() || $character->needsReviveSerum()) {
            return response()->json([
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Gebruik eerst de IC Reanimatie of een '.Character::REVIVE_SERUM_NAME.'.',
            ], 422);
        } elseif ($rewardType === 'prison') {
            if (! $character->isImprisoned()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je zit momenteel niet vast in de isoleercel.',
                ], 422);
            }
        } elseif ($rewardType === 'daily_xp') {
            if (! empty($dailyData['xp_claimed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt de dagelijkse +50 XP bonus voor vandaag al geclaimd! Morgen om 00:00 uur staat er een nieuwe klaar.',
                ], 422);
            }
        } elseif ($rewardType === 'daily_cash') {
            if (! empty($dailyData['cash_claimed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt de dagelijkse € 50.000 cash bonus voor vandaag al geclaimd! Morgen om 00:00 uur staat er een nieuwe klaar.',
                ], 422);
            }
        } elseif ($rewardType === 'daily_stars') {
            if (! empty($dailyData['stars_claimed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt de dagelijkse 5 Sterren bonus voor vandaag al geclaimd! Morgen om 00:00 uur staat er een nieuwe klaar.',
                ], 422);
            }
        }

        // Store anti-abuse verification timestamp in session
        $token = Str::random(32);
        $popupUrl = null;
        $robinRewardId = null;

        $apiKey = config('services.robinads.api_key', 'rbads_live_7c596edc4ee5a05b9ca3a7e17a060143aa5c2a67');
        $websiteId = config('services.robinads.website_id', '6ad2b8e95f70fee0a42a5496');

        if ($apiKey && $websiteId) {
            try {
                $response = Http::withoutVerifying()->withToken($apiKey)->timeout(5)->post('https://robinsads.com/api/v1/rewards', [
                    'website_id' => $websiteId,
                    'metadata' => [
                        'character_id' => (string) $character->id,
                        'reward_type' => $rewardType,
                        'token' => $token,
                    ],
                ]);

                if ($response->successful()) {
                    $popupUrl = $response->json('popup_url');
                    $robinRewardId = $response->json('id');
                }
            } catch (\Throwable $e) {
                // Silently fallback if network times out
            }
        }

        $adData = [
            'token' => $token,
            'started_at' => now()->timestamp,
            'character_id' => $character->id,
            'reward_type' => $rewardType,
            'robin_reward_id' => $robinRewardId,
        ];

        // Store in Cache (resilient against session race conditions) and Session
        Cache::put("tbs_ad_token_{$token}", $adData, now()->addMinutes(30));
        Cache::put("tbs_ad_char_{$character->id}", $adData, now()->addMinutes(30));
        session()->put('tbs_ad_session', $adData);
        session()->save();

        $smartlinkUrl = $popupUrl ?? route('sponsor.index');

        return response()->json([
            'success' => true,
            'token' => $token,
            'character_id' => $character->id,
            'smartlink_url' => $smartlinkUrl,
            'popup_url' => $popupUrl,
            'ad_network' => 'robinads',
            'robin_reward_id' => $robinRewardId,
            'duration' => self::WATCH_DURATION_SECONDS,
            'message' => 'Sponsoradvertentie gestart. Bekijk de advertentie en ontvang je beloning!',
        ]);
    }

    /**
     * Safe internal redirect to sponsor dashboard (Monetag completely disabled).
     */
    public function redirect(Request $request): RedirectResponse
    {
        if (! self::areAdsEnabled()) {
            return redirect()->route('dashboard')->with('info', 'Sponsor-advertenties zijn momenteel tijdelijk uitgeschakeld.');
        }

        return redirect()->route('sponsor.index');
    }

    /**
     * Get player's current sponsor ad status.
     */
    public function status(Request $request): JsonResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;
        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Niet ingelogd.'], 401);
        }

        return response()->json([
            'success' => true,
            'ads_enabled' => self::areAdsEnabled(),
            'ads_today' => $character->getEffectiveAdsWatchedToday(),
            'ads_left_today' => $character->getRemainingAdsToday(),
            'ads_total' => $character->ads_watched_total,
            'cooldown' => $character->getRemainingAdCooldownSeconds(),
            'vip_progress' => $character->getVipAdMilestoneProgress(),
            'ads_until_vip' => $character->getAdsUntilNextVip(),
            'is_in_prison' => $character->isImprisoned(),
            'is_comatose' => $character->isComatose(),
            'needs_revive_serum' => $character->needsReviveSerum(),
            'has_vest' => $character->hasVest(),
            'vest_remaining_seconds' => $character->getVestRemainingSeconds(),
            'daily_data' => $character->getAdDailyData(),
        ]);
    }

    /**
     * Claim the reward after timer completion.
     *
     * A per-character lock rejects parallel claims, so one ad view can never pay out twice.
     */
    public function claim(Request $request): JsonResponse
    {
        if (! self::areAdsEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Advertenties zijn momenteel tijdelijk uitgeschakeld.',
            ], 403);
        }

        /** @var Character $character */
        $character = Auth::user()->character;

        $claimLock = Cache::lock("tbs_ad_claim_{$character->id}", 15);
        if (! $claimLock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Je vorige advertentie-claim wordt nog verwerkt. Probeer het over enkele seconden opnieuw.',
            ], 429);
        }

        try {
            return $this->processClaim($request, $character);
        } finally {
            $claimLock->release();
        }
    }

    /**
     * Verify the ad session and grant the reward for one finished advertisement.
     */
    protected function processClaim(Request $request, Character $character): JsonResponse
    {
        $explicitToken = $request->input('token');
        $requestToken = $explicitToken ?: $request->input('robin_token');

        // Resolve ad session: 1) from Cache by token, 2) from Cache by character id, 3) from Session
        $adSession = null;
        if ($requestToken) {
            $adSession = Cache::get("tbs_ad_token_{$requestToken}");
        }

        if (! $adSession) {
            $charSession = Cache::get("tbs_ad_char_{$character->id}");
            if ($charSession && is_array($charSession)) {
                if (! $explicitToken || ($charSession['token'] ?? null) === $explicitToken) {
                    $adSession = $charSession;
                }
            }
        }

        if (! $adSession) {
            $sessionAd = session()->get('tbs_ad_session');
            if ($sessionAd && is_array($sessionAd)) {
                if (! $explicitToken || ($sessionAd['token'] ?? null) === $explicitToken) {
                    $adSession = $sessionAd;
                }
            }
        }

        if (! $adSession || ! is_array($adSession)) {
            return response()->json([
                'success' => false,
                'message' => 'Geen actieve advertentiesessie gevonden. Start de sponsor opnieuw.',
            ], 422);
        }

        // Validate character matches
        if ($character->id !== ($adSession['character_id'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Sessie komt niet overeen met jouw karakter.',
            ], 403);
        }

        $apiKey = config('services.robinads.api_key', 'rbads_live_7c596edc4ee5a05b9ca3a7e17a060143aa5c2a67');
        $robinToken = $request->input('robin_token');
        $robinRewardId = $request->input('robin_reward_id') ?? ($adSession['robin_reward_id'] ?? null);

        // Verification logic:
        // 1. If Robins Ads granted completion token on client side (in-screen interstitial full watch), view is completed!
        $isVerified = false;

        if ($robinToken) {
            $isVerified = true;

            // Optional non-blocking verification ping to register impression on Robins Ads
            if ($apiKey && $robinRewardId) {
                try {
                    Http::withoutVerifying()->withToken($apiKey)->timeout(3)->post('https://robinsads.com/api/ads/reward/verify', [
                        'impressionId' => $robinRewardId,
                        'token' => $robinToken,
                    ]);
                } catch (\Throwable $e) {
                }
            }
        }

        // 2. If a Robins Ads reward ID exists, strictly verify with Robins Ads server whether the ad has been fully watched
        if (! $isVerified && $apiKey && $robinRewardId) {
            try {
                $statusRes = Http::withoutVerifying()->withToken($apiKey)->timeout(4)->get("https://robinsads.com/api/v1/rewards/{$robinRewardId}");
                if ($statusRes->successful() && $statusRes->json('status') === 'verified') {
                    $isVerified = true;
                }
            } catch (\Throwable $e) {
            }
        }

        // 3. Fallback & Timer verification (essential when players are on the same household IP, RobinsAds has no ad fill, or player watched in-screen)
        if (! $isVerified) {
            $startedAt = (int) ($adSession['started_at'] ?? 0);
            $elapsedSeconds = $startedAt > 0 ? (now()->timestamp - $startedAt) : 0;
            // Allow watch duration with a 3-second grace buffer (>= 12s) for client-server clock/network latency
            if ($elapsedSeconds >= (self::WATCH_DURATION_SECONDS - 3)) {
                $isVerified = true;
            }
        }

        if (! $isVerified) {
            return response()->json([
                'success' => false,
                'message' => 'De advertentie is nog niet volledig uitgekeken of bevestigd door de sponsor. Bekijk de video helemaal tot het einde om je beloning te claimen!',
            ], 422);
        }

        $rawRewardType = $adSession['reward_type'] ?? $request->input('reward_type') ?? $request->input('type');
        $rewardType = $this->normalizeRewardType($rawRewardType);

        return DB::transaction(function () use ($character, $adSession, $rewardType): JsonResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->firstOrFail();

            // Check daily limit inside locked transaction
            if ($lockedCharacter->getRemainingAdsToday() <= 0) {
                if (! empty($adSession['token'])) {
                    Cache::forget("tbs_ad_token_{$adSession['token']}");
                }
                Cache::forget("tbs_ad_char_{$lockedCharacter->id}");
                session()->forget('tbs_ad_session');

                return response()->json([
                    'success' => false,
                    'message' => 'Daglimiet van '.self::DAILY_LIMIT.' advertenties bereikt.',
                ], 422);
            }

            // Validate once-per-day reward caps before mutating ad counters
            $dailyData = $lockedCharacter->getAdDailyData();
            if ($rewardType === 'daily_xp' && ! empty($dailyData['xp_claimed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt de dagelijkse +50 XP bonus voor vandaag al geclaimd!',
                ], 422);
            }
            if ($rewardType === 'daily_cash' && ! empty($dailyData['cash_claimed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt de dagelijkse € 50.000 cash bonus voor vandaag al geclaimd!',
                ], 422);
            }
            if ($rewardType === 'daily_stars' && ! empty($dailyData['stars_claimed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt de dagelijkse 5 Sterren bonus voor vandaag al geclaimd!',
                ], 422);
            }

            // Clean up cache and session tokens to prevent replay / double claiming
            if (! empty($adSession['token'])) {
                Cache::forget("tbs_ad_token_{$adSession['token']}");
            }
            Cache::forget("tbs_ad_char_{$lockedCharacter->id}");
            session()->forget('tbs_ad_session');
            session()->save();

            // Update ad counters on locked character
            $todayStr = now()->toDateString();
            $adsToday = ($lockedCharacter->last_ad_date === $todayStr) ? ($lockedCharacter->ads_watched_today + 1) : 1;
            $totalAds = $lockedCharacter->ads_watched_total + 1;

            $lockedCharacter->ads_watched_today = $adsToday;
            $lockedCharacter->ads_watched_total = $totalAds;
            $lockedCharacter->last_ad_watched_at = now();
            $lockedCharacter->last_ad_date = $todayStr;

            $rewardDetails = '';

            // Process reward
            if (str_starts_with($rewardType, 'cooldown:') && $rewardType !== 'cooldown:true_crime') {
                $targetAction = substr($rewardType, 9);
                $actions = [$targetAction];
                if ($targetAction === 'produce_telefoon') {
                    $actions[] = 'produce_phone';
                } elseif ($targetAction === 'produce_phone') {
                    $actions[] = 'produce_telefoon';
                }

                CharacterCooldown::where('character_id', $lockedCharacter->id)
                    ->whereIn('action', $actions)
                    ->delete();

                $actionLabels = [
                    'produce_sigaretten' => 'Sigaretten produceren',
                    'produce_ritalin' => 'Ritalin onderscheppen',
                    'produce_telefoon' => 'Telefoon binnensmokkelen',
                    'produce_phone' => 'Telefoon binnensmokkelen',
                ];
                $label = $actionLabels[$targetAction] ?? $targetAction;
                $rewardDetails = "⚡ Cooldown voor {$label} is direct gewist!";
            } else {
                switch ($rewardType) {
                    case 'true_crime':
                    case 'cooldown:true_crime':
                        /** @var CharacterTrueCrimeState $state */
                        $state = CharacterTrueCrimeState::firstOrCreate(['character_id' => $lockedCharacter->id]);
                        $current = ($state->cooldown_ads_watched ?? 0) + 1;
                        if ($current >= 5) {
                            $state->update([
                                'cooldown_until' => null,
                                'cooldown_ads_watched' => 0,
                                'current_case_id' => null,
                            ]);
                            $rewardDetails = '🔍 [5/5 Ads] GEFELICITEERD: Je hebt 5 advertenties bekeken! Caroline heeft direct een nieuw dossier voor je ontgrendeld!';
                        } else {
                            $state->update([
                                'cooldown_ads_watched' => $current,
                            ]);
                            $left = 5 - $current;
                            $rewardDetails = "🔍 [{$current}/5 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' om Caroline\'s 24-uurs wachttijd direct te omzeilen.';
                        }
                        break;

                    case 'cooldown_connect_four':
                        $stat = CharacterConnectFourStat::firstOrCreate(['character_id' => $lockedCharacter->id]);
                        if ($stat->isOnCooldown() || ($stat->matches_played ?? 0) >= 5) {
                            $stat->update([
                                'cooldown_until' => null,
                                'matches_played' => 0,
                                'current_player_wins' => 0,
                                'current_arjan_wins' => 0,
                                'current_draws' => 0,
                            ]);
                        } else {
                            $stat->update([
                                'cooldown_until' => null,
                            ]);
                        }
                        $rewardDetails = '⚡ Sponsor-Ad bekeken! De OOHPA Arjan wachttijd is gereset. Je kunt direct een nieuw 5-potjes toernooi starten!';
                        break;

                    case 'cooldown_memory':
                        $stat = CharacterMemoryStat::firstOrCreate(['character_id' => $lockedCharacter->id]);
                        $stat->update([
                            'cooldown_until' => null,
                        ]);
                        $rewardDetails = '⚡ Sponsor-Ad bekeken! Je geheugen is opgefrist en de wachttijd is gereset. Je kunt direct weer spelen!';
                        break;

                    case 'respect':
                        $lockedCharacter->addRespect(1);
                        $rewardDetails = '🔥 +1 Respect verdiend! Je respect score is direct verhoogd!';
                        break;

                    case 'prison':
                        if ($lockedCharacter->isImprisoned()) {
                            $required = $lockedCharacter->getRequiredPrisonAds();
                            $current = ($lockedCharacter->ad_prison_progress ?? 0) + 1;

                            if ($current >= $required) {
                                $lockedCharacter->is_in_prison = false;
                                $lockedCharacter->prison_until = null;
                                $lockedCharacter->prison_reason = null;
                                $lockedCharacter->ad_prison_progress = 0;
                                $lockedCharacter->prison_escape_attempted = false;
                                $rewardDetails = "🎉 [{$current}/{$required} Ads] GEFELICITEERD: Je straftijd is volledig kwijtgescholden en je bent DIRECT VRIJGELATEN uit de isoleercel!";
                            } else {
                                $lockedCharacter->ad_prison_progress = $current;
                                // Reduce remaining prison time by 3 minutes (180s) per ad watched
                                if ($lockedCharacter->prison_until && now()->lt($lockedCharacter->prison_until)) {
                                    $remainingSec = max(0, (int) ceil(now()->diffInSeconds($lockedCharacter->prison_until, false)));
                                    $newSec = max(10, $remainingSec - 180);
                                    $lockedCharacter->prison_until = now()->addSeconds($newSec);
                                }
                                $left = $required - $current;
                                $rewardDetails = "⚖️ [{$current}/{$required} Ads] Voortgang opgeslagen! Je straftijd is met 3 minuten ingekort. Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' om direct vrij te komen.';
                            }
                        } else {
                            // Fallback to cash if not in prison
                            $lockedCharacter->ad_prison_progress = 0;
                            $lockedCharacter->save();
                            $this->economy->addCash($lockedCharacter, 2500, 'sponsor_ad');
                            $rewardDetails = '💵 Je zat niet vast, dus je hebt € 2.500 zakgeld ontvangen!';
                        }
                        break;

                    case 'daily_xp':
                        $current = ($dailyData['xp_progress'] ?? 0) + 1;
                        if ($current >= 3) {
                            $dailyData['xp_progress'] = 3;
                            $dailyData['xp_claimed'] = true;
                            $lockedCharacter->ad_daily_data = $dailyData;
                            $this->levelService->addXp($lockedCharacter, 50);
                            $rewardDetails = '⚡ [3/3 Ads] GEFELICITEERD: +50 XP ontvangen! Je dagelijkse XP beloning is geclaimd.';
                        } else {
                            $dailyData['xp_progress'] = $current;
                            $lockedCharacter->ad_daily_data = $dailyData;
                            $left = 3 - $current;
                            $rewardDetails = "⚡ [{$current}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw dagelijkse +50 XP bonus.';
                        }
                        break;

                    case 'daily_cash':
                        $current = ($dailyData['cash_progress'] ?? 0) + 1;
                        if ($current >= 3) {
                            $dailyData['cash_progress'] = 3;
                            $dailyData['cash_claimed'] = true;
                            $lockedCharacter->ad_daily_data = $dailyData;
                            $lockedCharacter->save();
                            $this->economy->addCash($lockedCharacter, 50000, 'sponsor_ad');
                            $rewardDetails = '💶 [3/3 Ads] GEFELICITEERD: € 50.000 contant geld ontvangen! Je dagelijkse cash beloning is geclaimd.';
                        } else {
                            $dailyData['cash_progress'] = $current;
                            $lockedCharacter->ad_daily_data = $dailyData;
                            $left = 3 - $current;
                            $rewardDetails = "💶 [{$current}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw dagelijkse € 50.000 bonus.';
                        }
                        break;

                    case 'daily_stars':
                        $current = ($dailyData['stars_progress'] ?? 0) + 1;
                        if ($current >= 2) {
                            $dailyData['stars_progress'] = 2;
                            $dailyData['stars_claimed'] = true;
                            $lockedCharacter->ad_daily_data = $dailyData;
                            $lockedCharacter->stars = ($lockedCharacter->stars ?? 0) + 5;
                            $rewardDetails = '⭐ [2/2 Ads] GEFELICITEERD: +5 Sterren toegevoegd aan je profiel! Je dagelijkse sterren bonus is geclaimd.';
                        } else {
                            $dailyData['stars_progress'] = $current;
                            $lockedCharacter->ad_daily_data = $dailyData;
                            $left = 2 - $current;
                            $rewardDetails = "⭐ [{$current}/2 Ads] Voortgang opgeslagen! Bekijk nog {$left} advertentie voor jouw dagelijkse 5 Sterren bonus.";
                        }
                        break;

                    case 'mask':
                        $currentProgress = ($lockedCharacter->ad_mask_progress ?? 0) + 1;
                        if ($currentProgress >= 3) {
                            $lockedCharacter->ad_mask_progress = 0;
                            $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + 1;
                            $rewardDetails = '🎭 [3/3 Ads] GEFELICITEERD: +1 Bivakmuts / Masker toegevoegd aan je voorraad!';
                        } else {
                            $lockedCharacter->ad_mask_progress = $currentProgress;
                            $left = 3 - $currentProgress;
                            $rewardDetails = "🎭 [{$currentProgress}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw gratis Masker.';
                        }
                        break;

                    case 'lottery_ticket':
                        $currentProgress = ($lockedCharacter->ad_lottery_progress ?? 0) + 1;
                        if ($currentProgress >= 15) {
                            $lockedCharacter->ad_lottery_progress = 0;

                            $round = LotteryRound::current();
                            $round->increment('total_tickets_sold', 1);
                            $round->increment('jackpot', 8000);

                            do {
                                $ticketNumber = 'ADS-'.str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
                            } while (LotteryTicket::where('lottery_round_id', $round->id)->where('ticket_number', $ticketNumber)->exists());

                            LotteryTicket::create([
                                'lottery_round_id' => $round->id,
                                'character_id' => $lockedCharacter->id,
                                'ticket_number' => $ticketNumber,
                                'currency' => 'ads',
                                'price_paid' => 15,
                            ]);

                            $rewardDetails = "🎟️ [15/15 Ads] GEFELICITEERD: +1 Loterij Ticket ontvangen ({$ticketNumber}) voor de 14-daagse trekking! Je maakt nu kans op de jackpot!";
                        } else {
                            $lockedCharacter->ad_lottery_progress = $currentProgress;
                            $left = 15 - $currentProgress;
                            $rewardDetails = "🎟️ [{$currentProgress}/15 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor een gratis Loterij Ticket.';
                        }
                        break;

                    case 'vault_code':
                        $currentProgress = ($lockedCharacter->ad_vault_progress ?? 0) + 1;
                        if ($currentProgress >= 3) {
                            $lockedCharacter->ad_vault_progress = 0;
                            $bonusCode = VaultCode::generateUniqueCode('SPONSOR');
                            VaultCode::create([
                                'code' => $bonusCode,
                                'reward_cash' => 5000,
                                'reward_stars' => 2,
                                'reward_tokens' => 2,
                                'character_id' => $lockedCharacter->id,
                                'is_redeemed' => false,
                            ]);

                            GameMessage::create([
                                'recipient_id' => $lockedCharacter->id,
                                'sender_name' => 'TBS Sponsor Directie',
                                'subject' => '🎁 Geheime Kluiscode Voucher (3/3 Ads)',
                                'content' => "Beste {$lockedCharacter->name},\n\nGefeliciteerd! Omdat je 3 sponsor advertenties hebt bekeken, ontvang je hierbij jouw unieke Geheime Kluiscode voucher:\n\n👉 **{$bonusCode}**\n\nWissel hem direct in op de MedicijnKluis voor €5.000 bankgeld, 2 sterren en 2 kluismunten!",
                                'type' => 'reward',
                            ]);

                            $rewardDetails = "🔐 [3/3 Ads] GEFELICITEERD: Geheime Kluiscode voucher ontvangen: **{$bonusCode}** (ook bewaard in je inbox)!";
                        } else {
                            $lockedCharacter->ad_vault_progress = $currentProgress;
                            $left = 3 - $currentProgress;
                            $rewardDetails = "🔐 [{$currentProgress}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw Geheime Kluiscode voucher.';
                        }
                        break;

                    case 'bullets':
                        $currentProgress = ($lockedCharacter->ad_bullets_progress ?? 0) + 1;
                        if ($currentProgress >= 3) {
                            $lockedCharacter->ad_bullets_progress = 0;
                            $lockedCharacter->bullets = ($lockedCharacter->bullets ?? 0) + 50;
                            $rewardDetails = '🔫 [3/3 Ads] GEFELICITEERD: +50 Kogels ontvangen! Je munitievoorraad is nu '.number_format($lockedCharacter->bullets, 0, ',', '.').' kogels!';
                        } else {
                            $lockedCharacter->ad_bullets_progress = $currentProgress;
                            $left = 3 - $currentProgress;
                            $rewardDetails = "🔫 [{$currentProgress}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw voorraad van 50 kogels.';
                        }
                        break;

                    case 'vest':
                        $currentProgress = ($lockedCharacter->ad_vest_progress ?? 0) + 1;
                        if ($currentProgress >= 3) {
                            $lockedCharacter->ad_vest_progress = 0;
                            $currentVest = ($lockedCharacter->vest_until && $lockedCharacter->vest_until->isFuture())
                                ? $lockedCharacter->vest_until->copy()
                                : now();
                            $lockedCharacter->vest_until = $currentVest->addHours(12);
                            $rewardDetails = '🛡️ [3/3 Ads] GEFELICITEERD: Kogelwerend Vest geactiveerd voor +12 uur! Je bent zwaar beschermd tegen schutters en vijanden riskeren backfire!';
                        } else {
                            $lockedCharacter->ad_vest_progress = $currentProgress;
                            $left = 3 - $currentProgress;
                            $rewardDetails = "🛡️ [{$currentProgress}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor 12 uur Kogelwerend Vest bescherming.';
                        }
                        break;

                    case 'coma_revive':
                        $currentProgress = ($lockedCharacter->ad_coma_progress ?? 0) + 1;
                        if ($currentProgress >= 3) {
                            $lockedCharacter->ad_coma_progress = 0;
                            $lockedCharacter->coma_until = null;
                            $lockedCharacter->health = max((int) $lockedCharacter->health, 25, (int) ($lockedCharacter->max_health * 0.5));
                            $perks = $lockedCharacter->perks ?? [];
                            if (! empty($perks['liquidation_streak_deaths'])) {
                                $perks['liquidation_streak_deaths'] = 0;
                                $lockedCharacter->perks = $perks;
                            }
                            $rewardDetails = '💉 [3/3 Ads] GEFELICITEERD: Medisch ontwaakt uit Intensive Care Coma! Je bent weer op de been met minimaal 50% HP!';
                        } else {
                            $lockedCharacter->ad_coma_progress = $currentProgress;
                            $left = 3 - $currentProgress;
                            $rewardDetails = "💉 [{$currentProgress}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' om per direct te ontwaken uit Intensive Care Coma.';
                        }
                        break;

                    case 'cooldown':
                        CharacterCooldown::where('character_id', $lockedCharacter->id)->delete();
                        $rewardDetails = '⚡ Alle actieve cooldowns (Hosselen, Zakkenrollen, Kluisjes, Smokkelen, etc.) zijn direct gewist!';
                        break;

                    case 'cash':
                    default:
                        $lockedCharacter->save();
                        $this->economy->addCash($lockedCharacter, 2500, 'sponsor_ad');
                        $rewardDetails = '💵 € 2.500 contant geld ontvangen!';
                        break;
                }
            }

            // Check 10-ad milestone bonus: Dispatch a secret VaultCode to player chat & inbox
            if ($totalAds % 10 === 0) {
                $bonusCode = VaultCode::generateUniqueCode('SPONSOR');
                VaultCode::create([
                    'code' => $bonusCode,
                    'reward_cash' => 15000,
                    'reward_stars' => 3,
                    'reward_tokens' => 0,
                    'character_id' => $lockedCharacter->id,
                    'is_redeemed' => false,
                ]);

                app(ChatBotService::class)->sendVaultCode(
                    $lockedCharacter,
                    $bonusCode,
                    "{$totalAds} Sponsor Ads Mijlpaal",
                    '€ 15.000 bankgeld en 3 Sterren ⭐'
                );

                GameMessage::create([
                    'recipient_id' => $lockedCharacter->id,
                    'sender_name' => 'TBS Sponsor Directie',
                    'subject' => "🎁 Mijlpaal Bonus: {$totalAds} Advertenties Bekeken!",
                    'content' => "Beste {$lockedCharacter->name},\n\nBedankt voor je trouwe support aan de TBS Kliniek! Omdat je zojuist je {$totalAds}e advertentie hebt bekeken, ontvang je een gratis Geheime Kluiscode voucher:\n\n👉 **{$bonusCode}**\n\nWissel hem direct in op de MedicijnKluis voor €15.000 bankgeld en 3 sterren ⭐!\n\nBlijf kijken: bij 250 advertenties ontgrendel je 24 UUR GRATIS VIP!",
                    'type' => 'reward',
                ]);

                $rewardDetails .= " 🎁 + BONUS: Geheime kluiscode voucher gestuurd naar je chat en inbox ({$bonusCode})!";
            }

            // Check 250-ad VIP milestone
            $vipUnlocked = false;
            if ($totalAds % 250 === 0) {
                $vipUnlocked = true;
                $lockedCharacter->is_vip = true;
                $lockedCharacter->vip_expires_at = ($lockedCharacter->vip_expires_at && $lockedCharacter->vip_expires_at->isFuture())
                    ? $lockedCharacter->vip_expires_at->addDay()
                    : now()->addDay();

                GameMessage::create([
                    'recipient_id' => $lockedCharacter->id,
                    'sender_name' => 'TBS Directie & VIP Beheer',
                    'subject' => "👑 GEFELICITEERD: 24 UUR GRATIS VIP ONTGRENDELD ({$totalAds} Ads)!",
                    'content' => "Beste {$lockedCharacter->name},\n\nGeweldig gedaan! Je hebt in totaal {$totalAds} sponsor advertenties bekeken. Als ultieme beloning is jouw account zojuist geactiveerd met:\n\n⭐ **24 UUR VOLLEDIG GRATIS VIP LIDMAATSCHAP** ⭐\n\nGeniet van 5 VIP Punten per uur, dubbele dagelijkse spins, exclusieve VIP Shop toegang en voorrang in de kliniek!",
                    'type' => 'vip',
                ]);

                $rewardDetails .= ' 👑 BOOM! 250 ADS MIJLPAAL: 24 UUR GRATIS VIP IS GEACTIVEERD!';
            }

            $lockedCharacter->save();

            return response()->json([
                'success' => true,
                'message' => "Beloning succesvol geclaimd! {$rewardDetails}",
                'reward_details' => $rewardDetails,
                'reward_type' => $rewardType,
                'ads_today' => $lockedCharacter->getEffectiveAdsWatchedToday(),
                'ads_left_today' => $lockedCharacter->getRemainingAdsToday(),
                'ads_total' => $lockedCharacter->ads_watched_total,
                'vip_unlocked' => $vipUnlocked,
                'vip_progress' => $lockedCharacter->getVipAdMilestoneProgress(),
                'ads_until_vip' => $lockedCharacter->getAdsUntilNextVip(),
                'is_in_prison' => $lockedCharacter->isImprisoned(),
            ]);
        });
    }
}

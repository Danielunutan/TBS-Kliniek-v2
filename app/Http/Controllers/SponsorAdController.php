<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterCooldown;
use App\Models\CharacterTrueCrimeState;
use App\Models\GameMessage;
use App\Models\GameSetting;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SponsorAdController extends Controller
{
    public const ADS_ENABLED = true;

    public const DEFAULT_SMARTLINK = 'https://uplcm.com/4/12026868';

    public const DAILY_LIMIT = 500;

    public const WATCH_DURATION_SECONDS = 15;

    public const COOLDOWN_SECONDS = 0;

    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $levelService,
    ) {}

    /**
     * Show the Sponsorkamer dashboard.
     */
    public function index(): View|RedirectResponse
    {
        if (! self::ADS_ENABLED) {
            return redirect()->route('dashboard')->with('info', 'Sponsor-advertenties zijn momenteel tijdelijk uitgeschakeld.');
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
        if (! self::ADS_ENABLED) {
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

        $rewardType = $request->input('reward_type', 'cash');
        $validTypes = ['cash', 'prison', 'mask', 'star', 'respect', 'vault_code', 'cooldown', 'daily_xp', 'daily_cash', 'daily_stars', 'lottery_ticket', 'true_crime'];
        $isSpecificCooldown = is_string($rewardType) && str_starts_with($rewardType, 'cooldown:');
        if (! in_array($rewardType, $validTypes, true) && ! $isSpecificCooldown) {
            $rewardType = 'cash';
        }

        $dailyData = $character->getAdDailyData();

        if ($rewardType === 'prison') {
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

        $smartlinkUrl = $popupUrl ?? route('sponsor.redirect');

        return response()->json([
            'success' => true,
            'token' => $token,
            'character_id' => $character->id,
            'smartlink_url' => $smartlinkUrl,
            'popup_url' => $popupUrl,
            'robin_reward_id' => $robinRewardId,
            'duration' => self::WATCH_DURATION_SECONDS,
            'message' => 'Sponsoropdracht gestart. Voltooi de opdracht om je beloning direct te ontvangen!',
        ]);
    }

    /**
     * Safe internal redirect to ad SmartLink (passes player tracking subids).
     */
    public function redirect(Request $request): RedirectResponse
    {
        if (! self::ADS_ENABLED) {
            return redirect()->route('dashboard')->with('info', 'Sponsor-advertenties zijn momenteel tijdelijk uitgeschakeld.');
        }

        $character = Auth::user()?->character;
        $subid = $character ? $character->id : 'guest';
        $subid2 = session()->get('tbs_ad_session.reward_type', 'cash');
        $token = session()->get('tbs_ad_session.token', Str::random(8));

        $baseUrl = GameSetting::get('monetag_direct_link', GameSetting::get('cpalead_offerwall_url', self::DEFAULT_SMARTLINK));
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $targetUrl = "{$baseUrl}{$separator}ymid={$subid}_{$token}&subid={$subid}&subid2={$subid2}&requestVar={$subid2}";

        return redirect()->away($targetUrl);
    }

    /**
     * Claim the reward after timer completion.
     */
    public function claim(Request $request): JsonResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        $requestToken = $request->input('token') ?: $request->input('robin_token');

        // Resolve ad session: 1) from Cache by token, 2) from Cache by character id, 3) from Session
        $adSession = null;
        if ($requestToken) {
            $adSession = Cache::get("tbs_ad_token_{$requestToken}");
        }

        if (! $adSession) {
            $charSession = Cache::get("tbs_ad_char_{$character->id}");
            if ($charSession && is_array($charSession)) {
                $adSession = $charSession;
            }
        }

        if (! $adSession) {
            $sessionAd = session()->get('tbs_ad_session');
            if ($sessionAd && is_array($sessionAd)) {
                $adSession = $sessionAd;
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

        // Check daily limit again
        if ($character->getRemainingAdsToday() <= 0) {
            if (! empty($adSession['token'])) {
                Cache::forget("tbs_ad_token_{$adSession['token']}");
            }
            Cache::forget("tbs_ad_char_{$character->id}");
            session()->forget('tbs_ad_session');

            return response()->json([
                'success' => false,
                'message' => 'Daglimiet van '.self::DAILY_LIMIT.' advertenties bereikt.',
            ], 422);
        }

        // Clean up cache and session tokens to prevent replay / double claiming
        if (! empty($adSession['token'])) {
            Cache::forget("tbs_ad_token_{$adSession['token']}");
        }
        Cache::forget("tbs_ad_char_{$character->id}");
        session()->forget('tbs_ad_session');
        session()->save();

        // Update ad counters on character
        $todayStr = now()->toDateString();
        $adsToday = ($character->last_ad_date === $todayStr) ? ($character->ads_watched_today + 1) : 1;
        $totalAds = $character->ads_watched_total + 1;

        $character->ads_watched_today = $adsToday;
        $character->ads_watched_total = $totalAds;
        $character->last_ad_watched_at = now();
        $character->last_ad_date = $todayStr;

        $rewardType = $adSession['reward_type'] ?? 'cash';
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

            CharacterCooldown::where('character_id', $character->id)
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
                    $state = CharacterTrueCrimeState::firstOrCreate(['character_id' => $character->id]);
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

                case 'respect':
                    $character->respect = ($character->respect ?? 0) + 1;
                    $rewardDetails = '🔥 +1 Respect verdiend! Je respect score is direct verhoogd!';
                    break;

                case 'prison':
                    if ($character->isImprisoned()) {
                        $character->is_in_prison = false;
                        $character->prison_until = null;
                        $character->prison_reason = null;
                        $rewardDetails = '🎉 Je hebt de sponsor ad bekeken en bent DIRECT VRIJGELATEN uit de isoleercel!';
                    } else {
                        // Fallback to cash if not in prison
                        $this->economy->addCash($character, 2500, 'sponsor_ad');
                        $rewardDetails = '💵 Je zat niet vast, dus je hebt € 2.500 zakgeld ontvangen!';
                    }
                    break;

                case 'daily_xp':
                    $dailyData = $character->getAdDailyData();
                    if (! empty($dailyData['xp_claimed'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Je hebt de dagelijkse +50 XP bonus voor vandaag al geclaimd!',
                        ], 422);
                    }
                    $current = ($dailyData['xp_progress'] ?? 0) + 1;
                    if ($current >= 3) {
                        $dailyData['xp_progress'] = 3;
                        $dailyData['xp_claimed'] = true;
                        $character->ad_daily_data = $dailyData;
                        $this->levelService->addXp($character, 50);
                        $rewardDetails = '⚡ [3/3 Ads] GEFELICITEERD: +50 XP ontvangen! Je dagelijkse XP beloning is geclaimd.';
                    } else {
                        $dailyData['xp_progress'] = $current;
                        $character->ad_daily_data = $dailyData;
                        $left = 3 - $current;
                        $rewardDetails = "⚡ [{$current}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw dagelijkse +50 XP bonus.';
                    }
                    break;

                case 'daily_cash':
                    $dailyData = $character->getAdDailyData();
                    if (! empty($dailyData['cash_claimed'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Je hebt de dagelijkse € 50.000 cash bonus voor vandaag al geclaimd!',
                        ], 422);
                    }
                    $current = ($dailyData['cash_progress'] ?? 0) + 1;
                    if ($current >= 3) {
                        $dailyData['cash_progress'] = 3;
                        $dailyData['cash_claimed'] = true;
                        $character->ad_daily_data = $dailyData;
                        $this->economy->addCash($character, 50000, 'sponsor_ad');
                        $rewardDetails = '💶 [3/3 Ads] GEFELICITEERD: € 50.000 contant geld ontvangen! Je dagelijkse cash beloning is geclaimd.';
                    } else {
                        $dailyData['cash_progress'] = $current;
                        $character->ad_daily_data = $dailyData;
                        $left = 3 - $current;
                        $rewardDetails = "💶 [{$current}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw dagelijkse € 50.000 bonus.';
                    }
                    break;

                case 'daily_stars':
                    $dailyData = $character->getAdDailyData();
                    if (! empty($dailyData['stars_claimed'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Je hebt de dagelijkse 5 Sterren bonus voor vandaag al geclaimd!',
                        ], 422);
                    }
                    $current = ($dailyData['stars_progress'] ?? 0) + 1;
                    if ($current >= 2) {
                        $dailyData['stars_progress'] = 2;
                        $dailyData['stars_claimed'] = true;
                        $character->ad_daily_data = $dailyData;
                        $character->stars = ($character->stars ?? 0) + 5;
                        $rewardDetails = '⭐ [2/2 Ads] GEFELICITEERD: +5 Sterren toegevoegd aan je profiel! Je dagelijkse sterren bonus is geclaimd.';
                    } else {
                        $dailyData['stars_progress'] = $current;
                        $character->ad_daily_data = $dailyData;
                        $left = 2 - $current;
                        $rewardDetails = "⭐ [{$current}/2 Ads] Voortgang opgeslagen! Bekijk nog {$left} advertentie voor jouw dagelijkse 5 Sterren bonus.";
                    }
                    break;

                case 'mask':
                    $currentProgress = ($character->ad_mask_progress ?? 0) + 1;
                    if ($currentProgress >= 3) {
                        $character->ad_mask_progress = 0;
                        $character->masks = ($character->masks ?? 0) + 1;
                        $rewardDetails = '🎭 [3/3 Ads] GEFELICITEERD: +1 Bivakmuts / Masker toegevoegd aan je voorraad!';
                    } else {
                        $character->ad_mask_progress = $currentProgress;
                        $left = 3 - $currentProgress;
                        $rewardDetails = "🎭 [{$currentProgress}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw gratis Masker.';
                    }
                    break;

                case 'lottery_ticket':
                    $currentProgress = ($character->ad_lottery_progress ?? 0) + 1;
                    if ($currentProgress >= 15) {
                        $character->ad_lottery_progress = 0;

                        $round = LotteryRound::current();
                        $round->increment('total_tickets_sold', 1);
                        $round->increment('jackpot', 8000);

                        $ticketNumber = 'ADS-'.str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT);

                        LotteryTicket::create([
                            'lottery_round_id' => $round->id,
                            'character_id' => $character->id,
                            'ticket_number' => $ticketNumber,
                            'currency' => 'ads',
                            'price_paid' => 15,
                        ]);

                        $rewardDetails = "🎟️ [15/15 Ads] GEFELICITEERD: +1 Loterij Ticket ontvangen ({$ticketNumber}) voor de 14-daagse trekking! Je maakt nu kans op de jackpot!";
                    } else {
                        $character->ad_lottery_progress = $currentProgress;
                        $left = 15 - $currentProgress;
                        $rewardDetails = "🎟️ [{$currentProgress}/15 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor een gratis Loterij Ticket.';
                    }
                    break;

                case 'vault_code':
                    $currentProgress = ($character->ad_vault_progress ?? 0) + 1;
                    if ($currentProgress >= 3) {
                        $character->ad_vault_progress = 0;
                        $bonusCode = 'SPONSOR-'.strtoupper(Str::random(6));
                        VaultCode::create([
                            'code' => $bonusCode,
                            'reward_cash' => 5000,
                            'reward_stars' => 2,
                            'reward_tokens' => 2,
                            'character_id' => $character->id,
                            'is_redeemed' => false,
                        ]);

                        GameMessage::create([
                            'recipient_id' => $character->id,
                            'sender_name' => 'TBS Sponsor Directie',
                            'subject' => '🎁 Geheime Kluiscode Voucher (3/3 Ads)',
                            'content' => "Beste {$character->name},\n\nGefeliciteerd! Omdat je 3 sponsor advertenties hebt bekeken, ontvang je hierbij jouw unieke Geheime Kluiscode voucher:\n\n👉 **{$bonusCode}**\n\nWissel hem direct in op de MedicijnKluis voor €5.000 bankgeld, 2 sterren en 2 kluismunten!",
                            'type' => 'reward',
                        ]);

                        $rewardDetails = "🔐 [3/3 Ads] GEFELICITEERD: Geheime Kluiscode voucher ontvangen: **{$bonusCode}** (ook bewaard in je inbox)!";
                    } else {
                        $character->ad_vault_progress = $currentProgress;
                        $left = 3 - $currentProgress;
                        $rewardDetails = "🔐 [{$currentProgress}/3 Ads] Voortgang opgeslagen! Bekijk nog {$left} ".($left === 1 ? 'advertentie' : 'advertenties').' voor jouw Geheime Kluiscode voucher.';
                    }
                    break;

                case 'cooldown':
                    CharacterCooldown::where('character_id', $character->id)->delete();
                    $rewardDetails = '⚡ Alle actieve cooldowns (Hosselen, Zakkenrollen, Kluisjes, Smokkelen, etc.) zijn direct gewist!';
                    break;

                case 'cash':
                default:
                    $this->economy->addCash($character, 2500, 'sponsor_ad');
                    $rewardDetails = '💵 € 2.500 contant geld ontvangen!';
                    break;
            }
        }

        // Check 10-ad milestone bonus: Dispatch a secret VaultCode to player chat & inbox
        if ($totalAds % 10 === 0) {
            $bonusCode = 'SPONSOR-'.strtoupper(Str::random(6));
            VaultCode::create([
                'code' => $bonusCode,
                'reward_cash' => 15000,
                'reward_stars' => 3,
                'reward_tokens' => 0,
                'character_id' => $character->id,
                'is_redeemed' => false,
            ]);

            app(ChatBotService::class)->sendVaultCode(
                $character,
                $bonusCode,
                "{$totalAds} Sponsor Ads Mijlpaal",
                '€ 15.000 bankgeld en 3 Sterren ⭐'
            );

            GameMessage::create([
                'recipient_id' => $character->id,
                'sender_name' => 'TBS Sponsor Directie',
                'subject' => "🎁 Mijlpaal Bonus: {$totalAds} Advertenties Bekeken!",
                'content' => "Beste {$character->name},\n\nBedankt voor je trouwe support aan de TBS Kliniek! Omdat je zojuist je {$totalAds}e advertentie hebt bekeken, ontvang je een gratis Geheime Kluiscode voucher:\n\n👉 **{$bonusCode}**\n\nWissel hem direct in op de MedicijnKluis voor €15.000 bankgeld en 3 sterren ⭐!\n\nBlijf kijken: bij 250 advertenties ontgrendel je 24 UUR GRATIS VIP!",
                'type' => 'reward',
            ]);

            $rewardDetails .= " 🎁 + BONUS: Geheime kluiscode voucher gestuurd naar je chat en inbox ({$bonusCode})!";
        }

        // Check 250-ad VIP milestone
        $vipUnlocked = false;
        if ($totalAds % 250 === 0) {
            $vipUnlocked = true;
            $character->is_vip = true;
            $character->vip_expires_at = ($character->vip_expires_at && $character->vip_expires_at->isFuture())
                ? $character->vip_expires_at->addDay()
                : now()->addDay();

            GameMessage::create([
                'recipient_id' => $character->id,
                'sender_name' => 'TBS Directie & VIP Beheer',
                'subject' => "👑 GEFELICITEERD: 24 UUR GRATIS VIP ONTGRENDELD ({$totalAds} Ads)!",
                'content' => "Beste {$character->name},\n\nGeweldig gedaan! Je hebt in totaal {$totalAds} sponsor advertenties bekeken. Als ultieme beloning is jouw account zojuist geactiveerd met:\n\n⭐ **24 UUR VOLLEDIG GRATIS VIP LIDMAATSCHAP** ⭐\n\nGeniet van 5 VIP Punten per uur, dubbele dagelijkse spins, exclusieve VIP Shop toegang en voorrang in de kliniek!",
                'type' => 'vip',
            ]);

            $rewardDetails .= ' 👑 BOOM! 250 ADS MIJLPAAL: 24 UUR GRATIS VIP IS GEACTIVEERD!';
        }

        $character->save();

        return response()->json([
            'success' => true,
            'message' => "Beloning succesvol geclaimd! {$rewardDetails}",
            'reward_details' => $rewardDetails,
            'reward_type' => $rewardType,
            'ads_today' => $character->getEffectiveAdsWatchedToday(),
            'ads_left_today' => $character->getRemainingAdsToday(),
            'ads_total' => $character->ads_watched_total,
            'vip_unlocked' => $vipUnlocked,
            'vip_progress' => $character->getVipAdMilestoneProgress(),
            'ads_until_vip' => $character->getAdsUntilNextVip(),
            'is_in_prison' => $character->isImprisoned(),
        ]);
    }
}

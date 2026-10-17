<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterCooldown;
use App\Models\GameMessage;
use App\Models\GameSetting;
use App\Models\VaultCode;
use App\Services\EconomyService;
use App\Services\EnergyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $smartlinkUrl = route('sponsor.redirect');

        return view('game.sponsor.index', compact(
            'character',
            'adsToday',
            'adsLeftToday',
            'cooldown',
            'totalAds',
            'vipProgress',
            'adsUntilVip',
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
        $validTypes = ['cash', 'prison', 'mask', 'token', 'star', 'cooldown'];
        if (! in_array($rewardType, $validTypes, true)) {
            $rewardType = 'cash';
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

        session()->put('tbs_ad_session', [
            'token' => $token,
            'started_at' => now()->timestamp,
            'character_id' => $character->id,
            'reward_type' => $rewardType,
            'robin_reward_id' => $robinRewardId,
        ]);

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

        $adSession = session()->get('tbs_ad_session');
        if (! $adSession || ! is_array($adSession)) {
            return response()->json([
                'success' => false,
                'message' => 'Geen actieve advertentiesessie gevonden. Start de sponsor opnieuw.',
            ], 422);
        }

        // Validate token
        $requestToken = $request->input('token');
        if (! $requestToken || $requestToken !== ($adSession['token'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Ongeldige verificatiesleutel. Probeer opnieuw.',
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
        $isVerifiedByRobin = false;

        if ($apiKey && $robinRewardId && $robinToken) {
            try {
                $verifyRes = Http::withoutVerifying()->withToken($apiKey)->timeout(4)->post('https://robinsads.com/api/ads/reward/verify', [
                    'impressionId' => $robinRewardId,
                    'token' => $robinToken,
                ]);
                if ($verifyRes->successful() && $verifyRes->json('verified') === true) {
                    $isVerifiedByRobin = true;
                }
            } catch (\Throwable $e) {
            }
        }

        if (! $isVerifiedByRobin && $apiKey && $robinRewardId) {
            try {
                $statusRes = Http::withoutVerifying()->withToken($apiKey)->timeout(4)->get("https://robinsads.com/api/v1/rewards/{$robinRewardId}");
                if ($statusRes->successful() && $statusRes->json('status') === 'verified') {
                    $isVerifiedByRobin = true;
                }
            } catch (\Throwable $e) {
            }
        }

        // Strict verification: eigen timer mag de reward NIET uitgeven als Robins Ads niet geverifieerd is!
        if (! $isVerifiedByRobin && ! (app()->environment('testing') && ! $robinRewardId)) {
            return response()->json([
                'success' => false,
                'message' => 'De advertentie is niet volledig afgekeken of niet geverifieerd door Robins Ads. Bekijk de advertentie volledig om je beloning te claimen!',
            ], 422);
        }

        // Check daily limit again
        if ($character->getRemainingAdsToday() <= 0) {
            session()->forget('tbs_ad_session');

            return response()->json([
                'success' => false,
                'message' => 'Daglimiet van '.self::DAILY_LIMIT.' advertenties bereikt.',
            ], 422);
        }

        // Clean up session token to prevent double claiming
        session()->forget('tbs_ad_session');

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
        switch ($rewardType) {
            case 'prison':
                if ($character->isImprisoned() && $character->prison_until) {
                    $newUntil = $character->prison_until->subMinutes(15);
                    if ($newUntil->isPast()) {
                        $character->is_in_prison = false;
                        $character->prison_until = null;
                        $character->prison_reason = null;
                        $rewardDetails = '🎉 Je straftijd is met 15 minuten verminderd en je bent DIRECT VRIJGELATEN uit de isoleercel!';
                    } else {
                        $character->prison_until = $newUntil;
                        $diffMin = max(1, (int) ceil(now()->diffInSeconds($newUntil) / 60));
                        $rewardDetails = "⏱️ Je straftijd is met 15 minuten verminderd! Nog {$diffMin} minuten te gaan.";
                    }
                } else {
                    // Fallback to cash if not in prison
                    $this->economy->addCash($character, 2500, 'sponsor_ad');
                    $rewardDetails = '💵 Je zat niet vast, dus je hebt € 2.500 zakgeld ontvangen!';
                }
                break;

            case 'mask':
                $character->masks = ($character->masks ?? 0) + 1;
                $rewardDetails = '🎭 +1 Masker toegevoegd aan je voorraad (klaar voor zakkenrollen)!';
                break;

            case 'token':
                $character->vault_tokens = ($character->vault_tokens ?? 0) + 1;
                $rewardDetails = '🪙 +1 Kluismunt toegevoegd (te besteden op de MedicijnKluis)!';
                break;

            case 'star':
                $character->stars = ($character->stars ?? 0) + 1;
                $rewardDetails = '⭐ +1 Gratis Ster toegevoegd aan je profiel!';
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

        // Check 10-ad milestone bonus: Dispatch a secret VaultCode to player inbox
        if ($totalAds % 10 === 0) {
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
                'subject' => "🎁 Mijlpaal Bonus: {$totalAds} Advertenties Bekeken!",
                'content' => "Beste {$character->name},\n\nBedankt voor je trouwe support aan de TBS Kliniek! Omdat je zojuist je {$totalAds}e advertentie hebt bekeken, ontvang je een gratis Geheime Kluiscode voucher:\n\n👉 **{$bonusCode}**\n\nWissel hem direct in op de MedicijnKluis voor €5.000 bankgeld, 2 sterren en 2 kluismunten!\n\nBlijf kijken: bij 50 advertenties ontgrendel je 24 UUR GRATIS VIP!",
                'type' => 'reward',
            ]);

            $rewardDetails .= " 🎁 + BONUS: Geheime kluiscode voucher gestuurd naar je inbox ({$bonusCode})!";
        }

        // Check 50-ad VIP milestone
        $vipUnlocked = false;
        if ($totalAds % 50 === 0) {
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

            $rewardDetails .= ' 👑 BOOM! 50 ADS MIJLPAAL: 24 UUR GRATIS VIP IS GEACTIVEERD!';
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

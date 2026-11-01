<?php

namespace App\Http\Controllers;

use App\Models\DiscordShieldBan;
use App\Models\DiscordShieldLog;
use App\Models\DiscordShieldSetting;
use App\Models\DiscordVerification;
use App\Models\User;
use App\Services\DiscordShieldService;
use App\Services\VpnDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiscordShieldController extends Controller
{
    public function __construct(
        protected DiscordShieldService $discordService
    ) {}

    /**
     * Show the main verification landing page.
     */
    public function show(Request $request): View
    {
        $discordUser = session('discord_shield_user');
        $turnstileSiteKey = DiscordShieldSetting::get('turnstile_site_key', env('TURNSTILE_SITE_KEY', '0x4AAAAAAFTb6Vv0yuFCIDYU'));
        $user = Auth::user();
        $character = $user?->character;
        $widgetData = $this->getDiscordWidgetData();

        return view('discord.verify', [
            'discordUser' => $discordUser,
            'turnstileSiteKey' => $turnstileSiteKey,
            'user' => $user,
            'character' => $character,
            'hasClaimedBonus' => $character?->hasClaimedDiscordReward() ?? false,
            'isLinked' => ! empty($user?->discord_id),
            'widgetData' => $widgetData,
        ]);
    }

    /**
     * Get Discord Guild Widget data (cached for 60 seconds).
     */
    public function getDiscordWidgetData(): array
    {
        $guildId = DiscordShieldSetting::get('guild_id', '1546867905139056650');

        return Cache::remember("discord_widget_data_{$guildId}", 60, function () use ($guildId) {
            try {
                $resp = Http::timeout(3)->get("https://discord.com/api/guilds/{$guildId}/widget.json");
                if ($resp->successful()) {
                    return $resp->json();
                }
            } catch (\Throwable $e) {
                Log::debug('Discord Widget fetch error: '.$e->getMessage());
            }

            return [
                'id' => $guildId,
                'name' => 'TBS Kliniek',
                'instant_invite' => 'https://discord.gg/1546867905139056650',
                'presence_count' => 33,
                'members' => [],
            ];
        });
    }

    /**
     * API endpoint for dynamic widget refresh.
     */
    public function widgetData(): JsonResponse
    {
        return response()->json($this->getDiscordWidgetData());
    }

    /**
     * Redirect to Discord OAuth2 authorization.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = DiscordShieldSetting::get('client_id', config('services.discord.client_id', '1558628995439005748'));
        $redirectUri = 'https://tbs-kliniek.nl/discord/callback';
        $state = 'shield_'.Str::random(24);
        session(['discord_shield_state' => $state]);

        $url = 'https://discord.com/api/oauth2/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'identify email guilds.join',
            'state' => $state,
            'prompt' => 'consent',
        ]);

        return redirect()->away($url);
    }

    /**
     * Handle Discord OAuth2 callback.
     */
    public function callback(Request $request): Response|RedirectResponse
    {
        $state = (string) $request->query('state');
        $code = $request->query('code');

        // Check if this was a game web login from the homepage/web auth
        // Only shield states stay in this flow. Every other state goes to the login flow, which checks it.
        if ($state !== '' && ! str_starts_with($state, 'shield_')) {
            return app(AuthController::class)->discordCallback($request);
        }

        if (! $state || $state !== session('discord_shield_state')) {
            return redirect()->route('discord.verify')->with('error', 'Beveiligingsfout: Ongeldige sessie state. Probeer het opnieuw.');
        }

        if (! $code) {
            return redirect()->route('discord.verify')->with('error', 'Discord autorisatie is geannuleerd.');
        }

        $clientId = DiscordShieldSetting::get('client_id') ?: config('services.discord.client_id');
        $clientSecret = DiscordShieldSetting::get('client_secret') ?: config('services.discord.client_secret');
        $redirectUri = 'https://tbs-kliniek.nl/discord/callback';

        try {
            // Exchange code for token
            $tokenResp = Http::asForm()->timeout(10)->post('https://discord.com/api/oauth2/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            if (! $tokenResp->successful()) {
                Log::warning('Discord OAuth Token Error: '.$tokenResp->body());

                return redirect()->route('discord.verify')->with('error', 'Kon geen verbinding maken met Discord. Controleer de client gegevens.');
            }

            $tokenData = $tokenResp->json();
            $accessToken = $tokenData['access_token'];

            // Fetch user info
            $userResp = Http::withToken($accessToken)->timeout(10)->get('https://discord.com/api/users/@me');
            if (! $userResp->successful()) {
                return redirect()->route('discord.verify')->with('error', 'Kon Discord gebruikersprofiel niet ophalen.');
            }

            $userData = $userResp->json();
            session([
                'discord_shield_user' => [
                    'id' => (string) $userData['id'],
                    'username' => $userData['username'],
                    'global_name' => $userData['global_name'] ?? $userData['username'],
                    'avatar' => $userData['avatar'] ?? null,
                    'email' => $userData['email'] ?? null,
                ],
                'discord_shield_access_token' => $accessToken,
            ]);

            return redirect()->route('discord.verify')->with('success', 'Discord account gekoppeld! Voltooi nu de korte beveiligingscheck.');
        } catch (\Throwable $e) {
            Log::error('Discord Callback Exception: '.$e->getMessage());

            return redirect()->route('discord.verify')->with('error', 'Er is een onverwachte fout opgetreden bij het inloggen.');
        }
    }

    /**
     * Process Turnstile + Device Fingerprint + Anti-Alt verification.
     */
    public function process(Request $request): JsonResponse
    {
        $discordUser = session('discord_shield_user');
        if (! $discordUser) {
            return response()->json([
                'success' => false,
                'message' => 'Geen gekoppeld Discord account gevonden. Log eerst in met Discord.',
            ], 401);
        }

        $discordId = (string) $discordUser['id'];
        $username = $discordUser['username'];
        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();
        $country = $request->header('CF-IPCountry') ?? 'NL';

        // 1. Validate Cloudflare Turnstile
        $turnstileEnabled = DiscordShieldSetting::get('turnstile_enabled', '1') === '1';
        if ($turnstileEnabled) {
            $turnstileResponse = $request->input('turnstile_token');
            $turnstileSecret = DiscordShieldSetting::get('turnstile_secret_key', env('TURNSTILE_SECRET_KEY'));

            if (! $turnstileResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vul de Cloudflare Turnstile verificatie in.',
                ], 422);
            }

            $verifyResp = Http::asForm()->timeout(6)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $turnstileSecret,
                'response' => $turnstileResponse,
                'remoteip' => $ip,
            ]);

            if (! $verifyResp->successful() || ! $verifyResp->json('success')) {
                DiscordShieldLog::log('turnstile_failed', "Turnstile verificatie mislukt voor {$username} ({$discordId})", [
                    'discord_id' => $discordId,
                    'username' => $username,
                    'ip_address' => $ip,
                    'severity' => 'warning',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'De Cloudflare beveiligingscheck is mislukt. Probeer het opnieuw.',
                ], 422);
            }
        }

        // 2. Hardware / Browser Fingerprint + WebRTC & Timezone Intelligence
        $fingerprintHash = (string) $request->input('fingerprint_hash');
        $fingerprintRaw = (array) $request->input('fingerprint_raw', []);
        $deviceType = $request->input('device_type', 'Desktop');
        $browser = $request->input('browser', 'Onbekend');
        $os = $request->input('os', 'Onbekend');
        $webrtcIps = (array) $request->input('webrtc_ips', []);
        $timezoneOffset = $request->input('timezone_offset');
        $timezoneName = (string) $request->input('timezone_name', '');

        // Enrich fingerprint raw data
        $fingerprintRaw['webrtc_ips'] = $webrtcIps;
        $fingerprintRaw['timezone_offset'] = $timezoneOffset;
        $fingerprintRaw['timezone_name'] = $timezoneName;

        if (empty($fingerprintHash) || strlen($fingerprintHash) < 16) {
            $fingerprintHash = hash('sha256', $ip.'|'.$request->userAgent().'|'.$discordId);
        }

        // WebRTC Candidate Discovery (detect unmasked real public IP behind proxy extension)
        $leakedPublicIp = null;
        foreach ($webrtcIps as $candidateIp) {
            if (is_string($candidateIp) && filter_var($candidateIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                if ($candidateIp !== $ip) {
                    $leakedPublicIp = $candidateIp;
                    break;
                }
            }
        }

        // 3. Check Blacklist / Bans
        if (
            DiscordShieldBan::isBanned('discord_id', $discordId) ||
            DiscordShieldBan::isBanned('fingerprint', $fingerprintHash) ||
            DiscordShieldBan::isBanned('ip_address', $ip) ||
            ($leakedPublicIp && DiscordShieldBan::isBanned('ip_address', $leakedPublicIp))
        ) {
            $this->handleRejection('banned', $discordUser, $ip, $country, $fingerprintHash, null, false, 100, 'Dit apparaat of account staat op de zwarte lijst.');

            return response()->json([
                'success' => false,
                'banned' => true,
                'message' => 'Toegang geweigerd: Je account of apparaat is permanent verbannen van de TBS Kliniek Discord server.',
            ], 403);
        }

        // 4. Real Multi-Layered VPN / Proxy Intelligence (ProxyCheck.io + IP-API.com)
        $blockVpns = DiscordShieldSetting::get('block_vpns', '1') === '1';
        $vpnService = app(VpnDetectionService::class);
        $vpnCheck = $vpnService->check($ip);
        $isVpn = $vpnCheck['is_vpn'];
        $vpnProvider = $vpnCheck['provider'];
        if (! empty($vpnCheck['country'])) {
            $country = $vpnCheck['country'];
        }

        // If main IP is clean, also inspect leaked WebRTC public IP
        if (! $isVpn && $leakedPublicIp) {
            $leakedCheck = $vpnService->check($leakedPublicIp);
            if ($leakedCheck['is_vpn']) {
                $isVpn = true;
                $vpnProvider = $leakedCheck['provider'] ? "WebRTC Leak: {$leakedCheck['provider']}" : 'WebRTC Leaked VPN/Proxy';
            }
        }

        // Timezone consistency check (E.g. connecting via European IP but client clock is US/Asia proxy)
        $riskScore = 5;
        if (in_array(strtoupper($country), ['NL', 'BE', 'DE', 'FR', 'LU', 'GB']) && is_numeric($timezoneOffset)) {
            $offsetVal = (int) $timezoneOffset;
            // A genuine European visitor's browser getTimezoneOffset() is between -120 and 0.
            // If the browser reports offset > 180 (Americas) or < -360 (Asia/Oceania), flag proxy mismatch
            if ($offsetVal > 180 || $offsetVal < -360) {
                $riskScore = 40;
                DiscordShieldLog::log('timezone_mismatch', "Tijdzone mismatch voor {$username}: Land {$country} vs browser offset {$offsetVal}m ({$timezoneName})", [
                    'discord_id' => $discordId,
                    'username' => $username,
                    'ip_address' => $ip,
                    'severity' => 'warning',
                    'metadata' => [
                        'country' => $country,
                        'timezone_offset' => $offsetVal,
                        'timezone_name' => $timezoneName,
                        'webrtc_leak' => $leakedPublicIp,
                    ],
                ]);
            }
        }

        if ($isVpn && $blockVpns) {
            $providerLabel = $vpnProvider ? " ({$vpnProvider})" : '';
            $this->handleRejection('vpn_blocked', $discordUser, $ip, $country, $fingerprintHash, null, true, 85, "VPN of Proxy gedetecteerd: {$vpnProvider}");

            return response()->json([
                'success' => false,
                'vpn_blocked' => true,
                'message' => "Je gebruikt een actieve VPN of proxy verbinding{$providerLabel}. Schakel je VPN even helemaal uit op je telefoon of computer en probeer het daarna opnieuw.",
            ], 403);
        }

        // 5. Anti-Alt & Double Counter Check
        $blockAlts = DiscordShieldSetting::get('block_alts', '1') === '1';
        $permCookieUid = $request->cookie('tbs_perm_uid');

        $existingVerification = DiscordVerification::where('discord_id', '!=', $discordId)
            ->where(function ($q) use ($fingerprintHash, $permCookieUid): void {
                $q->where('fingerprint_hash', $fingerprintHash);
                if (! empty($permCookieUid)) {
                    $q->orWhere('fingerprint_raw->cookie_uid', $permCookieUid);
                }
            })
            ->first();

        if ($existingVerification && $blockAlts) {
            $altUsername = $existingVerification->username;
            $altId = $existingVerification->discord_id;

            $this->handleRejection('alt_detected', $discordUser, $ip, $country, $fingerprintHash, $altUsername, $isVpn, 95, "Alt account van {$altUsername} ({$altId})");

            return response()->json([
                'success' => false,
                'alt_detected' => true,
                'message' => "Toegang geweigerd: Er is al een ander Discord-account geregistreerd vanaf dit apparaat ({$altUsername}). Dubbele accounts zijn niet toegestaan.",
            ], 403);
        }

        // 6. SUCCESS: Register Verification
        $character = Auth::check() ? Auth::user()->character : null;

        $verification = DiscordVerification::updateOrCreate(
            ['discord_id' => $discordId],
            [
                'username' => $username,
                'discriminator' => $discordUser['discriminator'] ?? null,
                'avatar' => $discordUser['avatar'] ?? null,
                'email' => $discordUser['email'] ?? null,
                'character_id' => $character?->id,
                'user_id' => Auth::id(),
                'ip_address' => $ip,
                'country_code' => $country,
                'is_vpn' => $isVpn,
                'vpn_provider' => $vpnProvider,
                'fingerprint_hash' => $fingerprintHash,
                'fingerprint_raw' => $fingerprintRaw,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'status' => 'verified',
                'risk_score' => $riskScore,
                'turnstile_passed' => true,
                'verified_at' => now(),
            ]
        );

        // 7. Assign Discord Role via Bot API
        $guildId = $this->discordService->getGuildId();
        $verifiedRoleId = (string) DiscordShieldSetting::get('verified_role_id', '');
        $unverifiedRoleId = (string) DiscordShieldSetting::get('unverified_role_id', '');

        $roleGiven = false;
        if (! empty($guildId) && ! empty($verifiedRoleId)) {
            $roleGiven = $this->discordService->addRole($guildId, $discordId, $verifiedRoleId, 'TBS Shield verificatie succesvol voltooid');
            if (! empty($unverifiedRoleId)) {
                $this->discordService->removeRole($guildId, $discordId, $unverifiedRoleId, 'TBS Shield verificatie voltooid');
            }
        }

        // 7b. Link Game Account & Grant Starter Bonus (€2.500 Cash + 3 Days VIP + Discord Speler Role)
        $bonusAwarded = false;
        if (Auth::check()) {
            $authUser = Auth::user();
            if (empty($authUser->discord_id)) {
                if (! User::where('discord_id', $discordId)->where('id', '!=', $authUser->id)->exists()) {
                    $authUser->discord_id = $discordId;
                    $authUser->save();
                }
            }

            if ($authUser->discord_id === $discordId && $character) {
                if (! $character->hasClaimedDiscordReward()) {
                    $character->claimDiscordReward();
                    $bonusAwarded = true;
                }
                if (! empty($guildId)) {
                    $this->discordService->syncGameRoles($guildId, $discordId, $character);
                }
            }
        }

        // 8. Log Audit & Send Staff Embed
        DiscordShieldLog::log('verification_success', "Gebruiker {$username} ({$discordId}) succesvol geverifieerd via TBS Shield.", [
            'discord_id' => $discordId,
            'username' => $username,
            'ip_address' => $ip,
            'fingerprint_hash' => $fingerprintHash,
            'severity' => 'success',
            'metadata' => [
                'role_given' => $roleGiven,
                'bonus_awarded' => $bonusAwarded,
                'character_name' => $character?->name,
                'device' => "{$os} / {$browser} ({$deviceType})",
            ],
        ]);

        $this->discordService->sendVerificationReport(
            'verified',
            $discordUser,
            $ip,
            $country,
            $fingerprintHash,
            null,
            $isVpn,
            5
        );

        // Keep discord_id in session for seamless game account creation / linking
        session([
            'verified_discord_id' => $discordId,
            'verified_discord_username' => $username,
        ]);

        // Clear verification session state
        session()->forget(['discord_shield_user', 'discord_shield_state', 'discord_shield_access_token']);

        $successMsg = $bonusAwarded
            ? 'Gefeliciteerd! Je bent geverifieerd en je spelersaccount heeft direct €2.500 zakgeld, 3 dagen gratis VIP en de Discord spelersrol ontvangen!'
            : 'Gefeliciteerd! Je account is succesvol geverifieerd. Je hebt nu direct toegang tot de TBS Kliniek Discord server!';

        return response()->json([
            'success' => true,
            'role_given' => $roleGiven,
            'bonus_awarded' => $bonusAwarded,
            'message' => $successMsg,
        ]);
    }

    /**
     * Link current authenticated player to their verified Discord account and claim starter bonus.
     */
    public function linkGameAccount(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->character) {
            return redirect()->route('login')->with('error', 'Log eerst in om je Discord te koppelen.');
        }

        $character = $user->character;
        $discordUser = session('discord_shield_user');
        $discordId = $user->discord_id
            ?? ($discordUser['id'] ?? session('verified_discord_id'));

        if (! $discordId) {
            return redirect()->route('discord.redirect');
        }

        // Prevent linking if this Discord account is already linked to another player
        if ($user->discord_id !== $discordId && User::where('discord_id', $discordId)->where('id', '!=', $user->id)->exists()) {
            return redirect()->route('discord.verify')->with('error', 'Dit Discord account is al gekoppeld aan een ander spelersaccount.');
        }

        $user->discord_id = $discordId;
        $user->save();

        $claimed = false;
        if (! $character->hasClaimedDiscordReward()) {
            $character->claimDiscordReward();
            $claimed = true;
        }

        $guildId = $this->discordService->getGuildId();
        if (! empty($guildId)) {
            $this->discordService->syncGameRoles($guildId, $discordId, $character);
        }

        $msg = $claimed
            ? 'Top! Je Discord is succesvol gekoppeld! Je hebt direct €2.500 zakgeld en 3 dagen GRATIS VIP ontvangen, plus de Discord spelersrol!'
            : 'Je Discord account en server-rollen zijn succesvol gesynchroniseerd!';

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Re-sync Discord roles for the current player (Speler role + VIP role).
     */
    public function syncRoles(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->character) {
            return redirect()->route('login');
        }

        if (empty($user->discord_id)) {
            return redirect()->route('discord.redirect')->with('error', 'Koppel eerst je Discord account om rollen te synchroniseren.');
        }

        $guildId = $this->discordService->getGuildId();
        if (! empty($guildId)) {
            $this->discordService->syncGameRoles($guildId, $user->discord_id, $user->character);
        }

        return redirect()->back()->with('success', 'Je Discord server-rollen (Speler & VIP status) zijn succesvol gesynchroniseerd!');
    }

    /**
     * Handle rejected verifications (logging, mod logs, and auto-kicking if enabled).
     */
    protected function handleRejection(
        string $status,
        array $userData,
        string $ip,
        string $country,
        string $fingerprint,
        ?string $altUsername = null,
        bool $isVpn = false,
        int $riskScore = 80,
        string $reason = 'Beveiligingsbeleid geschonden'
    ): void {
        $discordId = $userData['id'];
        $username = $userData['username'];

        DiscordShieldLog::log($status, "Verificatie afgewezen voor {$username} ({$discordId}): {$reason}", [
            'discord_id' => $discordId,
            'username' => $username,
            'ip_address' => $ip,
            'fingerprint_hash' => $fingerprint,
            'severity' => 'danger',
            'metadata' => [
                'alt_username' => $altUsername,
                'risk_score' => $riskScore,
                'reason' => $reason,
            ],
        ]);

        $guildId = $this->discordService->getGuildId();
        $autoKickAlts = DiscordShieldSetting::get('auto_kick_alts', '1') === '1';
        $autoBanEvaders = DiscordShieldSetting::get('auto_ban_evaders', '0') === '1';

        if (! empty($guildId)) {
            if ($autoBanEvaders && $status === 'banned') {
                $this->discordService->banMember($guildId, $discordId, "TBS Shield: {$reason}");
            } elseif ($autoKickAlts && in_array($status, ['alt_detected', 'banned'])) {
                $this->discordService->kickMember($guildId, $discordId, "TBS Shield: {$reason}");
            }
        }

        $this->discordService->sendVerificationReport(
            $status,
            $userData,
            $ip,
            $country,
            $fingerprint,
            $altUsername,
            $isVpn,
            $riskScore
        );
    }
}

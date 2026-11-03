<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\DiscordShieldSetting;
use App\Models\DiscordVerification;
use App\Models\GameMessage;
use App\Models\User;
use App\Services\DiscordShieldService;
use App\Services\ReferralService;
use App\Services\VpnDetectionService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login / register landing page.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->has('logout') || $request->has('uitloggen') || $request->has('reset')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if ($request->filled('ref')) {
            $request->session()->put('referral_name', trim((string) $request->query('ref')));
        }

        try {
            $stats = Cache::remember('tbs_login_page_stats', 300, function () {
                return [
                    'totalPlayers' => Character::count(),
                    'totalCash' => (int) (Character::sum('cash') + Character::sum('bank')),
                    'topCharacters' => Character::with('user')->orderByDesc('respect')->limit(4)->get(),
                ];
            });
            $totalPlayers = $stats['totalPlayers'];
            $totalCash = $stats['totalCash'];
            $topCharacters = $stats['topCharacters'];
        } catch (\Throwable) {
            $totalPlayers = 1420;
            $totalCash = 24800000;
            $topCharacters = collect();
        }

        return view('auth.login', compact('totalPlayers', 'totalCash', 'topCharacters'));
    }

    /**
     * Discord SSO login handler.
     */
    public function discordLogin(Request $request): Response|RedirectResponse
    {
        // Never log in or mutate state on background prefetch requests
        if ($request->header('Purpose') === 'prefetch' || $request->header('Sec-Purpose') === 'prefetch' || $request->header('X-Sec-Purpose') === 'prefetch') {
            return response()->noContent();
        }

        $clientId = DiscordShieldSetting::get('client_id', config('services.discord.client_id', '1558628995439005748'));
        $redirectUri = 'https://tbs-kliniek.nl/discord/callback';

        if ($clientId) {
            $state = bin2hex(random_bytes(16));
            session(['oauth2state' => $state]);
            $url = 'https://discord.com/api/oauth2/authorize?client_id='.$clientId.'&redirect_uri='.urlencode($redirectUri).'&response_type=code&scope=identify%20email&state='.$state;

            return redirect()->away($url);
        }

        if (app()->environment('production')) {
            return redirect()->route('login')->with('error', 'Discord login is momenteel niet geconfigureerd.');
        }

        // Seamless instant demo login for local development
        $user = User::firstOrCreate(
            ['email' => 'discord_inmate@tbskliniek.nl'],
            [
                'name' => 'Discord Inmate',
                'password' => Hash::make('discord-secret-local-pass'),
            ]
        );

        if (! $user->character) {
            Character::create([
                'user_id' => $user->id,
                'name' => 'Discord_Rico',
                'title' => 'Discord Bendeleider',
                'avatar' => 'characters/avatar_male_1.jpg',
                'bio' => 'Geverifieerd via de officiële TBS Kliniek Discord server.',
                'status_message' => 'Actief in TBS Kliniek via Discord!',
                'level' => 3,
                'xp' => 45,
                'total_xp' => 45,
                'cash' => 2500,
                'bank' => 10000,
                'energy' => 100,
                'max_energy' => 100,
                'health' => 100,
                'max_health' => 100,
                'respect' => 45,
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welkom! Je bent succesvol ingelogd via je Discord account!');
    }

    /**
     * Check and consume the Discord login state that this browser session stored.
     */
    private function isValidLoginState(Request $request, string $state): bool
    {
        $expectedState = (string) $request->session()->pull('oauth2state', '');

        return $expectedState !== '' && hash_equals($expectedState, $state);
    }

    /**
     * Discord OAuth callback handler.
     */
    public function discordCallback(Request $request): Response|RedirectResponse
    {
        $clientId = DiscordShieldSetting::get('client_id') ?: config('services.discord.client_id');
        $clientSecret = DiscordShieldSetting::get('client_secret') ?: config('services.discord.client_secret');
        $code = $request->query('code');
        $redirectUri = 'https://tbs-kliniek.nl/discord/callback';

        // The state must match the value stored for this browser session. Without this check, an attacker can
        // send a victim a link that logs the victim into the attacker's Discord account (login CSRF).
        $state = (string) $request->query('state');
        if (str_starts_with($state, 'shield_')) {
            $expectedShieldState = (string) $request->session()->pull('discord_shield_state', '');
            if ($expectedShieldState === '' || ! hash_equals($expectedShieldState, $state)) {
                return redirect()->route('discord.verify')->with('error', 'Beveiligingsfout: Ongeldige sessie state. Probeer het opnieuw.');
            }
        } elseif (! $this->isValidLoginState($request, $state)) {
            return redirect()->route('login')->withErrors(['email' => 'Beveiligingsfout: Ongeldige sessie state. Probeer het opnieuw.']);
        }

        if ($clientId && $clientSecret && $code) {
            try {
                $response = Http::asForm()->timeout(10)->post('https://discord.com/api/oauth2/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ]);

                if ($response->successful()) {
                    $tokenData = $response->json();
                    $accessToken = $tokenData['access_token'] ?? null;

                    if ($accessToken) {
                        $userResponse = Http::withToken($accessToken)->timeout(10)->get('https://discord.com/api/users/@me');

                        if ($userResponse->successful()) {
                            $discordUser = $userResponse->json();

                            // If this request came from the Discord Shield verification portal
                            if (str_starts_with((string) $request->query('state'), 'shield_')) {
                                session([
                                    'discord_shield_user' => [
                                        'id' => (string) $discordUser['id'],
                                        'username' => $discordUser['username'],
                                        'global_name' => $discordUser['global_name'] ?? $discordUser['username'],
                                        'avatar' => $discordUser['avatar'] ?? null,
                                        'email' => $discordUser['email'] ?? null,
                                    ],
                                    'discord_shield_access_token' => $accessToken,
                                ]);

                                return redirect()->route('discord.verify')->with('success', 'Discord account gekoppeld! Voltooi nu de korte beveiligingscheck.');
                            }

                            // A generated address is not proof of ownership: a matching account could belong to anyone.
                            $discordId = $discordUser['id'] ?? null;
                            if (! is_string($discordId) || ! preg_match('/^[0-9]{1,20}$/', $discordId)) {
                                return redirect()->route('login')->withErrors(['email' => 'Discord heeft geen geldige accountidentiteit teruggegeven.']);
                            }

                            $linkedUser = User::where('discord_id', $discordId)->first();
                            $discordEmail = ($discordUser['verified'] ?? false) === true ? ($discordUser['email'] ?? null) : null;
                            if (! $linkedUser && (! is_string($discordEmail) || ! filter_var($discordEmail, FILTER_VALIDATE_EMAIL))) {
                                return redirect()->route('login')->withErrors(['email' => 'Bevestig eerst je e-mailadres bij Discord of koppel Discord aan een bestaand account.']);
                            }

                            $email = $linkedUser?->email ?? $discordEmail;
                            $rawName = $discordUser['global_name'] ?? $discordUser['username'] ?? 'Discord Speler';
                            $cleanUsername = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($discordUser['username'] ?? 'inmate')));
                            if (strlen($cleanUsername) < 3) {
                                $cleanUsername = 'inmate_'.rand(100, 999);
                            }

                            $isNew = ! User::where('email', $email)->exists();

                            if ($isNew) {
                                $clientIp = $request->ip();

                                $vpnCheck = app(VpnDetectionService::class)->check($clientIp);
                                if ($vpnCheck['is_vpn']) {
                                    return redirect()->route('login')->withErrors(['email' => 'Het gebruik van een VPN of proxy is niet toegestaan tijdens registratie. Schakel je VPN uit en probeer het opnieuw.']);
                                }

                                $registeredCount = User::where('registration_ip', $clientIp)
                                    ->where('ip_limit_exempt', false)
                                    ->count();

                                if ($registeredCount < 2) {
                                    $legacyUserIds = DB::table('sessions')
                                        ->where('ip_address', $clientIp)
                                        ->whereNotNull('user_id')
                                        ->pluck('user_id')
                                        ->unique();

                                    if ($legacyUserIds->isNotEmpty()) {
                                        $registeredCount += User::whereIn('id', $legacyUserIds)
                                            ->where('ip_limit_exempt', false)
                                            ->whereNull('registration_ip')
                                            ->count();
                                    }
                                }

                                if ($registeredCount >= 2) {
                                    return redirect()->route('login')->withErrors(['email' => 'Er zijn al maximaal 2 accounts geregistreerd vanaf dit IP-adres (toegestaan voor bijv. gezinsleden). Wil je een uitzondering aanvragen voor een extra gezinslid? Maak dan een ticket aan via support.']);
                                }
                            }

                            $user = $linkedUser ?? User::firstOrCreate(
                                ['email' => $email],
                                [
                                    'name' => $cleanUsername,
                                    'password' => Hash::make(Str::random(32)),
                                    'registration_ip' => $request->ip(),
                                    'last_login_ip' => $request->ip(),
                                    'email_verified_at' => now(),
                                    'discord_id' => $discordId,
                                    'is_onboarded' => false,
                                ]
                            );

                            if ($user->discord_id && $user->discord_id !== $discordId) {
                                return redirect()->route('login')->withErrors(['email' => 'Dit account is al gekoppeld aan een ander Discord-account.']);
                            }

                            if (! $isNew) {
                                $user->forceFill(['last_login_ip' => $request->ip()])->saveQuietly();
                            }

                            if (! $user->character) {
                                $candidateUsername = $cleanUsername;
                                $counter = 1;
                                while (Character::where('username', $candidateUsername)->exists()) {
                                    $candidateUsername = substr($cleanUsername, 0, 15).'_'.$counter;
                                    $counter++;
                                }

                                $newChar = Character::create([
                                    'user_id' => $user->id,
                                    'username' => $candidateUsername,
                                    'name' => $rawName,
                                    'display_name' => $rawName,
                                    'title' => 'Nieuwe Patiënt',
                                    'avatar' => 'assets/characters/avatar_male_1.jpg',
                                    'bio' => 'Aangemeld via Discord bij de TBS Kliniek.',
                                    'status_message' => 'Vers binnengebracht via Discord!',
                                    'level' => 1,
                                    'xp' => 0,
                                    'total_xp' => 0,
                                    'cash' => 500,
                                    'bank' => 2500,
                                    'energy' => 100,
                                    'max_energy' => 100,
                                    'health' => 100,
                                    'max_health' => 100,
                                    'respect' => 20,
                                    'is_onboarded' => false,
                                ]);

                                $refName = request()->session()->get('referral_name');
                                if ($refName) {
                                    app(ReferralService::class)->recordReferral($newChar, (string) $refName, request()->ip());
                                    request()->session()->forget('referral_name');
                                }
                            }

                            Auth::login($user, true);
                            request()->session()->regenerate();

                            if (! $user->is_onboarded || ! $user->character?->is_onboarded) {
                                return redirect()->route('onboarding.index')->with('success', 'Welkom via Discord! Maak nu jouw patiëntendossier compleet.');
                            }

                            return redirect()->route('dashboard')->with('success', 'Welkom! Je bent succesvol ingelogd via Discord.');
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Discord OAuth error: '.$e->getMessage());
            }

            return redirect()->route('login')->withErrors(['email' => 'Inloggen via Discord is mislukt. Probeer het opnieuw.']);
        }

        return $this->discordLogin($request);
    }

    /**
     * Handle login submission.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember', true))) {
            $request->session()->regenerate();

            /** @var User|null $loggedUser */
            $loggedUser = Auth::user();
            if ($loggedUser) {
                $loggedUser->forceFill(['last_login_ip' => $request->ip()])->saveQuietly();
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Onjuist e-mailadres of wachtwoord. Gerda heeft je login afgekeurd!',
        ])->onlyInput('email');
    }

    /**
     * Handle registration with onboarding avatar, gender, and terms.
     */
    public function register(Request $request): RedirectResponse
    {
        $clientIp = $request->ip();

        // 1. VPN / Proxy Blocker
        $vpnService = app(VpnDetectionService::class);
        $vpnCheck = $vpnService->check($clientIp);
        if ($vpnCheck['is_vpn']) {
            return back()->withErrors([
                'username' => 'Het gebruik van een VPN of proxy is niet toegestaan tijdens registratie. Schakel je VPN even helemaal uit op je computer of telefoon en probeer het daarna opnieuw.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // 2. Max 2 accounts per IP limit
        $registeredCount = User::where('registration_ip', $clientIp)
            ->where('ip_limit_exempt', false)
            ->count();

        if ($registeredCount < 2) {
            $legacyUserIds = DB::table('sessions')
                ->where('ip_address', $clientIp)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->unique();

            if ($legacyUserIds->isNotEmpty()) {
                $registeredCount += User::whereIn('id', $legacyUserIds)
                    ->where('ip_limit_exempt', false)
                    ->whereNull('registration_ip')
                    ->count();
            }
        }

        if ($registeredCount >= 2) {
            return back()->withErrors([
                'username' => 'Er zijn al maximaal 2 accounts geregistreerd vanaf dit IP-adres (toegestaan voor bijv. gezinsleden). Wil je een uitzondering aanvragen voor een extra gezinslid? Maak dan een ticket aan via support.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        $username = $request->input('username') ?? $request->input('character_name') ?? $request->input('name');
        $request->merge(['username' => $username, 'name' => $username]);

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:25', 'unique:characters,name', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'avatar' => ['nullable', 'string'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Je moet akkoord gaan met de algemene voorwaarden en het kliniekreglement.',
            'username.required' => 'Vul een gebruikersnaam in.',
            'username.unique' => 'Deze gebruikersnaam is al bezet in de kliniek.',
            'username.min' => 'Je gebruikersnaam moet minimaal 3 tekens zijn.',
            'email.unique' => 'Dit e-mailadres is al geregistreerd.',
            'password.min' => 'Het wachtwoord moet minimaal 6 tekens bevatten.',
            'password.confirmed' => 'De wachtwoorden komen niet overeen.',
        ]);

        $gender = $validated['gender'] ?? 'male';

        $allowedAvatars = [
            'assets/characters/avatar_male_1.jpg',
            'assets/characters/avatar_male_2.jpg',
            'assets/characters/avatar_male_3.jpg',
            'assets/characters/avatar_female_1.jpg',
            'assets/characters/avatar_female_2.jpg',
            'assets/characters/avatar_female_3.jpg',
            'assets/characters/henkie.jpg',
        ];

        $avatarInput = $validated['avatar'] ?? null;
        if ($avatarInput && ! in_array($avatarInput, $allowedAvatars, true)) {
            if (in_array('assets/'.ltrim($avatarInput, '/'), $allowedAvatars, true)) {
                $avatarInput = 'assets/'.ltrim($avatarInput, '/');
            } else {
                $avatarInput = null;
            }
        }

        $avatar = $avatarInput ?? ($gender === 'female' ? 'assets/characters/avatar_female_1.jpg' : 'assets/characters/avatar_male_1.jpg');

        [$user, $character] = DB::transaction(function () use ($validated, $clientIp, $gender, $avatar): array {
            $user = User::create([
                'name' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'registration_ip' => $clientIp,
                'last_login_ip' => $clientIp,
            ]);

            $character = Character::create([
                'user_id' => $user->id,
                'name' => $validated['username'],
                'title' => $gender === 'female' ? 'Nieuwe Patiënte' : 'Nieuwe Patiënt',
                'avatar' => $avatar,
                'gender' => $gender,
                'bio' => 'Pas binnengebracht via transport. Klaar om naam te maken.',
                'status_message' => 'Rondje maken over de afdeling...',
                'level' => 1,
                'xp' => 0,
                'total_xp' => 0,
                'cash' => 250,
                'bank' => 1000,
                'energy' => 100,
                'max_energy' => 100,
                'health' => 100,
                'max_health' => 100,
                'respect' => 10,
            ]);

            return [$user, $character];
        });

        $refName = $request->input('ref') ?: $request->session()->get('referral_name');
        $isOohpaPromo = false;
        if ($refName) {
            $normalizedRef = strtolower(trim((string) $refName));
            if (in_array($normalizedRef, ['oohpaarjan', 'oohpa', 'arjan', 'oohpa_arjan', 'oohpa-arjan'])) {
                $isOohpaPromo = true;
                $character->is_vip = true;
                $currentExpiry = ($character->vip_expires_at && $character->vip_expires_at->isFuture())
                    ? $character->vip_expires_at
                    : now();
                $character->vip_expires_at = $currentExpiry->addDays(3);
                $character->vip_points += 50;
                $character->cash += 5000;
                $character->save();

                try {
                    GameMessage::create([
                        'sender_id' => null,
                        'recipient_id' => $character->id,
                        'sender_name' => 'OOHPA Arjan & Directie',
                        'subject' => '🎁 3 Dagen Gratis VIP Geactiveerd via OOHPA Arjan!',
                        'content' => "Welkom in TBS Kliniek, {$character->name}!\n\nJe bent binnengebracht via de actie van OOHPA Arjan. Jouw 3 Dagen Gratis VIP status is per direct geactiveerd op jouw account (geldig tot ".$character->vip_expires_at->format('d-m-Y H:i').")!\n\nAls VIP geniet je van:\n- Tot 1.000 aanvallen per dag (i.p.v. 100)\n- 2x dagelijkse rad-spins\n- Exclusieve VIP chat sticker & diamanten\n- Snellere energie herlading\n\nSucces achter de tralies,\nDe Directie",
                        'type' => 'reward',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to create OOHPA welcome message: '.$e->getMessage());
                }
            } else {
                app(ReferralService::class)->recordReferral($character, (string) $refName, $request->ip());
            }
            $request->session()->forget('referral_name');
        }

        // Auto-link verified Discord and award Starter Bonus (€2.500 Cash + 3 Days VIP + Roles)
        $verifiedDiscordId = $request->session()->get('verified_discord_id');
        if ($verifiedDiscordId && ! User::where('discord_id', $verifiedDiscordId)->exists()) {
            $user->discord_id = $verifiedDiscordId;
            $user->save();
            DiscordVerification::where('discord_id', $verifiedDiscordId)
                ->update([
                    'user_id' => $user->id,
                    'character_id' => $character->id,
                ]);
            $character->claimDiscordReward();
            $request->session()->forget(['verified_discord_id', 'verified_discord_username']);

            $shieldService = app(DiscordShieldService::class);
            $guildId = $shieldService->getGuildId();
            if (! empty($guildId)) {
                $shieldService->syncGameRoles($guildId, $verifiedDiscordId, $character);
            }
        } elseif ($verifiedDiscordId) {
            $request->session()->forget(['verified_discord_id', 'verified_discord_username']);
            $verifiedDiscordId = null;
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        event(new Registered($user));

        $welcomeMsg = $isOohpaPromo
            ? 'Welkom in TBS Kliniek, '.$validated['username'].'! 🎉 Jouw 3 Dagen Gratis VIP via OOHPA Arjan is direct geactiveerd! Bevestig nog even je e-mailadres om direct te spelen.'
            : ($verifiedDiscordId
                ? 'Welkom in TBS Kliniek, '.$validated['username'].'! Je Discord is direct gekoppeld: je hebt €2.500 startbonus + 3 dagen gratis VIP ontvangen! Bevestig nog even je e-mailadres.'
                : 'Welkom in TBS Kliniek, '.$validated['username'].'! Bevestig je e-mailadres via de verzonden e-mail om te kunnen spelen.');

        return redirect()->route('verification.notice')->with('success', $welcomeMsg);
    }

    /**
     * Show forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link email.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => 'Er is een veilige wachtwoord herstellink verzonden naar je e-mailadres!'])
            : back()->withErrors(['email' => 'Geen account gevonden met dit e-mailadres.']);
    }

    /**
     * Show reset password form with token.
     */
    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Wachtwoord succesvol opnieuw ingesteld! Je kunt nu inloggen.')
            : back()->withErrors(['email' => 'Het wachtwoord kon niet worden hersteld. De link is mogelijk verlopen.']);
    }

    /**
     * Show email verification notice with 6-digit code input.
     */
    public function verifyNotice(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // If user has no code yet or code has expired, send a fresh code
        if (empty($user->email_verification_code) || ! $user->email_verification_code_expires_at || $user->email_verification_code_expires_at->isPast()) {
            $user->sendEmailVerificationNotification();
            $user->refresh();
        }

        return view('auth.verify-email', [
            'user' => $user,
            'expiresAt' => $user->email_verification_code_expires_at,
        ]);
    }

    /**
     * Handle incoming 6-digit email verification code.
     */
    public function verifyWithCode(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'code.required' => 'Vul de 6-cijferige verificatiecode in.',
            'code.regex' => 'De verificatiecode moet precies 6 cijfers bevatten.',
        ]);

        $inputCode = trim($validated['code']);

        // Check if code matches
        if ($user->email_verification_code !== $inputCode) {
            return back()->withErrors(['code' => 'De ingevoerde verificatiecode is onjuist. Controleer je e-mail of vraag een nieuwe code aan.'])->withInput();
        }

        // Check if expired after 30 minutes
        if (! $user->email_verification_code_expires_at || $user->email_verification_code_expires_at->isPast()) {
            return back()->withErrors(['code' => 'Deze verificatiecode is na 30 minuten verlopen. Vraag hieronder een nieuwe code aan.'])->withInput();
        }

        $user->markEmailAsVerified();
        $user->forceFill([
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ])->saveQuietly();

        event(new Verified($user));

        if (! $user->is_onboarded || ! $user->character?->is_onboarded) {
            return redirect()->route('onboarding.index')->with('success', 'E-mailadres succesvol geverifieerd! Welkom in TBS Kliniek.');
        }

        return redirect()->route('dashboard')->with('success', 'E-mailadres succesvol geverifieerd! Welkom in TBS Kliniek.');
    }

    /**
     * Handle incoming email verification request via link (legacy fallback).
     */
    public function verifyEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (int) $user->id !== (int) $id) {
            return redirect()->route('login')->with('error', 'Ongeldige verificatielink.');
        }

        if (! $request->hasValidSignature() || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Ongeldige verificatielink.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->forceFill([
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null,
            ])->saveQuietly();

            event(new Verified($user));
        }

        return redirect()->route('dashboard')->with('success', 'Gefeliciteerd! Je e-mailadres is succesvol geverifieerd. Welkom in TBS Kliniek!');
    }

    /**
     * Resend verification notification with new 6-digit code.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-code-sent');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user?->character && $request->session()->has('pending_arena_battle')) {
            app(FightController::class)->commitPendingBattle($user->character);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Je bent succesvol uitgelogd.');
    }
}

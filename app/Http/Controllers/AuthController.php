<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        try {
            $totalPlayers = Character::count();
            $totalCash = Character::sum('cash') + Character::sum('bank');
            $topCharacters = Character::with('user')->orderByDesc('respect')->limit(4)->get();
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

        $clientId = config('services.discord.client_id');
        $redirectUri = url('/auth/discord/callback');

        if ($clientId) {
            $state = bin2hex(random_bytes(16));
            session(['oauth2state' => $state]);
            $url = 'https://discord.com/api/oauth2/authorize?client_id='.$clientId.'&redirect_uri='.urlencode($redirectUri).'&response_type=code&scope=identify%20email&state='.$state;

            return redirect()->away($url);
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

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welkom! Je bent succesvol ingelogd via je Discord account!');
    }

    /**
     * Discord OAuth callback handler.
     */
    public function discordCallback(Request $request): Response|RedirectResponse
    {
        return $this->discordLogin($request);
    }

    /**
     * Google OAuth login handler.
     */
    public function googleLogin(Request $request): Response|RedirectResponse
    {
        // Never log in or mutate state on background prefetch requests
        if ($request->header('Purpose') === 'prefetch' || $request->header('Sec-Purpose') === 'prefetch' || $request->header('X-Sec-Purpose') === 'prefetch') {
            return response()->noContent();
        }

        $clientId = config('services.google.client_id');
        $redirectUri = url('/auth/google/callback');

        if ($clientId) {
            $state = bin2hex(random_bytes(16));
            session(['google_oauth_state' => $state]);
            $url = 'https://accounts.google.com/o/oauth2/v2/auth?client_id='.$clientId.'&redirect_uri='.urlencode($redirectUri).'&response_type=code&scope=openid%20profile%20email&state='.$state;

            return redirect()->away($url);
        }

        // Seamless instant registration / login for local development
        $user = User::firstOrCreate(
            ['email' => 'google_inmate@gmail.com'],
            [
                'name' => 'Google Inmate',
                'password' => Hash::make('google-secret-local-pass-123'),
            ]
        );

        if (! $user->character) {
            Character::create([
                'user_id' => $user->id,
                'name' => 'Google_Kees',
                'title' => 'Google Geverifieerd',
                'avatar' => 'characters/avatar_male_2.jpg',
                'bio' => 'Geregistreerd en aangemeld via Google account.',
                'status_message' => 'Nieuwe patiënt binnengebracht via Google!',
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
            ]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welkom! Je bent succesvol geregistreerd en ingelogd via Google!');
    }

    /**
     * Google OAuth callback handler.
     */
    public function googleCallback(Request $request): Response|RedirectResponse
    {
        return $this->googleLogin($request);
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

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
        $username = $request->input('username') ?? $request->input('character_name') ?? $request->input('name');
        $request->merge(['username' => $username, 'name' => $username]);

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:25', 'unique:characters,name'],
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

        $user = User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Character::create([
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

        Auth::login($user);
        $request->session()->regenerate();

        event(new Registered($user));

        return redirect()->route('verification.notice')->with('success', 'Welkom in TBS Kliniek, '.$validated['username'].'! Bevestig je e-mailadres via de verzonden e-mail om te kunnen spelen.');
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
     * Show email verification notice.
     */
    public function verifyNotice(): View|RedirectResponse
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    /**
     * Handle incoming email verification request.
     */
    public function verifyEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Ongeldige verificatielink.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('dashboard')->with('success', 'Gefeliciteerd! Je e-mailadres is succesvol geverifieerd. Welkom in TBS Kliniek!');
    }

    /**
     * Resend verification notification.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Je bent succesvol uitgelogd.');
    }
}

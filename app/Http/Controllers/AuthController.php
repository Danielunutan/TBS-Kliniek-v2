<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login / register landing page.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
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
     * Quick demo login button for localhost evaluation.
     */
    public function demoLogin(): RedirectResponse
    {
        $demoUser = User::where('email', 'demo@tbskliniek.nl')->first();

        if ($demoUser) {
            Auth::login($demoUser);
            request()->session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'Welkom terug in de kliniek, Kees! Tijd om de kassa te lichten.');
        }

        return redirect()->route('login')->with('error', 'Demo account niet gevonden.');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'character_name' => ['required', 'string', 'min:3', 'max:25', 'unique:characters,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Character::create([
            'user_id' => $user->id,
            'name' => $validated['character_name'],
            'title' => 'Beginnend Patiënt',
            'avatar' => 'characters/avatar-default.png',
            'bio' => 'Nieuw binnengebracht via transport. Klaar om naam te maken.',
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

        return redirect()->route('dashboard')->with('success', 'Inschrijving voltooid! Welkom in TBS Kliniek.');
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

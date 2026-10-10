<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    /**
     * Show account settings page.
     */
    public function index(): View
    {
        $user = Auth::user();
        $character = $user->character;

        // Check 6-month username change restriction
        $canChangeUsername = true;
        $daysUntilChange = 0;

        if ($character->username_changed_at) {
            $nextAllowed = $character->username_changed_at->addMonths(6);
            if ($nextAllowed->isFuture()) {
                $canChangeUsername = false;
                $daysUntilChange = (int) now()->diffInDays($nextAllowed);
            }
        }

        // Fetch active database sessions for this user
        $sessions = collect();
        if (config('session.driver') === 'database') {
            $currentSessionId = session()->getId();
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($s) use ($currentSessionId) {
                    $isCurrent = $s->id === $currentSessionId;
                    $agent = $s->user_agent ?? 'Onbekend apparaat';
                    $device = 'Desktop Browser';

                    if (stripos($agent, 'Mobile') !== false || stripos($agent, 'Android') !== false || stripos($agent, 'iPhone') !== false) {
                        $device = 'Mobiele Telefoon';
                    } elseif (stripos($agent, 'Tablet') !== false || stripos($agent, 'iPad') !== false) {
                        $device = 'Tablet';
                    }

                    return [
                        'id' => $s->id,
                        'is_current' => $isCurrent,
                        'device' => $device,
                        'ip' => $s->ip_address,
                        'user_agent' => Str::limit($agent, 60),
                        'last_active' => Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                    ];
                });
        }

        return view('game.settings.account', compact(
            'user',
            'character',
            'canChangeUsername',
            'daysUntilChange',
            'sessions'
        ));
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'Je huidige wachtwoord is onjuist.');
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('success', 'Wachtwoord is succesvol gewijzigd!');
    }

    /**
     * Update character username (enforced 1x per 6 months).
     */
    public function updateUsername(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->username_changed_at) {
            $nextAllowed = $character->username_changed_at->addMonths(6);
            if ($nextAllowed->isFuture()) {
                $days = (int) now()->diffInDays($nextAllowed);

                return back()->with('error', "Je mag je gebruikersnaam pas weer over {$days} dagen wijzigen (max 1x per 6 maanden)!");
            }
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:25',
                'regex:/^[a-zA-Z0-9_\-\s]+$/',
                'unique:characters,name,'.$character->id,
            ],
        ]);

        $oldName = $character->name;
        $character->name = trim($validated['name']);
        $character->username_changed_at = now();
        $character->save();

        return back()->with('success', "Gebruikersnaam gewijzigd van '{$oldName}' naar '{$character->name}'! Volgende wijziging mogelijk over 6 maanden.");
    }

    /**
     * Toggle 2FA authentication state.
     */
    public function toggleTwoFactor(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            $user->two_factor_enabled = false;
            $user->two_factor_secret = null;
            $user->save();

            return back()->with('success', 'Twee-factor-authenticatie (2FA) is uitgeschakeld.');
        }

        $user->two_factor_enabled = true;
        if (! $user->two_factor_secret) {
            $user->two_factor_secret = Str::upper(Str::random(16));
        }
        $user->save();

        return back()->with('success', 'Twee-factor-authenticatie (2FA) is ingeschakeld op je account!');
    }

    /**
     * Logout other sessions / remote devices.
     */
    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! Hash::check($validated['password'], $user->password)) {
            return back()->with('error', 'Wachtwoord onjuist. Vul je wachtwoord in om andere apparaten uit te loggen.');
        }

        try {
            Auth::logoutOtherDevices($validated['password']);

            // Purge other sessions in database
            if (config('session.driver') === 'database') {
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->where('id', '!=', session()->getId())
                    ->delete();
            }

            return back()->with('success', 'Alle andere actieve sessies en apparaten zijn succesvol uitgelogd!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Er is een fout opgetreden bij het uitloggen van andere apparaten.');
        }
    }
}

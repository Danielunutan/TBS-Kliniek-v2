<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
     * Update character username and display name.
     */
    public function updateUsername(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;
        $isUsernameLocked = false;
        $daysUntilChange = 0;

        if ($character->username_changed_at) {
            $nextAllowed = $character->username_changed_at->addMonths(6);
            if ($nextAllowed->isFuture()) {
                $isUsernameLocked = true;
                $daysUntilChange = (int) now()->diffInDays($nextAllowed);
            }
        }

        if (! $request->has('display_name') && $request->has('name')) {
            $request->merge(['display_name' => $request->input('name')]);
        }
        if (! $request->has('username') && $request->has('name')) {
            $request->merge(['username' => $request->input('name')]);
        }

        $rules = [
            // characters.name has a unique index, so a taken name must fail validation instead of crashing with a database error.
            'display_name' => ['required', 'string', 'min:2', 'max:30', 'unique:characters,name,'.$character->id],
            'profile_color' => ['nullable', 'string', 'in:purple,rose,emerald,amber,blue,indigo,dark'],
        ];

        // Only validate username if it was submitted and not locked
        $newUsername = $request->input('username');
        $cleanUsername = $newUsername ? strtolower(trim((string) $newUsername)) : null;
        $wantsNewUsername = $cleanUsername && $cleanUsername !== $character->username;

        if ($wantsNewUsername && $isUsernameLocked) {
            return back()->with('error', "Je kunt je unieke @gebruikersnaam max 1x per 6 maanden aanpassen. Nog {$daysUntilChange} dagen resterend.");
        }

        if ($wantsNewUsername && ! $isUsernameLocked) {
            $rules['username'] = [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_\-]+$/',
                'unique:characters,username,'.$character->id,
            ];
        }

        $validated = $request->validate($rules, [
            'username.regex' => 'Gebruikersnaam mag GEEN spaties of vreemde tekens bevatten (alleen letters a-z, cijfers 0-9, underscores _ en streepjes -).',
            'display_name.required' => 'Vul een weergavenaam in.',
            'display_name.unique' => 'Deze weergavenaam is al in gebruik door een andere patiënt.',
        ]);

        $displayName = trim($validated['display_name']);
        $character->display_name = $displayName;
        $character->name = $displayName;

        if (! empty($validated['profile_color'])) {
            $character->profile_color = $validated['profile_color'];
        }

        $usernameChanged = false;
        if ($wantsNewUsername && ! $isUsernameLocked) {
            $character->username = $cleanUsername;
            $character->username_changed_at = now();
            Auth::user()->forceFill(['name' => $cleanUsername])->save();
            $usernameChanged = true;
        }

        $character->save();

        if ($usernameChanged) {
            return back()->with('success', 'Je gebruikersnaam, weergavenaam en profielkleur zijn succesvol bijgewerkt!');
        }

        return back()->with('success', 'Je weergavenaam en profielkleur zijn succesvol bijgewerkt!');
    }

    /**
     * Upload cropped avatar via settings.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $user = Auth::user();
        $character = $user->character;

        $request->validate([
            'image' => ['required', 'string'],
        ]);

        $imageData = $request->input('image');
        if (! preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $imageData, $matches)) {
            return response()->json(['success' => false, 'message' => 'Ongeldig afbeeldingsformaat.'], 422);
        }

        $data = substr($imageData, strpos($imageData, ',') + 1);
        $data = base64_decode($data);
        if ($data === false) {
            return response()->json(['success' => false, 'message' => 'Fout bij verwerken van foto.'], 422);
        }

        // Security check: limit max decoded size to 3 MB
        if (strlen($data) > 3 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'Afbeelding is te groot (maximaal 3 MB toegestaan).'], 422);
        }

        // Security check: verify genuine image binary payload
        $imageInfo = @getimagesizefromstring($data);
        if ($imageInfo === false || ! in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return response()->json(['success' => false, 'message' => 'Ongeldige afbeeldingsdata gedetecteerd.'], 422);
        }

        $ext = match ($imageInfo['mime']) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $filename = 'avatar_user_'.$user->id.'_'.time().'_'.Str::random(6).'.'.$ext;
        $uploadDir = public_path('uploads/avatars');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir.'/'.$filename;
        file_put_contents($filePath, $data);

        $relativePath = 'uploads/avatars/'.$filename;

        $character->avatar_custom = $relativePath;
        $character->avatar = $relativePath;
        $character->save();

        return response()->json([
            'success' => true,
            'url' => asset($relativePath),
            'message' => 'Profielfoto succesvol bijgesneden en opgeslagen!',
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Models\DiscordShieldBan;
use App\Models\DiscordShieldLog;
use App\Models\DiscordShieldSetting;
use App\Models\DiscordVerification;
use App\Models\User;
use App\Services\DiscordShieldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DiscordShieldAdminController extends Controller
{
    public function __construct(
        protected DiscordShieldService $discordService
    ) {}

    /**
     * Display the Discord Shield management dashboard.
     */
    public function index(): View
    {
        $totalVerified = DiscordVerification::where('status', 'verified')->count();
        $totalAltsCaught = DiscordShieldLog::where('event', 'alt_detected')->count();
        $totalVpnBlocked = DiscordShieldLog::where('event', 'vpn_blocked')->count();
        $totalBans = DiscordShieldBan::count();

        // Recent Verifications
        $verifications = DiscordVerification::with(['character', 'user'])
            ->latest()
            ->paginate(15, ['*'], 'verifications_page');

        // Security Logs
        $logs = DiscordShieldLog::latest()
            ->paginate(20, ['*'], 'logs_page');

        // Active Bans
        $bans = DiscordShieldBan::latest()->get();

        // Current Settings
        $settings = [
            'bot_token' => DiscordShieldSetting::get('bot_token', ''),
            'client_id' => DiscordShieldSetting::get('client_id', config('services.discord.client_id', '1558628995439005748')),
            'guild_id' => DiscordShieldSetting::get('guild_id', ''),
            'verified_role_id' => DiscordShieldSetting::get('verified_role_id', ''),
            'unverified_role_id' => DiscordShieldSetting::get('unverified_role_id', ''),
            'log_channel_id' => DiscordShieldSetting::get('log_channel_id', ''),
            'verify_channel_id' => DiscordShieldSetting::get('verify_channel_id', ''),
            'block_vpns' => DiscordShieldSetting::get('block_vpns', '1'),
            'block_alts' => DiscordShieldSetting::get('block_alts', '1'),
            'auto_kick_alts' => DiscordShieldSetting::get('auto_kick_alts', '1'),
            'auto_ban_evaders' => DiscordShieldSetting::get('auto_ban_evaders', '0'),
            'turnstile_enabled' => DiscordShieldSetting::get('turnstile_enabled', '1'),
            'turnstile_site_key' => DiscordShieldSetting::get('turnstile_site_key', env('TURNSTILE_SITE_KEY', '0x4AAAAAAFTb6Vv0yuFCIDYU')),
            'turnstile_secret_key' => DiscordShieldSetting::get('turnstile_secret_key', env('TURNSTILE_SECRET_KEY', '0x4AAAAAAFTb6acMyf3-LP-CdIYqWEH_Lo0')),
        ];

        return view('admin.discord_shield.index', compact(
            'totalVerified',
            'totalAltsCaught',
            'totalVpnBlocked',
            'totalBans',
            'verifications',
            'logs',
            'bans',
            'settings'
        ));
    }

    /**
     * Update Discord Shield settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->isOwner() && ! $currentUser->isAdmin()) {
            return back()->with('error', 'Alleen de eigenaar of een beheerder kan Discord Shield systeeminstellingen wijzigen.');
        }

        $validated = $request->validate([
            'bot_token' => 'nullable|string',
            'client_id' => 'nullable|string',
            'guild_id' => 'nullable|string',
            'verified_role_id' => 'nullable|string',
            'unverified_role_id' => 'nullable|string',
            'log_channel_id' => 'nullable|string',
            'verify_channel_id' => 'nullable|string',
            'block_vpns' => 'nullable|boolean',
            'block_alts' => 'nullable|boolean',
            'auto_kick_alts' => 'nullable|boolean',
            'auto_ban_evaders' => 'nullable|boolean',
            'turnstile_enabled' => 'nullable|boolean',
            'turnstile_site_key' => 'nullable|string',
            'turnstile_secret_key' => 'nullable|string',
        ]);

        foreach ($validated as $key => $val) {
            DiscordShieldSetting::set($key, $val);
        }

        // Toggles that might be missing if unchecked
        foreach (['block_vpns', 'block_alts', 'auto_kick_alts', 'auto_ban_evaders', 'turnstile_enabled'] as $toggle) {
            if (! isset($validated[$toggle])) {
                DiscordShieldSetting::set($toggle, '0');
            }
        }

        DiscordShieldLog::log('settings_updated', 'Discord Shield instellingen bijgewerkt door '.Auth::user()->name, [
            'severity' => 'info',
        ]);

        return back()->with('success', 'Discord Shield instellingen zijn succesvol opgeslagen.');
    }

    /**
     * Post the official verification button message to the specified Discord channel.
     */
    public function postVerificationMessage(Request $request): RedirectResponse
    {
        $channelId = $request->input('channel_id') ?? DiscordShieldSetting::get('verify_channel_id');
        if (empty($channelId)) {
            return back()->with('error', 'Vul eerst een geldig Verificatie Kanaal ID in.');
        }

        $msgId = $this->discordService->postVerificationChannelMessage($channelId);
        if ($msgId) {
            return back()->with('success', 'De officiële verificatie embed met knop is geplaatst in het Discord kanaal!');
        }

        return back()->with('error', 'Kon het bericht niet plaatsen. Controleer of de Bot Token klopt en of de bot rechten heeft in dat kanaal.');
    }

    /**
     * Manually add a ban (Discord ID, Fingerprint, or IP).
     */
    public function ban(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:discord_id,fingerprint,ip_address',
            'value' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        DiscordShieldBan::updateOrCreate(
            ['type' => $request->type, 'value' => trim($request->value)],
            [
                'reason' => $request->reason ?? 'Handmatige ban via staff dashboard',
                'banned_by' => Auth::user()->name,
            ]
        );

        // If it's a discord_id, also ban them in the guild if bot is connected
        if ($request->type === 'discord_id') {
            $guildId = $this->discordService->getGuildId();
            if ($guildId) {
                $this->discordService->banMember($guildId, trim($request->value), $request->reason ?? 'Handmatige ban via TBS Shield');
            }
        }

        DiscordShieldLog::log('manual_ban', "Handmatige ban toegevoegd voor {$request->type}: {$request->value}", [
            'severity' => 'warning',
        ]);

        return back()->with('success', 'Zwarte lijst ban is succesvol toegevoegd.');
    }

    /**
     * Remove a ban.
     */
    public function unban(int $id): RedirectResponse
    {
        $ban = DiscordShieldBan::findOrFail($id);
        $val = $ban->value;
        $ban->delete();

        DiscordShieldLog::log('manual_unban', "Ban verwijderd voor {$val}", [
            'severity' => 'info',
        ]);

        return back()->with('success', "Ban voor {$val} is opgeheven.");
    }

    /**
     * Manually approve an account that was previously blocked or flagged.
     */
    public function approve(int $id): RedirectResponse
    {
        $verification = DiscordVerification::findOrFail($id);
        $verification->update([
            'status' => 'verified',
            'risk_score' => 0,
            'verified_at' => now(),
        ]);

        // Give role
        $guildId = $this->discordService->getGuildId();
        $roleId = DiscordShieldSetting::get('verified_role_id');
        if ($guildId && $roleId) {
            $this->discordService->addRole($guildId, $verification->discord_id, $roleId, 'Handmatig goedgekeurd door staff');
        }

        DiscordShieldLog::log('manual_approval', "Gebruiker {$verification->username} ({$verification->discord_id}) handmatig goedgekeurd door ".Auth::user()->name, [
            'discord_id' => $verification->discord_id,
            'username' => $verification->username,
            'severity' => 'success',
        ]);

        return back()->with('success', "Gebruiker {$verification->username} is handmatig goedgekeurd en heeft de Discord rol ontvangen!");
    }
}

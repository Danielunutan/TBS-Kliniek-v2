<?php

namespace App\Http\Controllers;

use App\Models\DiscordShieldBan;
use App\Models\DiscordShieldLog;
use App\Models\DiscordShieldSetting;
use App\Models\DiscordVerification;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\DiscordShieldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd. Deze beheeromgeving is uitsluitend toegankelijk voor TBS Kliniek personeel.');

        AuditLogService::sanitizeDiscordShieldLogs();

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

        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $canManageSettings = $currentUser && ($currentUser->isOwner() || $currentUser->isAdmin());

        // Current Settings (mask secrets for moderators who cannot edit system settings)
        $settings = [
            'bot_token' => $canManageSettings ? DiscordShieldSetting::get('bot_token', '') : '',
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
            'turnstile_secret_key' => $canManageSettings ? DiscordShieldSetting::get('turnstile_secret_key', env('TURNSTILE_SECRET_KEY')) : '',
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
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser || (! $currentUser->isOwner() && ! $currentUser->isAdmin())) {
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
            'webhook_url' => 'nullable|url',
            'block_vpns' => 'nullable|boolean',
            'block_alts' => 'nullable|boolean',
            'auto_kick_alts' => 'nullable|boolean',
            'auto_ban_evaders' => 'nullable|boolean',
            'turnstile_enabled' => 'nullable|boolean',
            'turnstile_site_key' => 'nullable|string',
            'turnstile_secret_key' => 'nullable|string',
        ]);

        if (! empty($validated['webhook_url'])) {
            $parsedUrl = parse_url($validated['webhook_url']);
            $host = strtolower($parsedUrl['host'] ?? '');
            if (! in_array($host, ['discord.com', 'discordapp.com'], true) || ! str_starts_with($parsedUrl['path'] ?? '', '/api/webhooks/')) {
                return back()->with('error', 'Ongeldige Discord Webhook URL. De URL moet beginnen met https://discord.com/api/webhooks/')->withInput();
            }
        }

        try {
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
        } catch (\Throwable $e) {
            Log::error('Failed to update Discord Shield settings: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het opslaan van de instellingen: '.$e->getMessage())->withInput();
        }

        return back()->with('success', 'Discord Shield instellingen zijn succesvol opgeslagen.');
    }

    /**
     * Post the official verification button message to the specified Discord channel.
     */
    public function postVerificationMessage(Request $request): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser || (! $currentUser->isOwner() && ! $currentUser->isAdmin())) {
            return back()->with('error', 'Alleen de eigenaar of een beheerder kan het verificatiebericht plaatsen.');
        }

        $channelId = $request->input('channel_id') ?? DiscordShieldSetting::get('verify_channel_id');
        if (empty($channelId) || ! ctype_digit(trim((string) $channelId))) {
            return back()->with('error', 'Vul eerst een geldig numeriek Verificatie Kanaal ID in.');
        }

        try {
            $msgId = $this->discordService->postVerificationChannelMessage(trim((string) $channelId));
            if ($msgId) {
                return back()->with('success', 'De officiële verificatie embed met knop is geplaatst in het Discord kanaal!');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to post Discord verification message: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Fout bij het plaatsen van verificatiebericht: '.$e->getMessage());
        }

        return back()->with('error', 'Kon het bericht niet plaatsen. Controleer of de Bot Token klopt en of de bot rechten heeft in dat kanaal.');
    }

    /**
     * Manually add a ban (Discord ID, Fingerprint, or IP).
     */
    public function ban(Request $request): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser || (! $currentUser->isOwner() && ! $currentUser->isAdmin())) {
            return back()->with('error', 'Alleen de eigenaar of een beheerder kan een Discord Shield ban toevoegen.');
        }

        $request->validate([
            'type' => 'required|in:discord_id,fingerprint,ip_address',
            'value' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $val = trim($request->value);

        if ($request->type === 'ip_address' && ! filter_var($val, FILTER_VALIDATE_IP)) {
            return back()->with('error', 'Ongeldig IP-adres opgegeven voor zwarte lijst.');
        }

        if ($request->type === 'discord_id' && (! ctype_digit($val) || strlen($val) < 15 || strlen($val) > 22)) {
            return back()->with('error', 'Ongeldig Discord ID (moet een numeriek snowflake ID zijn).');
        }

        if ($request->type === 'fingerprint' && strlen($val) < 16) {
            return back()->with('error', 'Ongeldige hardware fingerprint hash (moet minimaal 16 tekens zijn).');
        }

        try {
            DiscordShieldBan::updateOrCreate(
                ['type' => $request->type, 'value' => $val],
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
                'discord_id' => $request->type === 'discord_id' ? trim((string) $request->value) : null,
                'ip_address' => $request->type === 'ip_address' ? trim((string) $request->value) : null,
                'fingerprint_hash' => $request->type === 'fingerprint' ? trim((string) $request->value) : null,
                'severity' => 'warning',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to add Discord Shield ban: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het toevoegen van de ban: '.$e->getMessage());
        }

        $adminName = $currentUser?->name ?? 'Staff';

        try {
            AuditLogService::log(
                action: 'discord_shield_ban_add',
                category: 'moderation',
                description: "Discord Shield ban toegevoegd voor {$request->type}: {$request->value} door {$adminName}",
                character: $currentUser?->character,
                user: $currentUser,
                details: [
                    'type' => $request->type,
                    'value' => trim($request->value),
                    'reason' => $request->reason ?? 'Handmatige ban via staff dashboard',
                    'banned_by' => $adminName,
                ],
                customRisk: 'hoog',
                customReason: "Discord Shield zwarte lijst toevoeging ({$request->type})"
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log discord_shield_ban_add audit record: '.$e->getMessage());
        }

        return back()->with('success', 'Zwarte lijst ban is succesvol toegevoegd.');
    }

    /**
     * Remove a ban.
     */
    public function unban(int $id): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser || (! $currentUser->isOwner() && ! $currentUser->isAdmin())) {
            return back()->with('error', 'Alleen de eigenaar of een beheerder kan een Discord Shield ban opheffen.');
        }

        try {
            $ban = DiscordShieldBan::findOrFail($id);
            $banType = $ban->type;
            $val = $ban->value;
            $ban->delete();

            DiscordShieldLog::log('manual_unban', "Ban verwijderd voor {$val}", [
                'discord_id' => $banType === 'discord_id' ? $val : null,
                'ip_address' => $banType === 'ip_address' ? $val : null,
                'fingerprint_hash' => $banType === 'fingerprint' ? $val : null,
                'severity' => 'info',
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to unban Discord Shield record #{$id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het opheffen van de ban: '.$e->getMessage());
        }

        $adminName = $currentUser?->name ?? 'Staff';

        try {
            AuditLogService::log(
                action: 'discord_shield_ban_remove',
                category: 'moderation',
                description: "Discord Shield ban opgeheven voor {$val} door {$adminName}",
                character: $currentUser?->character,
                user: $currentUser,
                details: [
                    'ban_id' => $id,
                    'value' => $val,
                    'unbanned_by' => $adminName,
                ],
                customRisk: 'verdacht',
                customReason: 'Discord Shield zwarte lijst ban verwijderd'
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log discord_shield_ban_remove audit record: '.$e->getMessage());
        }

        return back()->with('success', "Ban voor {$val} is opgeheven.");
    }

    /**
     * Manually approve an account that was previously blocked or flagged.
     */
    public function approve(int $id): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser || (! $currentUser->isOwner() && ! $currentUser->isAdmin())) {
            return back()->with('error', 'Alleen de eigenaar of een beheerder kan een Discord Shield verificatie goedkeuren.');
        }

        try {
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
        } catch (\Throwable $e) {
            Log::error("Failed to approve Discord verification #{$id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het goedkeuren: '.$e->getMessage());
        }

        $adminName = $currentUser?->name ?? 'Staff';

        DiscordShieldLog::log('manual_approval', "Gebruiker {$verification->username} ({$verification->discord_id}) handmatig goedgekeurd door ".$adminName, [
            'discord_id' => $verification->discord_id,
            'username' => $verification->username,
            'ip_address' => $verification->ip_address,
            'fingerprint_hash' => $verification->fingerprint_hash,
            'severity' => 'success',
        ]);

        try {
            AuditLogService::log(
                action: 'discord_shield_manual_approval',
                category: 'moderation',
                description: "Discord verificatie voor {$verification->username} ({$verification->discord_id}) handmatig goedgekeurd door {$adminName}",
                character: $verification->character,
                user: $verification->user,
                details: [
                    'verification_id' => $verification->id,
                    'discord_id' => $verification->discord_id,
                    'username' => $verification->username,
                    'approved_by' => $adminName,
                ],
                customRisk: 'verdacht',
                customReason: 'Handmatige goedkeuring van Discord verificatie'
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log discord_shield_manual_approval audit record: '.$e->getMessage());
        }

        return back()->with('success', "Gebruiker {$verification->username} is handmatig goedgekeurd en heeft de Discord rol ontvangen!");
    }
}

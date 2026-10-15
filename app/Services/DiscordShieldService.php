<?php

namespace App\Services;

use App\Models\Character;
use App\Models\DiscordShieldSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordShieldService
{
    protected string $baseUrl = 'https://discord.com/api/v10';

    /**
     * Get the configured Bot Token.
     */
    public function getBotToken(): string
    {
        return (string) DiscordShieldSetting::get('bot_token', '');
    }

    /**
     * Get the configured Guild (Server) ID.
     */
    public function getGuildId(): string
    {
        return (string) DiscordShieldSetting::get('guild_id', '');
    }

    /**
     * Send an authenticated Discord REST request.
     */
    protected function request(string $method, string $endpoint, array $data = [], array $headers = [])
    {
        $token = $this->getBotToken();
        if (empty($token)) {
            Log::warning('DiscordShieldService: Geen Discord Bot Token ingesteld.');

            return null;
        }

        $allHeaders = array_merge([
            'Authorization' => "Bot {$token}",
            'Content-Type' => 'application/json',
            'User-Agent' => 'TBSKliniekShield/1.0',
        ], $headers);

        try {
            $client = Http::withHeaders($allHeaders)->timeout(8);
            $url = "{$this->baseUrl}/".ltrim($endpoint, '/');

            return match (strtoupper($method)) {
                'GET' => $client->get($url, $data),
                'POST' => $client->post($url, $data),
                'PUT' => $client->put($url, $data),
                'PATCH' => $client->patch($url, $data),
                'DELETE' => $client->delete($url, $data),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error("DiscordShieldService API Error ({$endpoint}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * Assign a role to a member.
     */
    public function addRole(string $guildId, string $userId, string $roleId, string $reason = 'TBS Shield Verificatie'): bool
    {
        if (empty($guildId) || empty($userId) || empty($roleId)) {
            return false;
        }

        $resp = $this->request('PUT', "/guilds/{$guildId}/members/{$userId}/roles/{$roleId}", [], [
            'X-Audit-Log-Reason' => $reason,
        ]);

        return $resp && ($resp->status() === 204 || $resp->successful());
    }

    /**
     * Remove a role from a member.
     */
    public function removeRole(string $guildId, string $userId, string $roleId, string $reason = 'TBS Shield Verificatie'): bool
    {
        if (empty($guildId) || empty($userId) || empty($roleId)) {
            return false;
        }

        $resp = $this->request('DELETE', "/guilds/{$guildId}/members/{$userId}/roles/{$roleId}", [], [
            'X-Audit-Log-Reason' => $reason,
        ]);

        return $resp && ($resp->status() === 204 || $resp->successful());
    }

    /**
     * Synchronize game-related roles (Speler role and VIP role) for a verified character.
     */
    public function syncGameRoles(string $guildId, string $discordId, Character $character): array
    {
        $results = [
            'player_role' => false,
            'vip_role' => false,
        ];

        // 1. Speler Rol (TBS-Kliniek.nl)
        $playerRoleId = (string) DiscordShieldSetting::get('game_player_role_id', '1560022304220512388');
        if (! empty($playerRoleId)) {
            $results['player_role'] = $this->addRole(
                $guildId,
                $discordId,
                $playerRoleId,
                "TBS Speler gekoppeld: {$character->name}"
            );
        }

        // 2. VIP Rol (Donateur / VIP status)
        $vipRoleId = (string) DiscordShieldSetting::get('vip_role_id', '1556100213381529610');
        if (! empty($vipRoleId)) {
            if ($character->isVip()) {
                $results['vip_role'] = $this->addRole(
                    $guildId,
                    $discordId,
                    $vipRoleId,
                    "TBS Actieve VIP speler: {$character->name}"
                );
            } else {
                $this->removeRole(
                    $guildId,
                    $discordId,
                    $vipRoleId,
                    "TBS VIP status verlopen: {$character->name}"
                );
            }
        }

        return $results;
    }

    /**
     * Kick a member from the server (e.g. for alt account or ban evasion).
     */
    public function kickMember(string $guildId, string $userId, string $reason = 'Alt account / Ban evasion gedetecteerd door TBS Shield'): bool
    {
        if (empty($guildId) || empty($userId)) {
            return false;
        }

        $resp = $this->request('DELETE', "/guilds/{$guildId}/members/{$userId}", [], [
            'X-Audit-Log-Reason' => $reason,
        ]);

        return $resp && ($resp->status() === 204 || $resp->successful());
    }

    /**
     * Ban a member from the server.
     */
    public function banMember(string $guildId, string $userId, string $reason = 'Permanente ban evasion via TBS Shield', int $deleteMessageDays = 1): bool
    {
        if (empty($guildId) || empty($userId)) {
            return false;
        }

        $resp = $this->request('PUT', "/guilds/{$guildId}/bans/{$userId}", [
            'delete_message_days' => $deleteMessageDays,
        ], [
            'X-Audit-Log-Reason' => $reason,
        ]);

        return $resp && ($resp->status() === 204 || $resp->successful());
    }

    /**
     * Post a rich embed to the staff logs channel.
     */
    public function sendStaffEmbed(array $embed): bool
    {
        $channelId = (string) DiscordShieldSetting::get('log_channel_id', '');
        if (empty($channelId)) {
            return false;
        }

        $resp = $this->request('POST', "/channels/{$channelId}/messages", [
            'embeds' => [$embed],
        ]);

        return $resp && $resp->successful();
    }

    /**
     * Build and send a verification alert embed to the staff channel.
     */
    public function sendVerificationReport(
        string $status,
        array $userData,
        string $ipAddress,
        string $country,
        string $fingerprint,
        ?string $altUsername = null,
        bool $isVpn = false,
        int $riskScore = 0
    ): void {
        $discordId = (string) ($userData['id'] ?? 'unknown');
        $cooldownKey = "discord_shield_report_cooldown_{$discordId}_{$status}";

        // Prevent spamming the staff log channel with identical warnings (max 1 per 5 mins per user for warnings, 1 per min for success)
        if (Cache::has($cooldownKey)) {
            return;
        }

        $cooldownSeconds = ($status === 'verified') ? 60 : 300;
        Cache::put($cooldownKey, true, now()->addSeconds($cooldownSeconds));

        $colors = [
            'verified' => 0x10B981,     // Emerald
            'alt_detected' => 0xEF4444, // Red
            'vpn_blocked' => 0xF59E0B,  // Amber
            'banned' => 0x881337,       // Dark Rose
        ];

        $titles = [
            'verified' => '✅ Nieuw Lid Geverifieerd',
            'alt_detected' => '🚨 ALT / BAN EVASION GEDETECTEERD',
            'vpn_blocked' => '⚠️ Verificatie Geweigerd (VPN / Proxy)',
            'banned' => '⛔ Zwarte Lijst / Hardware Ban Geblokkeerd',
        ];

        $embed = [
            'title' => $titles[$status] ?? 'TBS Shield Verificatie Rapport',
            'color' => $colors[$status] ?? 0x6366F1,
            'timestamp' => now()->toISOString(),
            'footer' => [
                'text' => 'TBS Kliniek Shield • Anti-Alt & Beveiliging',
            ],
            'fields' => [
                [
                    'name' => 'Discord Gebruiker',
                    'value' => "**{$userData['username']}** (`<@{$userData['id']}>`)\nID: `{$userData['id']}`",
                    'inline' => true,
                ],
                [
                    'name' => 'Locatie & Netwerk',
                    'value' => strtoupper($country).($isVpn ? ' • 🛡️ **VPN/Proxy**' : ' • ✅ Schoon'),
                    'inline' => true,
                ],
                [
                    'name' => 'Beveiligingscheck',
                    'value' => 'Turnstile & Fingerprint OK',
                    'inline' => true,
                ],
            ],
        ];

        if ($altUsername) {
            $embed['fields'][] = [
                'name' => '⚠️ Gelinkt aan Bestaand Account',
                'value' => "Match met: **{$altUsername}**\nActie: *Toegang geweigerd*",
                'inline' => false,
            ];
        }

        $embed['fields'][] = [
            'name' => 'Risico Score',
            'value' => "{$riskScore}% / 100%",
            'inline' => true,
        ];

        $this->sendStaffEmbed($embed);
    }

    /**
     * Post the official verification button message in the Discord verification channel.
     */
    public function postVerificationChannelMessage(string $channelId): ?string
    {
        $verifyUrl = route('discord.verify');

        $payload = [
            'embeds' => [
                [
                    'title' => '🛡️ Welkom bij TBS Kliniek • Verificatie',
                    'description' => "Om toegang te krijgen tot de afdelingen, chats en kanalen van de TBS Kliniek Discord server, moet je je account eenmalig verifiëren.\n\n🔒 **Waarom verifiëren?**\nDit beschermt onze community tegen spambots, overlast en verbannen spelers.\n\n👉 Klik op de onderstaande knop om direct binnen 10 seconden te verifiëren via je browser.",
                    'color' => 0x7C3AED, // Purple
                    'footer' => [
                        'text' => 'TBS Kliniek Beveiligingssysteem',
                    ],
                ],
            ],
            'components' => [
                [
                    'type' => 1, // ActionRow
                    'components' => [
                        [
                            'type' => 2, // Button
                            'style' => 5, // Link button
                            'label' => 'Verifieer je Account',
                            'url' => $verifyUrl,
                        ],
                    ],
                ],
            ],
        ];

        $resp = $this->request('POST', "/channels/{$channelId}/messages", $payload);

        return $resp && $resp->successful() ? $resp->json('id') : null;
    }
}

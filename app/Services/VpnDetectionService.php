<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VpnDetectionService
{
    /**
     * Known VPN, Proxy and Hosting provider keywords.
     */
    protected array $vpnKeywords = [
        'nordvpn', 'tefincom', 'hydra communications', 'm247', 'datacamp',
        'packethub', 'proton', 'mullvad', 'expressvpn', 'surfshark',
        'cyberghost', 'windscribe', 'private internet access', 'pia',
        'ovh', 'hetzner', 'digitalocean', 'linode', 'vultr', 'choopa',
        'leaseweb', 'hostinger', 'contabo', 'cogent', 'tor exit',
        'cloudflare warp', 'ipvanish', 'purevpn', 'tunsafe', 'tailscale',
    ];

    /**
     * Check if an IP address is a VPN, Proxy or Datacenter IP.
     *
     * @return array{is_vpn: bool, provider: ?string, country: ?string}
     */
    public function check(string $ip): array
    {
        // Don't check loopback / local IPs
        if (
            in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
        ) {
            return ['is_vpn' => false, 'provider' => null, 'country' => 'NL'];
        }

        // Cache result for 24 hours to keep verification super fast
        return Cache::remember("vpn_intel_{$ip}", 86400, function () use ($ip) {
            // Layer 1: ProxyCheck.io (Fast & Highly Accurate for NordVPN/Mullvad/Proton)
            try {
                $pResp = Http::timeout(2.5)->get("https://proxycheck.io/v2/{$ip}?vpn=1&asn=1&risk=1");
                if ($pResp->successful()) {
                    $pData = $pResp->json();
                    if (isset($pData[$ip])) {
                        $info = $pData[$ip];
                        $isProxy = ($info['proxy'] ?? 'no') === 'yes';
                        $type = $info['type'] ?? '';
                        $provider = $info['provider'] ?? ($info['organisation'] ?? 'Onbekende VPN');

                        if ($isProxy || in_array(strtoupper($type), ['VPN', 'TOR', 'COMPROMISED_SERVER'])) {
                            return [
                                'is_vpn' => true,
                                'provider' => $provider,
                                'country' => $info['isocode'] ?? 'NL',
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('ProxyCheck error: '.$e->getMessage());
            }

            // Layer 2: IP-API.com (Fast datacenter/hosting and proxy detection)
            try {
                $apiResp = Http::timeout(2.5)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,isp,org,as,proxy,hosting");
                if ($apiResp->successful()) {
                    $apiData = $apiResp->json();
                    if (($apiData['status'] ?? '') === 'success') {
                        $isHosting = (bool) ($apiData['hosting'] ?? false);
                        $isProxy = (bool) ($apiData['proxy'] ?? false);
                        $isp = strtolower((string) ($apiData['isp'] ?? ''));
                        $org = strtolower((string) ($apiData['org'] ?? ''));
                        $as = strtolower((string) ($apiData['as'] ?? ''));

                        // Check known keyword matches
                        $matchedKeyword = null;
                        foreach ($this->vpnKeywords as $kw) {
                            if (str_contains($isp, $kw) || str_contains($org, $kw) || str_contains($as, $kw)) {
                                $matchedKeyword = $kw;
                                break;
                            }
                        }

                        if ($isProxy || $isHosting || $matchedKeyword !== null) {
                            return [
                                'is_vpn' => true,
                                'provider' => $apiData['org'] ?? ($apiData['isp'] ?? 'Datacenter / VPN'),
                                'country' => $apiData['countryCode'] ?? 'NL',
                            ];
                        }

                        return [
                            'is_vpn' => false,
                            'provider' => null,
                            'country' => $apiData['countryCode'] ?? 'NL',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('IP-API error: '.$e->getMessage());
            }

            return ['is_vpn' => false, 'provider' => null, 'country' => 'NL'];
        });
    }
}

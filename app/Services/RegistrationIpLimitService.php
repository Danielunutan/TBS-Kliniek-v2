<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationIpLimitService
{
    /** Serialize the IP limit check and account creation across all application workers. */
    public function register(string $ip, callable $create): mixed
    {
        return DB::transaction(function () use ($ip, $create) {
            $ipClean = trim($ip);
            $packed = @inet_pton($ipClean);
            $hash = hash('sha256', $packed === false ? $ipClean : $packed);
            $canonicalIp = $packed !== false ? inet_ntop($packed) : $ipClean;

            DB::table('registration_ip_locks')->insertOrIgnore(['ip_hash' => $hash]);
            DB::table('registration_ip_locks')->where('ip_hash', $hash)->lockForUpdate()->first();

            $count = User::where(function ($q) use ($ipClean, $canonicalIp) {
                $q->where('registration_ip', $ipClean)
                    ->orWhere('registration_ip', $canonicalIp);
            })
                ->where('ip_limit_exempt', false)
                ->count();

            if ($count < 2) {
                $legacyIds = DB::table('sessions')
                    ->where(function ($q) use ($ipClean, $canonicalIp) {
                        $q->where('ip_address', $ipClean)
                            ->orWhere('ip_address', $canonicalIp);
                    })
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id');

                $count += User::whereIn('id', $legacyIds)
                    ->where('ip_limit_exempt', false)
                    ->whereNull('registration_ip')
                    ->count();
            }
            if ($count >= 2) {
                throw ValidationException::withMessages([
                    'username' => 'Er zijn al maximaal 2 accounts geregistreerd vanaf dit IP-adres (toegestaan voor bijv. gezinsleden). Wil je een uitzondering aanvragen voor een extra gezinslid? Maak dan een ticket aan via support.',
                ]);
            }

            return $create();
        });
    }
}

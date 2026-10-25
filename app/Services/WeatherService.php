<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class WeatherService
{
    /**
     * Get clinic weather data. Cached for 1 hour.
     *
     * @return array{location: string, temp: int, feels_like: int, condition: string, icon: string, wind: string, humidity: int, rain_chance: int, yard_status: string, yard_open: bool}
     */
    public function getClinicWeather(): array
    {
        return Cache::remember('tbs_clinic_weather', 3600, function (): array {
            $hour = (int) now()->format('H');
            $isNight = $hour < 7 || $hour >= 21;

            $temp = 14 + (int) round(sin(($hour - 6) / 24 * 2 * M_PI) * 5);
            $feelsLike = $temp - 1;

            $conditions = [
                ['name' => 'Half bewolkt', 'icon' => $isNight ? 'fa-cloud-moon' : 'fa-cloud-sun', 'rain' => 15, 'wind' => '3 Bft WNW'],
                ['name' => 'Licht bewolkt', 'icon' => $isNight ? 'fa-moon' : 'fa-sun', 'rain' => 5, 'wind' => '2 Bft ZW'],
                ['name' => 'Wisselvallig', 'icon' => 'fa-cloud-sun-rain', 'rain' => 35, 'wind' => '4 Bft NW'],
                ['name' => 'Zacht herfstweer', 'icon' => 'fa-wind', 'rain' => 10, 'wind' => '3 Bft ZW'],
            ];

            // Deterministic condition based on day of year
            $cond = $conditions[now()->dayOfYear % count($conditions)];

            $yardOpen = $hour >= 8 && $hour <= 20;
            $yardStatus = $yardOpen
                ? 'Luchtplaats geopend tot 20:30 uur (Ideaal voor beweging & hosselen)'
                : 'Luchtplaats gesloten &bull; Avondlockdown actief (Verplicht binnen)';

            return [
                'location' => 'TBS Kliniek Vught / Afdeling De Poort',
                'temp' => $temp,
                'feels_like' => $feelsLike,
                'condition' => $cond['name'],
                'icon' => $cond['icon'],
                'wind' => $cond['wind'],
                'humidity' => 76,
                'rain_chance' => $cond['rain'],
                'yard_status' => $yardStatus,
                'yard_open' => $yardOpen,
            ];
        });
    }
}

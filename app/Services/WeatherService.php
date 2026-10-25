<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class WeatherService
{
    /**
     * Default coordinates: Vught (TBS Kliniek Vught / PI Vught).
     */
    public const DEFAULT_LAT = 51.6500;

    public const DEFAULT_LON = 5.2900;

    public const DEFAULT_LOCATION = 'TBS Kliniek Vught / Afdeling De Poort';

    /**
     * Get clinic weather data for default location (Vught).
     *
     * @return array<string, mixed>
     */
    public function getClinicWeather(): array
    {
        return $this->getWeather(self::DEFAULT_LAT, self::DEFAULT_LON, self::DEFAULT_LOCATION);
    }

    /**
     * Get live weather data for any Dutch city or town name.
     *
     * @return array<string, mixed>
     */
    public function getWeatherByCity(string $city): array
    {
        $city = trim($city);
        if ($city === '') {
            return $this->getClinicWeather();
        }

        try {
            $resp = Http::withoutVerifying()
                ->timeout(4)
                ->get('https://geocoding-api.open-meteo.com/v1/search', [
                    'name' => $city,
                    'count' => 1,
                    'language' => 'nl',
                    'format' => 'json',
                ]);

            if ($resp->successful() && ! empty($resp->json('results'))) {
                $first = $resp->json('results')[0];
                $lat = (float) ($first['latitude'] ?? self::DEFAULT_LAT);
                $lon = (float) ($first['longitude'] ?? self::DEFAULT_LON);
                $resolvedName = (string) ($first['name'] ?? $city);

                return $this->getWeather($lat, $lon, $resolvedName);
            }
        } catch (Throwable $e) {
            // ignore
        }

        return $this->getClinicWeather();
    }

    /**
     * Get real live weather data from Open-Meteo for coordinates.
     * Cached for 15 minutes per approximate coordinate (2 decimals ~ 1km).
     *
     * @return array{
     *     location: string,
     *     city: string,
     *     temp: int,
     *     feels_like: int,
     *     condition: string,
     *     icon: string,
     *     wind: string,
     *     wind_speed: int,
     *     humidity: int,
     *     rain_chance: int,
     *     rain_mm: float,
     *     has_buien: bool,
     *     buien_text: string,
     *     yard_status: string,
     *     yard_open: bool,
     *     is_gps: bool,
     *     latitude: float,
     *     longitude: float
     * }
     */
    public function getWeather(?float $lat = null, ?float $lon = null, ?string $customCity = null): array
    {
        $lat = $lat !== null ? round($lat, 2) : self::DEFAULT_LAT;
        $lon = $lon !== null ? round($lon, 2) : self::DEFAULT_LON;
        $isGps = ($lat !== self::DEFAULT_LAT || $lon !== self::DEFAULT_LON);

        $cacheKey = sprintf('tbs_weather_real_%s_%s', str_replace('.', '_', (string) $lat), str_replace('.', '_', (string) $lon));

        return Cache::remember($cacheKey, 900, function () use ($lat, $lon, $isGps, $customCity): array {
            $cityName = $customCity ?: ($isGps ? $this->resolveCityName($lat, $lon) : self::DEFAULT_LOCATION);

            try {
                $response = Http::withoutVerifying()
                    ->timeout(4)
                    ->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,rain,showers,weather_code,wind_speed_10m',
                        'hourly' => 'precipitation_probability,precipitation',
                        'forecast_days' => 1,
                        'timezone' => 'auto',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $current = $data['current'] ?? [];
                    $hourly = $data['hourly'] ?? [];

                    $temp = (int) round($current['temperature_2m'] ?? 14);
                    $feelsLike = (int) round($current['apparent_temperature'] ?? $temp);
                    $humidity = (int) ($current['relative_humidity_2m'] ?? 75);
                    $windKm = (int) round($current['wind_speed_10m'] ?? 18);
                    $rainMm = (float) round(($current['precipitation'] ?? 0) + ($current['showers'] ?? 0), 1);
                    $weatherCode = (int) ($current['weather_code'] ?? 0);

                    // Map WMO code to human description & icons
                    $weatherMeta = $this->mapWmoCode($weatherCode, $rainMm);

                    // Buien radar / neerslag analyse
                    $hasBuien = $rainMm > 0.1 || in_array($weatherCode, [80, 81, 82, 95, 96, 99], true);
                    $maxRainProb = ! empty($hourly['precipitation_probability'])
                        ? (int) max(array_slice($hourly['precipitation_probability'], 0, 4))
                        : ($hasBuien ? 85 : 15);

                    if ($hasBuien) {
                        $buienText = sprintf('Actieve bui gaande (%.1f mm/u neerslag)', $rainMm);
                    } elseif ($maxRainProb >= 50) {
                        $buienText = sprintf('Buien op komst (%d%% neerslagkans komende uren)', $maxRainProb);
                    } else {
                        $buienText = 'Geen buien verwacht &bull; Droog';
                    }

                    // Yard rules based on time & precipitation
                    $hour = (int) now()->format('H');
                    $isNight = $hour < 8 || $hour >= 21;
                    $yardOpen = ! $isNight && $rainMm < 3.5 && ! in_array($weatherCode, [81, 82, 95, 96, 99], true);

                    if ($isNight) {
                        $yardStatus = 'Luchtplaats gesloten &bull; Avondlockdown actief (Verplicht binnen)';
                    } elseif (! $yardOpen) {
                        $yardStatus = 'Luchtplaats tijdelijk ontruimd wegens hevige stortbui!';
                    } elseif ($rainMm > 0.1) {
                        $yardStatus = 'Luchtplaats geopend (Pas op: nat & gladde tegels)';
                    } else {
                        $yardStatus = 'Luchtplaats geopend tot 20:30 uur (Ideaal voor beweging & hosselen)';
                    }

                    return [
                        'location' => $cityName,
                        'city' => $cityName,
                        'temp' => $temp,
                        'feels_like' => $feelsLike,
                        'condition' => $weatherMeta['condition'],
                        'icon' => $weatherMeta['icon'],
                        'wind' => sprintf('%d km/u (%s)', $windKm, $this->kmhToBft($windKm)),
                        'wind_speed' => $windKm,
                        'humidity' => $humidity,
                        'rain_chance' => $maxRainProb,
                        'rain_mm' => $rainMm,
                        'has_buien' => $hasBuien,
                        'buien_text' => $buienText,
                        'yard_status' => $yardStatus,
                        'yard_open' => $yardOpen,
                        'is_gps' => $isGps,
                        'latitude' => $lat,
                        'longitude' => $lon,
                    ];
                }
            } catch (Throwable $e) {
                // Graceful fallback to deterministic weather
            }

            return $this->fallbackWeather($cityName, $isGps, $lat, $lon);
        });
    }

    /**
     * Resolve reverse geocoded city name.
     */
    protected function resolveCityName(float $lat, float $lon): string
    {
        try {
            $resp = Http::withoutVerifying()
                ->timeout(3)
                ->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'localityLanguage' => 'nl',
                ]);

            if ($resp->successful()) {
                $city = $resp->json('city') ?? $resp->json('locality') ?? $resp->json('principalSubdivision');
                if ($city) {
                    return (string) $city;
                }
            }
        } catch (Throwable $e) {
            // ignore
        }

        return sprintf('Locatie (%.2f, %.2f)', $lat, $lon);
    }

    /**
     * Map WMO weather codes to Dutch terms and FontAwesome icons.
     *
     * @return array{condition: string, icon: string}
     */
    protected function mapWmoCode(int $code, float $rainMm): array
    {
        $hour = (int) now()->format('H');
        $isNight = $hour < 7 || $hour >= 21;

        return match ($code) {
            0 => ['condition' => 'Helder & Zonnig', 'icon' => $isNight ? 'fa-moon text-indigo-300' : 'fa-sun text-amber-400'],
            1, 2 => ['condition' => 'Licht tot half bewolkt', 'icon' => $isNight ? 'fa-cloud-moon text-indigo-300' : 'fa-cloud-sun text-amber-400'],
            3 => ['condition' => 'Zwaar bewolkt', 'icon' => 'fa-cloud text-slate-400'],
            45, 48 => ['condition' => 'Mist & Dichte nevel', 'icon' => 'fa-smog text-slate-400'],
            51, 53, 55 => ['condition' => 'Motregen', 'icon' => 'fa-cloud-rain text-sky-400'],
            61, 63 => ['condition' => 'Lichte regen', 'icon' => 'fa-cloud-rain text-sky-400'],
            65 => ['condition' => 'Aanhoudende regen', 'icon' => 'fa-cloud-showers-heavy text-blue-500'],
            71, 73, 75 => ['condition' => 'Sneeuwval', 'icon' => 'fa-snowflake text-sky-200'],
            80 => ['condition' => 'Lichte regenbui', 'icon' => 'fa-cloud-sun-rain text-sky-400'],
            81 => ['condition' => 'Matige regenbui', 'icon' => 'fa-cloud-showers-heavy text-blue-500'],
            82 => ['condition' => 'Hevige stortbui!', 'icon' => 'fa-cloud-showers-heavy text-indigo-500 animate-pulse'],
            95, 96, 99 => ['condition' => 'Onweersbui & Bliksem', 'icon' => 'fa-bolt text-amber-400 animate-bounce'],
            default => $rainMm > 0
                ? ['condition' => 'Regenachtig', 'icon' => 'fa-cloud-rain text-sky-400']
                : ['condition' => 'Wisselvallig', 'icon' => 'fa-cloud-sun text-amber-400'],
        };
    }

    /**
     * Convert wind speed km/h to Beaufort scale.
     */
    protected function kmhToBft(int $kmh): string
    {
        if ($kmh <= 5) {
            return '1 Bft';
        }
        if ($kmh <= 11) {
            return '2 Bft';
        }
        if ($kmh <= 19) {
            return '3 Bft';
        }
        if ($kmh <= 28) {
            return '4 Bft';
        }
        if ($kmh <= 38) {
            return '5 Bft';
        }
        if ($kmh <= 49) {
            return '6 Bft';
        }
        if ($kmh <= 61) {
            return '7 Bft';
        }

        return '8+ Bft';
    }

    /**
     * Fallback in case Open-Meteo is unavailable.
     *
     * @return array<string, mixed>
     */
    protected function fallbackWeather(string $cityName, bool $isGps, float $lat, float $lon): array
    {
        $hour = (int) now()->format('H');
        $isNight = $hour < 7 || $hour >= 21;
        $temp = 14 + (int) round(sin(($hour - 6) / 24 * 2 * M_PI) * 4);
        $yardOpen = $hour >= 8 && $hour <= 20;

        return [
            'location' => $cityName,
            'city' => $cityName,
            'temp' => $temp,
            'feels_like' => $temp - 1,
            'condition' => 'Half bewolkt',
            'icon' => $isNight ? 'fa-cloud-moon text-indigo-300' : 'fa-cloud-sun text-amber-400',
            'wind' => '18 km/u (3 Bft)',
            'wind_speed' => 18,
            'humidity' => 78,
            'rain_chance' => 20,
            'rain_mm' => 0.0,
            'has_buien' => false,
            'buien_text' => 'Geen buien verwacht &bull; Droog',
            'yard_status' => $yardOpen
                ? 'Luchtplaats geopend tot 20:30 uur (Ideaal voor beweging & hosselen)'
                : 'Luchtplaats gesloten &bull; Avondlockdown actief (Verplicht binnen)',
            'yard_open' => $yardOpen,
            'is_gps' => $isGps,
            'latitude' => $lat,
            'longitude' => $lon,
        ];
    }
}

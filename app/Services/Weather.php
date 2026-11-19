<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Current conditions + 5-day forecast from Open-Meteo, with plain-language farming tips.
 * Always safe to call: returns null when weather is off or the service can't be reached,
 * so the dashboard never breaks on a poor connection.
 */
class Weather
{
    private const DAYS = 5;

    /** WMO weather code => [label, emoji] */
    private const CODES = [
        0 => ['Clear sky', '☀️'],
        1 => ['Mostly clear', '🌤️'],
        2 => ['Partly cloudy', '⛅'],
        3 => ['Overcast', '☁️'],
        45 => ['Fog', '🌫️'],
        48 => ['Fog', '🌫️'],
        51 => ['Light drizzle', '🌦️'],
        53 => ['Drizzle', '🌦️'],
        55 => ['Heavy drizzle', '🌦️'],
        56 => ['Freezing drizzle', '🌦️'],
        57 => ['Freezing drizzle', '🌦️'],
        61 => ['Light rain', '🌦️'],
        63 => ['Rain', '🌧️'],
        65 => ['Heavy rain', '🌧️'],
        66 => ['Freezing rain', '🌧️'],
        67 => ['Freezing rain', '🌧️'],
        71 => ['Light snow', '🌨️'],
        73 => ['Snow', '🌨️'],
        75 => ['Heavy snow', '🌨️'],
        77 => ['Snow grains', '🌨️'],
        80 => ['Light showers', '🌦️'],
        81 => ['Showers', '🌧️'],
        82 => ['Heavy showers', '🌧️'],
        85 => ['Snow showers', '🌨️'],
        86 => ['Snow showers', '🌨️'],
        95 => ['Thunderstorm', '⛈️'],
        96 => ['Thunderstorm with hail', '⛈️'],
        99 => ['Thunderstorm with hail', '⛈️'],
    ];

    public static function forecast(): ?array
    {
        if (! config('weather.enabled')) {
            return null;
        }

        $key = 'weather:'.config('weather.latitude').','.config('weather.longitude');
        $minutes = max(1, (int) config('weather.cache_minutes'));

        // Only successful responses are cached, so a failed request is retried on the next page load.
        $cached = Cache::get($key);
        if ($cached) {
            return $cached;
        }

        $data = self::fetch();
        if ($data) {
            Cache::put($key, $data, now()->addMinutes($minutes));
        }

        return $data;
    }

    private static function fetch(): ?array
    {
        try {
            $res = Http::timeout((int) config('weather.timeout'))
                ->retry(1, 200, throw: false)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => config('weather.latitude'),
                    'longitude' => config('weather.longitude'),
                    'current' => 'temperature_2m,apparent_temperature,relative_humidity_2m,precipitation,weather_code,wind_speed_10m',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max',
                    'timezone' => config('app.timezone'),
                    'forecast_days' => self::DAYS,
                ]);

            if (! $res->successful()) {
                Log::warning('Weather request failed', ['status' => $res->status()]);

                return null;
            }

            return self::shape($res->json());
        } catch (Throwable $e) {
            Log::warning('Weather unavailable: '.$e->getMessage());

            return null;
        }
    }

    /** Turn the raw API payload into what the view needs. Returns null if it isn't usable. */
    public static function shape(?array $json): ?array
    {
        $cur = $json['current'] ?? null;
        $d = $json['daily'] ?? null;
        if (! $cur || ! $d || empty($d['time']) || ! isset($cur['temperature_2m'])) {
            return null;
        }

        $days = [];
        foreach ($d['time'] as $i => $date) {
            $code = (int) ($d['weather_code'][$i] ?? 0);
            $days[] = [
                'date' => Carbon::parse($date),
                'label' => $i === 0 ? 'Today' : Carbon::parse($date)->format('D'),
                'icon' => self::describe($code)[1],
                'summary' => self::describe($code)[0],
                'max' => (int) round($d['temperature_2m_max'][$i] ?? 0),
                'min' => (int) round($d['temperature_2m_min'][$i] ?? 0),
                'rain_mm' => round((float) ($d['precipitation_sum'][$i] ?? 0), 1),
                'rain_chance' => (int) ($d['precipitation_probability_max'][$i] ?? 0),
            ];
        }

        $code = (int) ($cur['weather_code'] ?? 0);
        [$label, $icon] = self::describe($code);

        return [
            'location' => config('weather.location'),
            'temp' => (int) round($cur['temperature_2m']),
            'feels_like' => (int) round($cur['apparent_temperature'] ?? $cur['temperature_2m']),
            'humidity' => (int) ($cur['relative_humidity_2m'] ?? 0),
            'wind' => (int) round($cur['wind_speed_10m'] ?? 0),
            'condition' => $label,
            'icon' => $icon,
            'days' => $days,
            'tips' => self::tips($days),
            'updated' => now(),
        ];
    }

    public static function describe(int $code): array
    {
        return self::CODES[$code] ?? ['Unknown', '🌡️'];
    }

    /** Simple, practical advice from the next few days of rain and heat. Returns [ [text, level], ... ]. */
    public static function tips(array $days): array
    {
        if (! $days) {
            return [];
        }

        $tips = [];
        $today = $days[0];
        $next3 = array_slice($days, 0, 3);

        $heavy = collect($next3)->first(fn ($d) => $d['rain_mm'] >= 20);
        $rainyToday = $today['rain_mm'] >= 5 || $today['rain_chance'] >= 70;
        $dry = collect($days)->every(fn ($d) => $d['rain_mm'] < 1 && $d['rain_chance'] < 30);
        $hot = collect($next3)->contains(fn ($d) => $d['max'] >= 32);

        if ($heavy) {
            $when = $heavy['label'] === 'Today' ? 'today' : 'on '.$heavy['date']->format('l');
            $tips[] = ["Heavy rain expected {$when} (about {$heavy['rain_mm']} mm). Hold off on spraying and fertiliser, harvest ripe crops early, and clear drainage channels.", 'warn'];
        } elseif ($rainyToday) {
            $tips[] = ['Rain likely today. Avoid spraying or applying fertiliser — it will wash away — and keep harvested produce under cover.', 'info'];
        }

        if ($dry) {
            $tips[] = ['A dry week ahead. Mulch beds, water young seedlings early morning or evening, and delay transplanting if you can.', 'warn'];
        } elseif (! $heavy && ! $rainyToday && ($next3[1]['rain_chance'] ?? 0) >= 60) {
            $tips[] = ['Rain is likely in the next couple of days. A good window to plant or transplant so seedlings get the water.', 'good'];
        }

        if ($hot) {
            $tips[] = ['Hot afternoons coming. Work in the cool of the morning and water young crops before midday.', 'warn'];
        }

        if (! $tips) {
            $tips[] = ['Fairly settled weather. A good time for weeding, field work and routine spraying.', 'good'];
        }

        return $tips;
    }
}

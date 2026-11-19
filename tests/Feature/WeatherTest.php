<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Weather;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $rain = [0, 0, 0, 0, 0]): array
    {
        $dates = collect(range(0, 4))->map(fn ($i) => now()->addDays($i)->toDateString())->all();

        return [
            'current' => ['temperature_2m' => 24.6, 'apparent_temperature' => 26.1, 'relative_humidity_2m' => 71, 'weather_code' => 2, 'wind_speed_10m' => 8.4],
            'daily' => [
                'time' => $dates,
                'weather_code' => [2, 61, 63, 3, 0],
                'temperature_2m_max' => [27, 26, 25, 27, 28],
                'temperature_2m_min' => [16, 16, 15, 16, 16],
                'precipitation_sum' => $rain,
                'precipitation_probability_max' => array_map(fn ($r) => $r > 0 ? 80 : 10, $rain),
            ],
        ];
    }

    private function user(): User
    {
        return User::create(['name' => 'Test User', 'email' => 'w@example.com', 'role' => 'member', 'password' => 'password']);
    }

    public function test_dashboard_shows_weather_card(): void
    {
        config(['weather.enabled' => true]);
        Http::fake(['api.open-meteo.com/*' => Http::response($this->payload([0, 2, 6, 0, 0]))]);

        $this->actingAs($this->user())->get('/dashboard')
            ->assertOk()->assertSee('Weather')->assertSee('25°C')->assertSee('Partly cloudy');
    }

    public function test_dashboard_still_loads_when_weather_is_down(): void
    {
        config(['weather.enabled' => true]);
        Http::fake(['api.open-meteo.com/*' => Http::response('nope', 500)]);

        $this->actingAs($this->user())->get('/dashboard')
            ->assertOk()->assertSee('Income Summary')->assertDontSee('Weather ·');
    }

    public function test_heavy_rain_gives_a_warning_tip(): void
    {
        $data = Weather::shape($this->payload([0, 30, 0, 0, 0]));

        $this->assertNotNull($data);
        $this->assertStringContainsString('Heavy rain', $data['tips'][0][0]);
        $this->assertSame('warn', $data['tips'][0][1]);
    }

    public function test_bad_payload_returns_null(): void
    {
        $this->assertNull(Weather::shape(['oops' => true]));
    }
}

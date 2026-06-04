<?php

namespace App\Weather\Provider;

use App\Weather\ForecastData;
use App\Weather\WeatherProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Portée gratuite : 14 jours. Tous les horizons disponibles.
 * Toutes les 6 variables disponibles nativement en métrique.
 */
class WeatherApiService implements WeatherProviderInterface
{
    private const BASE_URL = 'https://api.weatherapi.com/v1/forecast.json';
    private const HORIZONS = [0, 5, 10, 14];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    public function getName(): string { return 'WeatherAPI'; }
    public function getSlug(): string { return 'weatherapi'; }

    public function getForecasts(float $lat, float $lon): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => [
                'key'    => $this->apiKey,
                'q'      => "$lat,$lon",
                'days'   => 14,
                'aqi'    => 'no',
                'alerts' => 'no',
            ],
        ]);

        $days = $response->toArray()['forecast']['forecastday'] ?? [];
        $forecasts = [];

        foreach (self::HORIZONS as $horizon) {
            if (!isset($days[$horizon])) {
                continue;
            }

            $day = $days[$horizon]['day'];

            $forecasts[$horizon] = new ForecastData(
                horizon: $horizon,
                targetDate: new \DateTimeImmutable($days[$horizon]['date']),
                tempMax: $day['maxtemp_c'] ?? null,
                tempMin: $day['mintemp_c'] ?? null,
                precipitation: $day['totalprecip_mm'] ?? null,
                rainProbability: $day['daily_chance_of_rain'] ?? null,
                windSpeed: $day['maxwind_kph'] ?? null,
                humidity: isset($day['avghumidity']) ? (int) $day['avghumidity'] : null,
            );
        }

        return $forecasts;
    }
}

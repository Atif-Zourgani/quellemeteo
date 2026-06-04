<?php

namespace App\Weather\Provider;

use App\Weather\ForecastData;
use App\Weather\WeatherProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Portée gratuite : 8 jours max (index 0 à 7).
 * J+10 et J+14 seront absents du tableau retourné.
 * Vent retourné en m/s par l'API → converti en km/h.
 */
class OpenWeatherMapService implements WeatherProviderInterface
{
    private const BASE_URL = 'https://api.openweathermap.org/data/3.0/onecall';
    private const HORIZONS = [0, 5, 10, 14];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    public function getName(): string { return 'OpenWeatherMap'; }
    public function getSlug(): string { return 'openweathermap'; }

    public function getForecasts(float $lat, float $lon): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => [
                'lat'     => $lat,
                'lon'     => $lon,
                'exclude' => 'current,minutely,hourly,alerts',
                'units'   => 'metric',
                'appid'   => $this->apiKey,
            ],
        ]);

        $daily = $response->toArray()['daily'] ?? [];
        $forecasts = [];

        foreach (self::HORIZONS as $horizon) {
            if (!isset($daily[$horizon])) {
                continue; // Horizon hors portée (J+10, J+14 non disponibles sur le tier gratuit)
            }

            $day = $daily[$horizon];

            $forecasts[$horizon] = new ForecastData(
                horizon: $horizon,
                targetDate: new \DateTimeImmutable('@' . $day['dt']),
                tempMax: $day['temp']['max'] ?? null,
                tempMin: $day['temp']['min'] ?? null,
                precipitation: $day['rain'] ?? 0.0,
                rainProbability: isset($day['pop']) ? (int) round($day['pop'] * 100) : null,
                windSpeed: isset($day['wind_speed']) ? round($day['wind_speed'] * 3.6, 1) : null,
                humidity: $day['humidity'] ?? null,
            );
        }

        return $forecasts;
    }
}

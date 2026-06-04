<?php

namespace App\Weather\Provider;

use App\Weather\ForecastData;
use App\Weather\WeatherProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Portée gratuite : 15 jours. Tous les horizons (J0, J+5, J+10, J+14) disponibles.
 * Toutes les 6 variables disponibles nativement.
 */
class VisualCrossingService implements WeatherProviderInterface
{
    private const BASE_URL = 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline';
    private const HORIZONS = [0, 5, 10, 14];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    public function getName(): string { return 'Visual Crossing'; }
    public function getSlug(): string { return 'visual-crossing'; }

    public function getForecasts(float $lat, float $lon): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . "/$lat,$lon/next15days", [
            'query' => [
                'unitGroup'   => 'metric',
                'include'     => 'days',
                'key'         => $this->apiKey,
                'contentType' => 'json',
            ],
        ]);

        $days = $response->toArray()['days'] ?? [];
        $forecasts = [];

        foreach (self::HORIZONS as $horizon) {
            if (!isset($days[$horizon])) {
                continue;
            }

            $day = $days[$horizon];

            $forecasts[$horizon] = new ForecastData(
                horizon: $horizon,
                targetDate: new \DateTimeImmutable($day['datetime']),
                tempMax: $day['tempmax'] ?? null,
                tempMin: $day['tempmin'] ?? null,
                precipitation: $day['precip'] ?? null,
                rainProbability: isset($day['precipprob']) ? (int) $day['precipprob'] : null,
                windSpeed: $day['windspeed'] ?? null,
                humidity: isset($day['humidity']) ? (int) round($day['humidity']) : null,
            );
        }

        return $forecasts;
    }
}

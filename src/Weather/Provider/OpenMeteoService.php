<?php

namespace App\Weather\Provider;

use App\Weather\ForecastData;
use App\Weather\WeatherProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Aucune clé API requise. Portée max : 16 jours.
 * Humidité non disponible en agrégat journalier → null.
 */
class OpenMeteoService implements WeatherProviderInterface
{
    private const BASE_URL = 'https://api.open-meteo.com/v1/forecast';
    private const HORIZONS = [0, 5, 10, 14];

    public function __construct(private readonly HttpClientInterface $httpClient) {}

    public function getName(): string { return 'Open-Meteo'; }
    public function getSlug(): string { return 'open-meteo'; }

    public function getForecasts(float $lat, float $lon): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => [
                'latitude'       => $lat,
                'longitude'      => $lon,
                'daily'          => implode(',', [
                    'temperature_2m_max',
                    'temperature_2m_min',
                    'precipitation_sum',
                    'precipitation_probability_max',
                    'wind_speed_10m_max',
                ]),
                'forecast_days'  => 15,
                'timezone'       => 'Europe/Paris',
                'wind_speed_unit' => 'kmh',
            ],
        ]);

        $daily = $response->toArray()['daily'];
        $forecasts = [];

        foreach (self::HORIZONS as $horizon) {
            if (!isset($daily['time'][$horizon])) {
                continue;
            }

            $forecasts[$horizon] = new ForecastData(
                horizon: $horizon,
                targetDate: new \DateTimeImmutable($daily['time'][$horizon]),
                tempMax: $daily['temperature_2m_max'][$horizon] ?? null,
                tempMin: $daily['temperature_2m_min'][$horizon] ?? null,
                precipitation: $daily['precipitation_sum'][$horizon] ?? null,
                rainProbability: isset($daily['precipitation_probability_max'][$horizon])
                    ? (int) $daily['precipitation_probability_max'][$horizon]
                    : null,
                windSpeed: $daily['wind_speed_10m_max'][$horizon] ?? null,
                humidity: null,
            );
        }

        return $forecasts;
    }
}

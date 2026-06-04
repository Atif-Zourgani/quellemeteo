<?php

namespace App\Weather\Provider;

use App\Weather\ForecastData;
use App\Weather\WeatherProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * API communautaire française Infoclimat.
 * Requiert une clé API (_auth) et un secret (_c).
 * Portée : à confirmer — utilise les modèles GFS/ARPEGE.
 * Probabilité de pluie non disponible → null.
 * Limite : 5 000 requêtes / 24h.
 */
class InfoclimatService implements WeatherProviderInterface
{
    private const BASE_URL = 'https://www.infoclimat.fr/public-api/gfs/json';
    private const HORIZONS = [0, 5, 10, 14];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $apiSecret,
    ) {}

    public function getName(): string { return 'Infoclimat'; }
    public function getSlug(): string { return 'infoclimat'; }

    public function getForecasts(float $lat, float $lon): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => [
                '_ll'   => "$lat,$lon",
                '_auth' => $this->apiKey,
                '_c'    => $this->apiSecret,
            ],
        ]);

        $data = $response->toArray();

        // Infoclimat retourne un objet indexé par date (ex: "2026-06-04 12:00:00")
        // On reconstruit un tableau trié par date pour accéder par index d'horizon.
        $dailyData = $this->groupByDay($data);
        $forecasts = [];

        foreach (self::HORIZONS as $horizon) {
            if (!isset($dailyData[$horizon])) {
                continue;
            }

            $day = $dailyData[$horizon];

            $forecasts[$horizon] = new ForecastData(
                horizon: $horizon,
                targetDate: new \DateTimeImmutable($day['date']),
                tempMax: $day['temperature_max'] ?? null,
                tempMin: $day['temperature_min'] ?? null,
                precipitation: $day['rr'] ?? null,
                rainProbability: null, // Non fourni par Infoclimat
                windSpeed: isset($day['wind_speed']) ? round($day['wind_speed'] * 3.6, 1) : null,
                humidity: $day['humidity'] ?? null,
            );
        }

        return $forecasts;
    }

    /**
     * Regroupe les données horaires par jour et extrait les agrégats journaliers.
     * La structure exacte sera ajustée lors du test avec les vraies clés API.
     */
    private function groupByDay(array $data): array
    {
        $days = [];

        foreach ($data as $datetime => $values) {
            if (!is_array($values)) {
                continue;
            }

            $date = substr((string) $datetime, 0, 10);

            if (!isset($days[$date])) {
                $days[$date] = [
                    'date'            => $date,
                    'temperature_max' => -INF,
                    'temperature_min' => INF,
                    'rr'              => 0,
                    'wind_speed'      => 0,
                    'humidity'        => [],
                ];
            }

            if (isset($values['temperature']['2m'])) {
                $days[$date]['temperature_max'] = max($days[$date]['temperature_max'], $values['temperature']['2m']);
                $days[$date]['temperature_min'] = min($days[$date]['temperature_min'], $values['temperature']['2m']);
            }

            if (isset($values['pluie'])) {
                $days[$date]['rr'] += $values['pluie'];
            }

            if (isset($values['vent']['10m']['ff'])) {
                $days[$date]['wind_speed'] = max($days[$date]['wind_speed'], $values['vent']['10m']['ff']);
            }

            if (isset($values['humidite']['2m'])) {
                $days[$date]['humidity'][] = $values['humidite']['2m'];
            }
        }

        $indexed = [];
        $i = 0;
        ksort($days);

        foreach ($days as $day) {
            $day['temperature_max'] = $day['temperature_max'] === -INF ? null : round($day['temperature_max'] - 273.15, 1);
            $day['temperature_min'] = $day['temperature_min'] === INF  ? null : round($day['temperature_min'] - 273.15, 1);
            $day['humidity']        = count($day['humidity']) > 0 ? (int) round(array_sum($day['humidity']) / count($day['humidity'])) : null;

            $indexed[$i++] = $day;
        }

        return $indexed;
    }
}

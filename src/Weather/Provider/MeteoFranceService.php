<?php

namespace App\Weather\Provider;

use App\Weather\ForecastData;
use App\Weather\WeatherProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * API officielle Météo-France (public-api.meteofrance.fr).
 * Clé API requise, valable 3 ans (inscription sur portail Météo-France).
 *
 * Endpoint utilisé : prévision quotidienne par coordonnées.
 * À valider lors de l'obtention de la clé API — le portail est en migration.
 * Portée : ~14 jours via le modèle ARPEGE étendu.
 */
class MeteoFranceService implements WeatherProviderInterface
{
    private const BASE_URL = 'https://public-api.meteofrance.fr/public/DPPaquet/v1/prevision-quotidienne';
    private const HORIZONS = [0, 5, 10, 14];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    public function getName(): string { return 'Météo-France'; }
    public function getSlug(): string { return 'meteo-france'; }

    public function getForecasts(float $lat, float $lon): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'headers' => [
                'apikey' => $this->apiKey,
            ],
            'query' => [
                'lat'  => $lat,
                'lon'  => $lon,
            ],
        ]);

        $data = $response->toArray();

        // La structure exacte de la réponse sera ajustée lors du test avec la clé API réelle.
        // Météo-France retourne probablement un tableau 'daily' ou 'properties/periods'.
        $days = $data['daily'] ?? $data['properties']['periods'] ?? [];
        $forecasts = [];

        foreach (self::HORIZONS as $horizon) {
            if (!isset($days[$horizon])) {
                continue;
            }

            $day = $days[$horizon];

            $forecasts[$horizon] = new ForecastData(
                horizon: $horizon,
                targetDate: new \DateTimeImmutable($day['time'] ?? $day['date']),
                tempMax: $day['T_max'] ?? $day['temperature']['max'] ?? null,
                tempMin: $day['T_min'] ?? $day['temperature']['min'] ?? null,
                precipitation: $day['rr24'] ?? $day['precipitation']['total'] ?? null,
                rainProbability: $day['probarain'] ?? $day['precipitation']['probability'] ?? null,
                windSpeed: $day['ff_max'] ?? $day['wind']['speed'] ?? null,
                humidity: $day['U_min'] ?? $day['humidity']['mean'] ?? null,
            );
        }

        return $forecasts;
    }
}

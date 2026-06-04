<?php

namespace App\Weather;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Récupère la météo réelle du jour précédent via Open-Meteo ERA5.
 * ERA5 est la référence scientifique mondiale pour les données météo historiques (ECMWF).
 * Aucune clé API requise. Données disponibles dès le lendemain matin.
 */
class ActualWeatherService
{
    private const BASE_URL = 'https://archive-api.open-meteo.com/v1/archive';
    private const VARIABLES = [
        'temperature_2m_max',
        'temperature_2m_min',
        'precipitation_sum',
        'wind_speed_10m_max',
        'relative_humidity_2m_mean',
    ];

    public function __construct(private readonly HttpClientInterface $httpClient) {}

    /**
     * Retourne la météo réelle d'hier pour une ville donnée.
     */
    public function getYesterday(float $lat, float $lon): ActualWeatherData
    {
        $yesterday = (new \DateTimeImmutable('yesterday'))->format('Y-m-d');

        return $this->getForDate($lat, $lon, $yesterday);
    }

    /**
     * Retourne la météo réelle pour une date précise (format Y-m-d).
     * Utile pour récupérer des données historiques lors de l'initialisation.
     */
    public function getForDate(float $lat, float $lon, string $date): ActualWeatherData
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => [
                'latitude'        => $lat,
                'longitude'       => $lon,
                'start_date'      => $date,
                'end_date'        => $date,
                'daily'           => implode(',', self::VARIABLES),
                'timezone'        => 'Europe/Paris',
                'wind_speed_unit' => 'kmh',
            ],
        ]);

        $daily = $response->toArray()['daily'];

        return new ActualWeatherData(
            date:          new \DateTimeImmutable($daily['time'][0]),
            tempMax:       $daily['temperature_2m_max'][0],
            tempMin:       $daily['temperature_2m_min'][0],
            precipitation: $daily['precipitation_sum'][0],
            windSpeed:     $daily['wind_speed_10m_max'][0],
            humidity:      (int) $daily['relative_humidity_2m_mean'][0],
        );
    }
}

<?php

namespace App\Weather;

/**
 * DTO représentant la météo réelle d'une ville pour une journée passée.
 * Source : Open-Meteo ERA5 (référence scientifique mondiale).
 * Pas de probabilité de pluie — c'est une mesure réelle, pas une prévision.
 */
readonly class ActualWeatherData
{
    public function __construct(
        public \DateTimeImmutable $date,
        public float $tempMax,       // °C
        public float $tempMin,       // °C
        public float $precipitation, // mm
        public float $windSpeed,     // km/h
        public int   $humidity,      // %
    ) {}
}

<?php

namespace App\Weather;

/**
 * DTO représentant la prévision météo d'une source pour un horizon donné.
 * Toutes les valeurs peuvent être nulles si la source ne fournit pas la variable.
 */
readonly class ForecastData
{
    public function __construct(
        public int $horizon,                   // Nombre de jours dans le futur : 0, 5, 10 ou 14
        public \DateTimeImmutable $targetDate, // Date cible de la prévision
        public ?float $tempMax,                // Température max journalière (°C)
        public ?float $tempMin,                // Température min journalière (°C)
        public ?float $precipitation,          // Précipitations cumulées (mm)
        public ?int $rainProbability,          // Probabilité de pluie (0-100 %)
        public ?float $windSpeed,              // Vitesse max du vent (km/h)
        public ?int $humidity,                 // Humidité relative moyenne (%)
    ) {}
}

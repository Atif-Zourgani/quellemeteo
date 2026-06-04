<?php

namespace App\Weather;

interface WeatherProviderInterface
{
    public function getName(): string;

    public function getSlug(): string;

    /**
     * Retourne les prévisions pour les horizons J0, J+5, J+10, J+14.
     *
     * @return array<int, ForecastData> Indexé par horizon (0, 5, 10, 14)
     */
    public function getForecasts(float $lat, float $lon): array;
}

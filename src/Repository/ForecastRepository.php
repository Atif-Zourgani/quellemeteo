<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Forecast;
use App\Entity\WeatherSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ForecastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forecast::class);
    }

    public function findExisting(City $city, WeatherSource $source, \DateTimeImmutable $collectedDate, int $horizon): ?Forecast
    {
        return $this->findOneBy([
            'city'          => $city,
            'weatherSource' => $source,
            'collectedDate' => $collectedDate,
            'horizon'       => $horizon,
        ]);
    }
}

<?php

namespace App\Repository;

use App\Entity\ActualWeather;
use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActualWeatherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActualWeather::class);
    }

    public function findForCityAndDate(City $city, \DateTimeImmutable $date): ?ActualWeather
    {
        return $this->findOneBy(['city' => $city, 'date' => $date]);
    }
}

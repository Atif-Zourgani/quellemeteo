<?php

namespace App\Repository;

use App\Entity\WeatherSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WeatherSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherSource::class);
    }

    public function findActiveBySlug(string $slug): ?WeatherSource
    {
        return $this->findOneBy(['slug' => $slug, 'isActive' => true]);
    }

    /** @return WeatherSource[] */
    public function findAllActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}

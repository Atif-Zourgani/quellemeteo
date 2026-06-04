<?php

namespace App\DataFixtures;

use App\Entity\WeatherSource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WeatherSourceFixtures extends Fixture
{
    private const SOURCES = [
        ['name' => 'Open-Meteo',      'slug' => 'open-meteo',      'color' => '#16a34a', 'maxHorizonDays' => 16,  'isActive' => true],
        ['name' => 'OpenWeatherMap',  'slug' => 'openweathermap',  'color' => '#f97316', 'maxHorizonDays' => 7,   'isActive' => false],
        ['name' => 'Visual Crossing', 'slug' => 'visual-crossing', 'color' => '#8b5cf6', 'maxHorizonDays' => 15,  'isActive' => false],
        ['name' => 'WeatherAPI',      'slug' => 'weatherapi',      'color' => '#0891b2', 'maxHorizonDays' => 14,  'isActive' => false],
        ['name' => 'Météo-France',    'slug' => 'meteo-france',    'color' => '#003189', 'maxHorizonDays' => 14,  'isActive' => false],
        ['name' => 'Infoclimat',      'slug' => 'infoclimat',      'color' => '#be185d', 'maxHorizonDays' => 10,  'isActive' => false],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SOURCES as $data) {
            $source = (new WeatherSource())
                ->setName($data['name'])
                ->setSlug($data['slug'])
                ->setColor($data['color'])
                ->setMaxHorizonDays($data['maxHorizonDays'])
                ->setIsActive($data['isActive']);

            $manager->persist($source);
        }

        $manager->flush();
    }
}

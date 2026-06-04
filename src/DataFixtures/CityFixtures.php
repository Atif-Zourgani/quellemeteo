<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture
{
    private const CITIES = [
        // Zone Océanique
        ['name' => 'Brest',             'slug' => 'brest',            'lat' => 48.3904,  'lon' => -4.4861, 'zone' => 'oceanique'],
        ['name' => 'Rennes',            'slug' => 'rennes',           'lat' => 48.1173,  'lon' => -1.6778, 'zone' => 'oceanique'],
        ['name' => 'Nantes',            'slug' => 'nantes',           'lat' => 47.2184,  'lon' => -1.5536, 'zone' => 'oceanique'],
        ['name' => 'Caen',              'slug' => 'caen',             'lat' => 49.1829,  'lon' => -0.3707, 'zone' => 'oceanique'],
        ['name' => 'Bordeaux',          'slug' => 'bordeaux',         'lat' => 44.8378,  'lon' => -0.5792, 'zone' => 'oceanique'],
        // Zone Semi-Continentale
        ['name' => 'Paris',             'slug' => 'paris',            'lat' => 48.8566,  'lon' =>  2.3522, 'zone' => 'semi-continentale'],
        ['name' => 'Lille',             'slug' => 'lille',            'lat' => 50.6292,  'lon' =>  3.0573, 'zone' => 'semi-continentale'],
        ['name' => 'Reims',             'slug' => 'reims',            'lat' => 49.2583,  'lon' =>  4.0317, 'zone' => 'semi-continentale'],
        // Zone Continentale
        ['name' => 'Strasbourg',        'slug' => 'strasbourg',       'lat' => 48.5734,  'lon' =>  7.7521, 'zone' => 'continentale'],
        ['name' => 'Dijon',             'slug' => 'dijon',            'lat' => 47.3220,  'lon' =>  5.0415, 'zone' => 'continentale'],
        ['name' => 'Clermont-Ferrand',  'slug' => 'clermont-ferrand', 'lat' => 45.7772,  'lon' =>  3.0870, 'zone' => 'continentale'],
        ['name' => 'Lyon',              'slug' => 'lyon',             'lat' => 45.7578,  'lon' =>  4.8320, 'zone' => 'continentale'],
        // Zone Méditerranéenne
        ['name' => 'Marseille',         'slug' => 'marseille',        'lat' => 43.2965,  'lon' =>  5.3698, 'zone' => 'mediterraneenne'],
        ['name' => 'Montpellier',       'slug' => 'montpellier',      'lat' => 43.6119,  'lon' =>  3.8772, 'zone' => 'mediterraneenne'],
        ['name' => 'Nice',              'slug' => 'nice',             'lat' => 43.7102,  'lon' =>  7.2620, 'zone' => 'mediterraneenne'],
        // Zone Montagne / Sud-Ouest
        ['name' => 'Grenoble',          'slug' => 'grenoble',         'lat' => 45.1885,  'lon' =>  5.7245, 'zone' => 'montagne-sudouest'],
        ['name' => 'Chamonix',          'slug' => 'chamonix',         'lat' => 45.9237,  'lon' =>  6.8694, 'zone' => 'montagne-sudouest'],
        ['name' => 'Toulouse',          'slug' => 'toulouse',         'lat' => 43.6047,  'lon' =>  1.4442, 'zone' => 'montagne-sudouest'],
        ['name' => 'Biarritz',          'slug' => 'biarritz',         'lat' => 43.4832,  'lon' => -1.5586, 'zone' => 'montagne-sudouest'],
        ['name' => 'Tarbes',            'slug' => 'tarbes',           'lat' => 43.2327,  'lon' =>  0.0781, 'zone' => 'montagne-sudouest'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CITIES as $data) {
            $city = (new City())
                ->setName($data['name'])
                ->setSlug($data['slug'])
                ->setLatitude($data['lat'])
                ->setLongitude($data['lon'])
                ->setZone($data['zone']);

            $manager->persist($city);
        }

        $manager->flush();
    }
}

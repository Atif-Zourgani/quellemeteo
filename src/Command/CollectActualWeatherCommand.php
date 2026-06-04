<?php

namespace App\Command;

use App\Weather\ActualWeatherService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:collect-actual-weather',
    description: 'Collecte la météo réelle d\'hier (ERA5) pour les 21 villes. À lancer chaque matin via cron.'
)]
class CollectActualWeatherCommand extends Command
{
    // Les 21 villes avec leurs coordonnées et leur zone climatique
    private const CITIES = [
        ['name' => 'Paris',             'lat' => 48.8566,  'lon' =>  2.3522, 'zone' => 'semi-continentale'],
        ['name' => 'Lyon',              'lat' => 45.7578,  'lon' =>  4.8320, 'zone' => 'continentale'],
        ['name' => 'Marseille',         'lat' => 43.2965,  'lon' =>  5.3698, 'zone' => 'mediterraneenne'],
        ['name' => 'Bordeaux',          'lat' => 44.8378,  'lon' => -0.5792, 'zone' => 'oceanique'],
        ['name' => 'Lille',             'lat' => 50.6292,  'lon' =>  3.0573, 'zone' => 'semi-continentale'],
        ['name' => 'Strasbourg',        'lat' => 48.5734,  'lon' =>  7.7521, 'zone' => 'continentale'],
        ['name' => 'Nantes',            'lat' => 47.2184,  'lon' => -1.5536, 'zone' => 'oceanique'],
        ['name' => 'Toulouse',          'lat' => 43.6047,  'lon' =>  1.4442, 'zone' => 'montagne-sudouest'],
        ['name' => 'Nice',              'lat' => 43.7102,  'lon' =>  7.2620, 'zone' => 'mediterraneenne'],
        ['name' => 'Rennes',            'lat' => 48.1173,  'lon' => -1.6778, 'zone' => 'oceanique'],
        ['name' => 'Grenoble',          'lat' => 45.1885,  'lon' =>  5.7245, 'zone' => 'montagne-sudouest'],
        ['name' => 'Montpellier',       'lat' => 43.6119,  'lon' =>  3.8772, 'zone' => 'mediterraneenne'],
        ['name' => 'Brest',             'lat' => 48.3904,  'lon' => -4.4861, 'zone' => 'oceanique'],
        ['name' => 'Dijon',             'lat' => 47.3220,  'lon' =>  5.0415, 'zone' => 'continentale'],
        ['name' => 'Caen',              'lat' => 49.1829,  'lon' => -0.3707, 'zone' => 'oceanique'],
        ['name' => 'Reims',             'lat' => 49.2583,  'lon' =>  4.0317, 'zone' => 'semi-continentale'],
        ['name' => 'Clermont-Ferrand',  'lat' => 45.7772,  'lon' =>  3.0870, 'zone' => 'continentale'],
        ['name' => 'Biarritz',          'lat' => 43.4832,  'lon' => -1.5586, 'zone' => 'montagne-sudouest'],
        ['name' => 'Tarbes',            'lat' => 43.2327,  'lon' =>  0.0781, 'zone' => 'montagne-sudouest'],
        ['name' => 'Chamonix',          'lat' => 45.9237,  'lon' =>  6.8694, 'zone' => 'montagne-sudouest'],
    ];

    public function __construct(private readonly ActualWeatherService $actualWeatherService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $yesterday = (new \DateTimeImmutable('yesterday'))->format('Y-m-d');
        $io->title("Collecte météo réelle — $yesterday — " . count(self::CITIES) . ' villes');

        $success = 0;
        $errors  = [];
        $rows    = [];

        foreach (self::CITIES as $city) {
            try {
                $data = $this->actualWeatherService->getYesterday($city['lat'], $city['lon']);

                $rows[] = [
                    $city['name'],
                    $data->tempMax . ' °C',
                    $data->tempMin . ' °C',
                    $data->precipitation . ' mm',
                    $data->windSpeed . ' km/h',
                    $data->humidity . ' %',
                ];

                $success++;

                // TODO : Étape suivante — persister $data en base via Doctrine

            } catch (\Throwable $e) {
                $errors[] = "{$city['name']} : " . $e->getMessage();
            }
        }

        $io->table(
            ['Ville', 'Tmax', 'Tmin', 'Précipitations', 'Vent', 'Humidité'],
            $rows
        );

        if (!empty($errors)) {
            $io->section('Erreurs');
            foreach ($errors as $error) {
                $io->error($error);
            }
        }

        $io->success("$success/" . count(self::CITIES) . ' villes collectées pour le ' . $yesterday);

        return empty($errors) ? Command::SUCCESS : Command::FAILURE;
    }
}

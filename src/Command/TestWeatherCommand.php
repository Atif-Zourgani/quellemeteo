<?php

namespace App\Command;

use App\Weather\Provider\OpenMeteoService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:test-weather', description: 'Teste Open-Meteo sur toutes les villes, tous les horizons, toutes les variables')]
class TestWeatherCommand extends Command
{
    // Les 20 villes du projet avec leurs coordonnées
    private const CITIES = [
        'Paris'            => [48.8566,  2.3522],
        'Lyon'             => [45.7578,  4.8320],
        'Marseille'        => [43.2965,  5.3698],
        'Bordeaux'         => [44.8378, -0.5792],
        'Lille'            => [50.6292,  3.0573],
        'Strasbourg'       => [48.5734,  7.7521],
        'Nantes'           => [47.2184, -1.5536],
        'Toulouse'         => [43.6047,  1.4442],
        'Nice'             => [43.7102,  7.2620],
        'Rennes'           => [48.1173, -1.6778],
        'Grenoble'         => [45.1885,  5.7245],
        'Montpellier'      => [43.6119,  3.8772],
        'Brest'            => [48.3904, -4.4861],
        'Dijon'            => [47.3220,  5.0415],
        'Caen'             => [49.1829, -0.3707],
        'Reims'            => [49.2583,  4.0317],
        'Clermont-Ferrand' => [45.7772,  3.0870],
        'Biarritz'         => [43.4832, -1.5586],
        'Tarbes'           => [43.2327,  0.0781],
        'Chamonix'         => [45.9237,  6.8694],
    ];

    private const HORIZONS = [0, 5, 10, 14];
    private const VARIABLES = ['tempMax', 'tempMin', 'precipitation', 'rainProbability', 'windSpeed', 'humidity'];

    public function __construct(private readonly OpenMeteoService $openMeteo)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Audit complet Open-Meteo — 21 villes × 4 horizons × 6 variables');

        $nullCounts = [];
        $errors     = [];
        $sample     = null; // On garde un exemple de ville pour affichage détaillé

        foreach (self::CITIES as $city => [$lat, $lon]) {
            try {
                $forecasts = $this->openMeteo->getForecasts($lat, $lon);

                // Vérification que tous les horizons sont présents
                foreach (self::HORIZONS as $horizon) {
                    if (!isset($forecasts[$horizon])) {
                        $errors[] = "$city : horizon J+$horizon MANQUANT";
                        continue;
                    }

                    $f = $forecasts[$horizon];

                    // Compte les variables nulles par horizon
                    foreach (self::VARIABLES as $var) {
                        if ($f->$var === null) {
                            $key = "J+$horizon / $var";
                            $nullCounts[$key] = ($nullCounts[$key] ?? 0) + 1;
                        }
                    }
                }

                if ($sample === null) {
                    $sample = ['city' => $city, 'forecasts' => $forecasts];
                }

            } catch (\Throwable $e) {
                $errors[] = "$city : ERREUR — " . $e->getMessage();
            }
        }

        // Affiche un exemple complet (Paris)
        if ($sample) {
            $io->section("Exemple détaillé — {$sample['city']}");
            $rows = [];
            foreach ($sample['forecasts'] as $horizon => $f) {
                $rows[] = [
                    "J+$horizon ({$f->targetDate->format('d/m')})",
                    $f->tempMax    !== null ? $f->tempMax . ' °C'    : '❌ null',
                    $f->tempMin    !== null ? $f->tempMin . ' °C'    : '❌ null',
                    $f->precipitation !== null ? $f->precipitation . ' mm' : '❌ null',
                    $f->rainProbability !== null ? $f->rainProbability . ' %' : '❌ null',
                    $f->windSpeed  !== null ? $f->windSpeed . ' km/h' : '❌ null',
                    $f->humidity   !== null ? $f->humidity . ' %'    : '❌ null',
                ];
            }
            $io->table(['Horizon', 'Temp max', 'Temp min', 'Précip.', 'Proba pluie', 'Vent', 'Humidité'], $rows);
        }

        // Rapport des variables nulles
        $io->section('Variables nulles (sur ' . count(self::CITIES) . ' villes)');
        if (empty($nullCounts)) {
            $io->success('Toutes les variables sont présentes sur tous les horizons.');
        } else {
            $rows = [];
            foreach ($nullCounts as $key => $count) {
                $rows[] = [$key, "$count/" . count(self::CITIES) . ' villes', $count === count(self::CITIES) ? '❌ Toujours null' : '⚠️  Parfois null'];
            }
            $io->table(['Variable / Horizon', 'Villes concernées', 'Statut'], $rows);
        }

        // Rapport des erreurs
        if (!empty($errors)) {
            $io->section('Erreurs rencontrées');
            foreach ($errors as $error) {
                $io->error($error);
            }
            return Command::FAILURE;
        }

        $io->success('Audit terminé — ' . count(self::CITIES) . ' villes testées.');
        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Entity\ActualWeather;
use App\Entity\City;
use App\Repository\ActualWeatherRepository;
use App\Repository\CityRepository;
use App\Weather\ActualWeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:collect-actual-weather',
    description: 'Collecte la météo réelle d\'hier (ERA5) pour les 20 villes. À lancer chaque matin via cron.'
)]
class CollectActualWeatherCommand extends Command
{
    public function __construct(
        private readonly ActualWeatherService     $actualWeatherService,
        private readonly CityRepository           $cityRepository,
        private readonly ActualWeatherRepository  $actualWeatherRepository,
        private readonly EntityManagerInterface   $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $yesterday = new \DateTimeImmutable('yesterday');
        $io->title('Collecte météo réelle ERA5 — ' . $yesterday->format('d/m/Y') . ' — 20 villes');

        $cities = $this->cityRepository->findAll();

        if (empty($cities)) {
            $io->error('Aucune ville en base. Lance d\'abord : php bin/console doctrine:fixtures:load');
            return Command::FAILURE;
        }

        $success = 0;
        $skipped = 0;
        $errors  = [];
        $rows    = [];

        foreach ($cities as $city) {
            // Évite les doublons si la commande est relancée sur le même jour
            if ($this->actualWeatherRepository->findForCityAndDate($city, $yesterday)) {
                $skipped++;
                continue;
            }

            try {
                $data = $this->actualWeatherService->getYesterday($city->getLatitude(), $city->getLongitude());

                $actualWeather = (new ActualWeather())
                    ->setCity($city)
                    ->setDate($yesterday)
                    ->setTempMax($data->tempMax)
                    ->setTempMin($data->tempMin)
                    ->setPrecipitation($data->precipitation)
                    ->setWindSpeed($data->windSpeed)
                    ->setHumidity($data->humidity);

                $this->entityManager->persist($actualWeather);

                $rows[] = [
                    $city->getName(),
                    $data->tempMax . ' °C',
                    $data->tempMin . ' °C',
                    $data->precipitation . ' mm',
                    $data->windSpeed . ' km/h',
                    $data->humidity . ' %',
                ];

                $success++;

            } catch (\Throwable $e) {
                $errors[] = "{$city->getName()} : " . $e->getMessage();
            }
        }

        $this->entityManager->flush();

        if (!empty($rows)) {
            $io->table(['Ville', 'Tmax', 'Tmin', 'Précipitations', 'Vent', 'Humidité'], $rows);
        }

        if ($skipped > 0) {
            $io->note("$skipped ville(s) déjà collectée(s) pour cette date — ignorée(s).");
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $io->error($error);
            }
            return Command::FAILURE;
        }

        $io->success("$success ville(s) collectée(s) et enregistrée(s) pour le " . $yesterday->format('d/m/Y'));
        return Command::SUCCESS;
    }
}

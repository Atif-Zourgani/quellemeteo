<?php

namespace App\Command;

use App\Entity\Forecast;
use App\Repository\CityRepository;
use App\Repository\ForecastRepository;
use App\Repository\WeatherSourceRepository;
use App\Weather\WeatherProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:collect-forecasts',
    description: 'Collecte les prévisions météo (J0/J+5/J+10/J+14) pour toutes les sources actives. À lancer chaque matin via cron.'
)]
class CollectForecastsCommand extends Command
{
    /**
     * @param iterable<WeatherProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable                $providers,
        private readonly CityRepository          $cityRepository,
        private readonly WeatherSourceRepository $weatherSourceRepository,
        private readonly ForecastRepository      $forecastRepository,
        private readonly EntityManagerInterface  $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $today = new \DateTimeImmutable('today');
        $io->title('Collecte des prévisions — ' . $today->format('d/m/Y'));

        $cities  = $this->cityRepository->findAll();
        $totalOk = 0;
        $errors  = [];

        foreach ($this->providers as $provider) {
            $source = $this->weatherSourceRepository->findActiveBySlug($provider->getSlug());

            // On ignore les sources non actives en base (API non encore validée)
            if (!$source) {
                continue;
            }

            $io->section($provider->getName());
            $sourceOk = 0;

            foreach ($cities as $city) {
                try {
                    $forecasts = $provider->getForecasts($city->getLatitude(), $city->getLongitude());

                    foreach ($forecasts as $horizon => $data) {
                        // Évite les doublons si la commande est relancée le même jour
                        if ($this->forecastRepository->findExisting($city, $source, $today, $horizon)) {
                            continue;
                        }

                        $forecast = (new Forecast())
                            ->setCity($city)
                            ->setWeatherSource($source)
                            ->setCollectedDate($today)
                            ->setTargetDate($data->targetDate)
                            ->setHorizon($horizon)
                            ->setTempMax($data->tempMax)
                            ->setTempMin($data->tempMin)
                            ->setPrecipitation($data->precipitation)
                            ->setRainProbability($data->rainProbability)
                            ->setWindSpeed($data->windSpeed)
                            ->setHumidity($data->humidity);

                        $this->entityManager->persist($forecast);
                        $sourceOk++;
                    }

                } catch (\Throwable $e) {
                    $errors[] = "[{$provider->getName()}] {$city->getName()} : " . $e->getMessage();
                }
            }

            $this->entityManager->flush();
            $io->text("  ✓ $sourceOk prévision(s) enregistrée(s)");
            $totalOk += $sourceOk;
        }

        if (!empty($errors)) {
            $io->section('Erreurs');
            foreach ($errors as $error) {
                $io->error($error);
            }
            return Command::FAILURE;
        }

        $io->success("$totalOk prévision(s) enregistrée(s) au total pour le " . $today->format('d/m/Y'));
        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Weather\Provider\OpenMeteoService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:test-weather', description: 'Teste la récupération météo sur Open-Meteo (Paris)')]
class TestWeatherCommand extends Command
{
    public function __construct(private readonly OpenMeteoService $openMeteo)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Test Open-Meteo — Paris (48.8566, 2.3522)');

        $forecasts = $this->openMeteo->getForecasts(48.8566, 2.3522);

        foreach ($forecasts as $horizon => $data) {
            $io->section("J+$horizon — {$data->targetDate->format('d/m/Y')}");
            $io->table(
                ['Variable', 'Valeur'],
                [
                    ['Température max', $data->tempMax !== null ? $data->tempMax . ' °C' : 'N/A'],
                    ['Température min', $data->tempMin !== null ? $data->tempMin . ' °C' : 'N/A'],
                    ['Précipitations',  $data->precipitation !== null ? $data->precipitation . ' mm' : 'N/A'],
                    ['Proba. pluie',    $data->rainProbability !== null ? $data->rainProbability . ' %' : 'N/A'],
                    ['Vent max',        $data->windSpeed !== null ? $data->windSpeed . ' km/h' : 'N/A'],
                    ['Humidité',        $data->humidity !== null ? $data->humidity . ' %' : 'N/A'],
                ]
            );
        }

        $io->success('Open-Meteo OK — ' . count($forecasts) . ' horizons récupérés.');
        return Command::SUCCESS;
    }
}

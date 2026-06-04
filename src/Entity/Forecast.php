<?php

namespace App\Entity;

use App\Repository\ForecastRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForecastRepository::class)]
#[ORM\Table(name: 'forecast')]
#[ORM\UniqueConstraint(name: 'unique_forecast', columns: ['city_id', 'weather_source_id', 'collected_date', 'horizon'])]
class Forecast
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(nullable: false)]
    private City $city;

    #[ORM\ManyToOne(targetEntity: WeatherSource::class, inversedBy: 'forecasts')]
    #[ORM\JoinColumn(nullable: false)]
    private WeatherSource $weatherSource;

    /** Date à laquelle la prévision a été collectée (aujourd'hui) */
    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $collectedDate;

    /** Date cible de la prévision (dans le futur) */
    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $targetDate;

    /** Nombre de jours entre collectedDate et targetDate : 0, 5, 10 ou 14 */
    #[ORM\Column]
    private int $horizon;

    #[ORM\Column(nullable: true)]
    private ?float $tempMax = null;

    #[ORM\Column(nullable: true)]
    private ?float $tempMin = null;

    #[ORM\Column(nullable: true)]
    private ?float $precipitation = null;

    #[ORM\Column(nullable: true)]
    private ?int $rainProbability = null;

    #[ORM\Column(nullable: true)]
    private ?float $windSpeed = null;

    #[ORM\Column(nullable: true)]
    private ?int $humidity = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getCity(): City { return $this->city; }
    public function setCity(City $city): static { $this->city = $city; return $this; }

    public function getWeatherSource(): WeatherSource { return $this->weatherSource; }
    public function setWeatherSource(WeatherSource $weatherSource): static { $this->weatherSource = $weatherSource; return $this; }

    public function getCollectedDate(): \DateTimeImmutable { return $this->collectedDate; }
    public function setCollectedDate(\DateTimeImmutable $collectedDate): static { $this->collectedDate = $collectedDate; return $this; }

    public function getTargetDate(): \DateTimeImmutable { return $this->targetDate; }
    public function setTargetDate(\DateTimeImmutable $targetDate): static { $this->targetDate = $targetDate; return $this; }

    public function getHorizon(): int { return $this->horizon; }
    public function setHorizon(int $horizon): static { $this->horizon = $horizon; return $this; }

    public function getTempMax(): ?float { return $this->tempMax; }
    public function setTempMax(?float $tempMax): static { $this->tempMax = $tempMax; return $this; }

    public function getTempMin(): ?float { return $this->tempMin; }
    public function setTempMin(?float $tempMin): static { $this->tempMin = $tempMin; return $this; }

    public function getPrecipitation(): ?float { return $this->precipitation; }
    public function setPrecipitation(?float $precipitation): static { $this->precipitation = $precipitation; return $this; }

    public function getRainProbability(): ?int { return $this->rainProbability; }
    public function setRainProbability(?int $rainProbability): static { $this->rainProbability = $rainProbability; return $this; }

    public function getWindSpeed(): ?float { return $this->windSpeed; }
    public function setWindSpeed(?float $windSpeed): static { $this->windSpeed = $windSpeed; return $this; }

    public function getHumidity(): ?int { return $this->humidity; }
    public function setHumidity(?int $humidity): static { $this->humidity = $humidity; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

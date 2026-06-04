<?php

namespace App\Entity;

use App\Repository\ActualWeatherRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActualWeatherRepository::class)]
#[ORM\Table(name: 'actual_weather')]
#[ORM\UniqueConstraint(name: 'unique_city_date', columns: ['city_id', 'date'])]
class ActualWeather
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'actualWeathers')]
    #[ORM\JoinColumn(nullable: false)]
    private City $city;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column]
    private float $tempMax;

    #[ORM\Column]
    private float $tempMin;

    #[ORM\Column]
    private float $precipitation;

    #[ORM\Column]
    private float $windSpeed;

    #[ORM\Column]
    private int $humidity;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $collectedAt;

    public function __construct()
    {
        $this->collectedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getCity(): City { return $this->city; }
    public function setCity(City $city): static { $this->city = $city; return $this; }

    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }

    public function getTempMax(): float { return $this->tempMax; }
    public function setTempMax(float $tempMax): static { $this->tempMax = $tempMax; return $this; }

    public function getTempMin(): float { return $this->tempMin; }
    public function setTempMin(float $tempMin): static { $this->tempMin = $tempMin; return $this; }

    public function getPrecipitation(): float { return $this->precipitation; }
    public function setPrecipitation(float $precipitation): static { $this->precipitation = $precipitation; return $this; }

    public function getWindSpeed(): float { return $this->windSpeed; }
    public function setWindSpeed(float $windSpeed): static { $this->windSpeed = $windSpeed; return $this; }

    public function getHumidity(): int { return $this->humidity; }
    public function setHumidity(int $humidity): static { $this->humidity = $humidity; return $this; }

    public function getCollectedAt(): \DateTimeImmutable { return $this->collectedAt; }
}

<?php

namespace App\Entity;

use App\Repository\WeatherSourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeatherSourceRepository::class)]
#[ORM\Table(name: 'weather_source')]
class WeatherSource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 100, unique: true)]
    private string $slug;

    #[ORM\Column(length: 7)]
    private string $color; // Couleur identitaire hex ex: #003189

    #[ORM\Column]
    private int $maxHorizonDays; // Portée max en jours

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\OneToMany(targetEntity: Forecast::class, mappedBy: 'weatherSource', orphanRemoval: true)]
    private Collection $forecasts;

    public function __construct()
    {
        $this->forecasts = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getColor(): string { return $this->color; }
    public function setColor(string $color): static { $this->color = $color; return $this; }

    public function getMaxHorizonDays(): int { return $this->maxHorizonDays; }
    public function setMaxHorizonDays(int $maxHorizonDays): static { $this->maxHorizonDays = $maxHorizonDays; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getForecasts(): Collection { return $this->forecasts; }
}

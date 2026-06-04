<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: 'city')]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 100, unique: true)]
    private string $slug;

    #[ORM\Column]
    private float $latitude;

    #[ORM\Column]
    private float $longitude;

    #[ORM\Column(length: 50)]
    private string $zone;

    #[ORM\OneToMany(targetEntity: ActualWeather::class, mappedBy: 'city', orphanRemoval: true)]
    private Collection $actualWeathers;

    public function __construct()
    {
        $this->actualWeathers = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getLatitude(): float { return $this->latitude; }
    public function setLatitude(float $latitude): static { $this->latitude = $latitude; return $this; }

    public function getLongitude(): float { return $this->longitude; }
    public function setLongitude(float $longitude): static { $this->longitude = $longitude; return $this; }

    public function getZone(): string { return $this->zone; }
    public function setZone(string $zone): static { $this->zone = $zone; return $this; }

    public function getActualWeathers(): Collection { return $this->actualWeathers; }
}

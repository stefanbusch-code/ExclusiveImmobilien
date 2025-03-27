<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $location_id = null;

    #[ORM\Column]
    private ?int $location_zipcode = null;

    #[ORM\Column(length: 255)]
    private ?string $location_town = null;

    #[ORM\Column(length: 255)]
    private ?string $location_street = null;

    #[ORM\Column]
    private ?int $location_streetnumber = null;

    public function getId(): ?int
    {
        return $this->location_id;
    }

    public function getLocationZipcode(): ?int
    {
        return $this->location_zipcode;
    }

    public function setLocationZipcode(int $location_zipcode): static
    {
        $this->location_zipcode = $location_zipcode;

        return $this;
    }

    public function getLocationTown(): ?string
    {
        return $this->location_town;
    }

    public function setLocationTown(string $location_town): static
    {
        $this->location_town = $location_town;

        return $this;
    }

    public function getLocationStreet(): ?string
    {
        return $this->location_street;
    }

    public function setLocationStreet(string $location_street): static
    {
        $this->location_street = $location_street;

        return $this;
    }

    public function getLocationStreetnumber(): ?int
    {
        return $this->location_streetnumber;
    }

    public function setLocationStreetnumber(int $location_streetnumber): static
    {
        $this->location_streetnumber = $location_streetnumber;

        return $this;
    }
}

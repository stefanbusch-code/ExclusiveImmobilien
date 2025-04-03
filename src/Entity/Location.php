<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $location_zipcode = null;

    #[ORM\Column(length: 255)]
    private ?string $location_town = null;

    #[ORM\Column(length: 255)]
    private ?string $location_street = null;

    #[ORM\Column]
    private ?int $location_streetnumber = null;

    /**
     * @var Collection<int, Property>
     */
    #[ORM\OneToMany(targetEntity: Property::class, mappedBy: 'location', cascade:['persist', 'remove'])]
    private Collection $properties;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    public function getId(): ?int
    {
        return $this->id;
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


    /**
     * @return Collection<int, Property>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): static
    {
        if (!$this->properties->contains($property)) {
            $this->properties->add($property);
            $property->setLocation($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): static
    {
        if ($this->properties->removeElement($property)) {
            // set the owning side to null (unless already changed)
            if ($property->getLocation() === $this) {
                $property->setLocation(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) ($this->location_town ?? 'Unbekannt');
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $property_title = null;

    #[ORM\Column(length: 255)]
    private ?string $property_discription = null;

    #[ORM\Column(nullable: true)]
    private ?float $preis = null;

    #[ORM\Column(length: 255, nullable: true)]
    private $bild;


    #[ORM\ManyToOne(targetEntity: Location::class, inversedBy: 'properties', cascade: ['persist'])]
    #[ORM\JoinColumn(name: "location_id", referencedColumnName: "id", nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'properties', cascade: ['persist'])]
    #[Orm\JoinColumn(name: "category_id", referencedColumnName: "id", nullable: false)]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPropertyTitle(): ?string
    {
        return $this->property_title;
    }

    public function setPropertyTitle(string $property_title): static
    {
        $this->property_title = $property_title;

        return $this;
    }

    public function getPropertyDiscription(): ?string
    {
        return $this->property_discription;
    }

    public function setPropertyDiscription(string $property_discription): static
    {
        $this->property_discription = $property_discription;

        return $this;
    }

    public function getPreis(): ?float
    {
        return $this->preis;
    }

    public function setPreis(?float $Preis): static
    {
        $this->preis = $Preis;

        return $this;
    }

    public function getBild(): ?string
    {
        return $this->bild;
    }

    public function setBild(?string $bild): static
    {
        $this->bild = $bild;

        return $this;
    }
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }



}
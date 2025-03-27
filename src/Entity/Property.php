<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
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
}

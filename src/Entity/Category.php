<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $discription = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\OneToMany(targetEntity: Property::class,mappedBy: 'category')]
    private Collection $properties;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    public function getDiscription(): ?string
    {
        return $this->discription;
    }

    public function setDiscription(string $discription): static
    {
        $this->discription = $discription;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }
    public function addProperty(Property $property): static
    {
        if (!$this->properties->contains($property))
        {
            $this->properties->add($property);
            $property->setCategory($this);
        }
        return $this;
    }
    public function removeProperty(Property $property): static
    {
        if ($this->properties->removeElement($property))
        {
            if ($property->getCategory() === $this)
            {
                $property->setCategory(null);
            }
        }
        return $this;
    }

    public function __toString()
    {
        return $this->discription;
    }

}

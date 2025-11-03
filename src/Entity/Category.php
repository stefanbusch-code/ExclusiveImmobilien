<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category - Entität für Immobilien-Kategorien
 *
 * Definiert die Klassifizierung von Immobilien (z.B. "Apartments zum Kaufen",
 * "Häuser zum Mieten"). Wird für Kategorie-basierte Filterung und Navigation
 * verwendet.
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    /**
     * Eindeutige Identifikationsnummer der Kategorie
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Beschreibung/Name der Kategorie
     * Enthält lesbare Bezeichnungen wie "Apartments zum Kaufen"
     */
    #[ORM\Column(length: 255)]
    private ?string $discription = null;

    /**
     * Collection der zugehörigen Immobilien (1:n Beziehung)
     */
    #[ORM\OneToMany(targetEntity: Property::class,mappedBy: 'category')]
    private Collection $properties;

    /**
     * Konstruktor - Initialisiert die Properties-Collection
     */
    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    // ==================== GETTER & SETTER ====================

    /**
     * Getter für die Kategorie-ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gibt die Kategorie-Beschreibung zurück
     */
    public function getDiscription(): ?string
    {
        return $this->discription;
    }

    /**
     * Setzt die Kategorie-Beschreibung
     */
    public function setDiscription(string $discription): static
    {
        $this->discription = $discription;

        return $this;
    }

    /**
     * Gibt die Collection aller zugehörigen Immobilien zurück
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    /**
     * Fügt eine Immobilie zu dieser Kategorie hinzu
     */
    public function addProperty(Property $property): static
    {
        if (!$this->properties->contains($property))
        {
            $this->properties->add($property);
            $property->setCategory($this);
        }
        return $this;
    }

    /**
     * Entfernt eine Immobilie aus dieser Kategorie
     */
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

    /**
     * String-Repräsentation für Dropdowns und Anzeigen
     */
    public function __toString()
    {
        return $this->discription;
    }

}

<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 *  Location - Entität für Immobilien-Standorte
 *
 *  Repräsentiert einen physischen Standort mit Adressdaten.
 *  Enthält Beziehungen zu mehreren Property-Entitäten (1:n).
 *  Wird für Standort-basierte Filterung und Geodaten verwendet.
 *
 */
#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    /**
     * Eindeutige Identifikationsnummer des Standorts
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Postleitzahl des Standorts
     */
    #[ORM\Column]
    private ?int $location_zipcode = null;

    /**
     * Stadtname des Standorts
     * Wird für die Standortfilterung verwendet
     */
    #[ORM\Column(length: 255)]
    private ?string $location_town = null;

    /**
     * Straßenname des Standorts
     */
    #[ORM\Column(length: 255)]
    private ?string $location_street = null;

    /**
     * Hausnummer des Standorts
     */
    #[ORM\Column]
    private ?int $location_streetnumber = null;

    /**
     * Collection der zugehörigen Immobilien (1:n Beziehung)
     *
     * @var Collection<int, Property>
     */
    #[ORM\OneToMany(targetEntity: Property::class, mappedBy: 'location', cascade:['persist', 'remove'])]
    private Collection $properties;

    /**
     * Konstruktor - Initialisiert die Properties-Collection
     */
    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    /**
     * Region/Bundesland des Standorts
     * Wird für die Regionsfilterung verwendet
     */
    #[ORM\Column(length: 255)]
    private ?string $region = null;

    /**
     * Land des Standorts
     * Wird für die Länderfilterung verwendet
     */
    #[ORM\Column(length: 255)]
    private ?string $country = null;

    // ==================== GETTER & SETTER ====================

    /**
     * Gibt die ID des Standorts zurück
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gibt die Postleitzahl zurück
     */
    public function getLocationZipcode(): ?int
    {
        return $this->location_zipcode;
    }

    /**
     * Setzt die Postleitzahl
     */
    public function setLocationZipcode(int $location_zipcode): static
    {
        $this->location_zipcode = $location_zipcode;

        return $this;
    }

    /**
     * Gibt den Stadtnamen zurück
     */
    public function getLocationTown(): ?string
    {
        return $this->location_town;
    }

    /**
     * Setzt den Stadtnamen
     */
    public function setLocationTown(string $location_town): static
    {
        $this->location_town = $location_town;

        return $this;
    }

    /**
     * Gibt den Straßennamen zurück
     */
    public function getLocationStreet(): ?string
    {
        return $this->location_street;
    }

    /**
     * Setzt den Straßennamen
     */
    public function setLocationStreet(string $location_street): static
    {
        $this->location_street = $location_street;

        return $this;
    }

    /**
     * Gibt die Hausnummer zurück
     */
    public function getLocationStreetnumber(): ?int
    {
        return $this->location_streetnumber;
    }

    /**
     * Setzt die Hausnummer
     */
    public function setLocationStreetnumber(int $location_streetnumber): static
    {
        $this->location_streetnumber = $location_streetnumber;

        return $this;
    }

    /**
     * Gibt die Region zurück
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * Setzt die Region
     */
    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Gibt das Land zurück
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Setzt das Land
     */
    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Gibt die Collection aller zugehörigen Immobilien zurück
     *
     * @return Collection<int, Property>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    /**
     * Fügt eine Immobilie zu diesem Standort hinzu
     */
    public function addProperty(Property $property): static
    {
        if (!$this->properties->contains($property)) {
            $this->properties->add($property);
            $property->setLocation($this);
        }

        return $this;
    }

    /**
     * Entfernt eine Immobilie von diesem Standort
     */
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


    /**
     * String-Repräsentation für Dropdowns und Anzeigen
     */
    public function __toString()
    {
        return (string) ($this->location_town ?? 'Unbekannt');
    }

}

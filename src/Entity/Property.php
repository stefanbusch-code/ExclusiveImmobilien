<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Property - Hauptentität für Immobilien
 *
 * Repräsentiert eine einzelne Immobilie mit allen relevanten Daten.
 * Enthält Beziehungen zu Location (Standort) und Category (Kategorie).
 * Ist die zentrale Entität für Such-, Filter- und Anzeigefunktionen.
 */

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    /**
     * Eindeutige Identifikationsnummer der Immobilie
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Titel der Immobilie
     * Wird in Übersichten und als Hauptüberschrift verwendet
     */
    #[ORM\Column(length: 255)]
    private ?string $property_title = null;

    /**
     * Detaillierte Beschreibung der Immobilie
     * Enthält Features, Ausstattung und Besonderheiten
     */
    #[ORM\Column(length: 255)]
    private ?string $property_discription = null;

    /**
     * Preis der Immobilie in Euro
     * Wird für Preis-Filterung und Sortierung verwendet
     */
    #[ORM\Column(nullable: true)]
    private ?int $preis = null;

    /**
     * Dateiname des Immobilienbildes
     * Verweist auf eine Bilddatei im /public/Bilder/ Verzeichnis
     */
    #[ORM\Column(length: 255, nullable: true)]
    private $bild;

    /**
     * Standort der Immobilie (n:1 Beziehung)
     * Jede Immobilie hat genau einen Standort
     */
    #[ORM\ManyToOne(targetEntity: Location::class, inversedBy: 'properties', cascade: ['persist'])]
    #[ORM\JoinColumn(name: "location_id", referencedColumnName: "id", nullable: false)]
    private ?Location $location = null;

    /**
     * Kategorie der Immobilie (n:1 Beziehung)
     * Jede Immobilie gehört zu genau einer Kategorie
     */
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'properties', cascade: ['persist'])]
    #[Orm\JoinColumn(name: "category_id", referencedColumnName: "id", nullable: false)]
    private ?Category $category = null;

    // ==================== GETTER & SETTER ====================

    /**
     * Gibt die ID der Immobilie zurück
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gibt den Immobilientitel zurück
     */
    public function getPropertyTitle(): ?string
    {
        return $this->property_title;
    }

    /**
     * Setzt den Immobilientitel
     */
    public function setPropertyTitle(string $property_title): static
    {
        $this->property_title = $property_title;

        return $this;
    }

    /**
     * Gibt die Immobilienbeschreibung zurück
     */
    public function getPropertyDiscription(): ?string
    {
        return $this->property_discription;
    }

    /**
     * Setzt die Immobilienbeschreibung
     */
    public function setPropertyDiscription(string $property_discription): static
    {
        $this->property_discription = $property_discription;

        return $this;
    }

    /**
     * Gibt den Preis der Immobilie zurück
     */
    public function getPreis(): ?int
    {
        return $this->preis;
    }

    /**
     * Setzt den Preis der Immobilie
     */
    public function setPreis(?int $Preis): static
    {
        $this->preis = $Preis;

        return $this;
    }

    /**
     * Gibt den Bild-Dateinamen zurück
     */
    public function getBild(): ?string
    {
        return $this->bild;
    }

    /**
     * Setzt den Bild-Dateinamen
     */
    public function setBild(?string $bild): static
    {
        $this->bild = $bild;

        return $this;
    }

    /**
     * Gibt den Standort der Immobilie zurück
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * Setzt den Standort der Immobilie
     */
    public function setLocation(?Location $location): static
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Gibt die Kategorie der Immobilie zurück
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setzt die Kategorie der Immobilie
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }
}
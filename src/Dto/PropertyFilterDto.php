<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * PropertyFilterDto - Data Transfer Objekt für Immobilienparameter
 *
 * sammelt und validiert alle Such- und Filtereingaben für die Immobiliensuche.
 * Wird verwendet zur typsicheren Übergabe von Suchparametern zwischen
 * Controller und Repository mit integrierter Validierung.
 * Validiert werden die Filterparameter auf syntaktischer Korrektheit und Typsicherheit
 * bevor sie an die Datenbank oder Service weitergeleitet werden.
 *
 */

class PropertyFilterDto
{
    /**
     * Durchsucht die Suchbegriffe der Volltextsuche
     * Durchsucht Titel, Beschreibung, Standort und Kategorie
     *
     * @var string|null
     */
    #[Assert\Length(max: 100, maxMessage: 'Suchbegriffe dürfen max 100 Zeichen lang sein')]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\s\-]*$/u',
        message: 'Nur Buchstaben, Zahlen und leerzeichen erlaubt.'
    )]
    public ?string $search = null;

    /**
     * Preisbereich für die Filterung
     * Format "minPreis-maxPreis"
     *
     * @var string|null
     */
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\s\-]*$/u',
        message: 'Ungültiges Preisformat. Beispiel: 1000000-5000000'
    )]
    public ?string $preis = null;

    /**
     * Stadt für die Standortfilterung
     * filtert Immobilien nach bestimmten Städten
     *
     * @var string|null
     */
    #[Assert\Length(
        max: 100,
        maxMessage: 'Städtenamen dürfen maximal 100 Zeichen lang sein.'
    )]
    public ?string $town = null;

    /**
     * Region für die Standortfilterung
     * filtert Immobilien nach bestimmten Regionen/Bundesländer
     *
     * @var string|null
     */
    #[Assert\Length(
        max: 100,
        maxMessage: 'Regionsbezeichnungen dürfen maximal 100 Zeichen lang sein.'
    )]
    public ?string $region = null;

    /**
     * Land für die Standortfilterung
     * filtert Immobilien nach bestimmten Ländern
     *
     * @var string|null
     */
    #[Assert\Length(
        max: 100,
        maxMessage: 'Ländernamen dürfen maximal 100 Zeichen lang sein.'
    )]
    public ?string $country = null;

}
<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LocationRepository - Datenbankzugriff für Standort-Entitäten
 *
 * Enthält spezielle Abfragemethoden für Standort-bezogene Daten
 * zur Unterstützung der Filter- und Suchfunktionalität.
 * Stellt distinct Werte für Dropdown-Menü bereit.
 *
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    /**
     * Konstruktor - Initialisiert das Repository für Location-Entitäten
     *
     * @param ManagerRegistry $registry Doctrine-Kernkomponente zur Verwaltung von Datenbankzugriffen
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    /**
     * Findet alle distinct (eindeutige Werte-ohne Duplikate) Städtnamen für die Filter Dropdown
     * Wird verwendet um die Stadtauswahl für die Dropdown Filter zu füllen.
     * Gibt eine alphabetisch sortierte Liste aller vorhandenen Städte zurück.
     *
     * @return string[] Array von distinct Städtenamen, alphabetisch sortiert (A-Z)
     * @example ['Berlin', 'Hamburg', 'München']
     */
    public function findDistinctTowns(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.location_town')
            ->orderBy('l.location_town','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Findet alle distinct (eindeutige Werte-ohne Duplikate) Regionsnamen für die Filter Dropdown
     * Wird verwendet um die Regionsauswahl für die Dropdown Filter zu füllen.
     * Gibt eine alphabetisch sortierte Liste aller vorhandenen Regionen zurück.
     *
     * @return string[] Array von distinct Regionsnamen, alphabetisch sortiert (A-Z)
     * @example ['Bayern', 'Nordrhein-Westfalen', 'Sachsen']
     */
    public function findDistinctRegions(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.region')
            ->orderBy('l.region','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Findet alle distinct (eindeutige Werte-ohne Duplikate) Ländernamen für die Filter Dropdown
     * Wird verwendet um die Länderauswahl für die Dropdown Filter zu füllen.
     * Gibt eine alphabetisch sortierte Liste aller vorhandenen Länder zurück.
     *
     * @return string[] Array von distinct Ländernamen, alphabetisch sortiert (A-Z)
     * @example ['Australien', 'Deutschland', 'Spanien']
     */
    public function findDistinctCountries(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.country')
            ->orderBy('l.country','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

}

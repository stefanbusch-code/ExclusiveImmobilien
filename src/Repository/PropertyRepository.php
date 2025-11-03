<?php

namespace App\Repository;

use App\Entity\Property;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * PropertyRepository - Datenbankzugriff für Immobilien-Entitäten
 * Enthält spezielle Such- und Filterfunktionen für das Immobilienportfolio
 * sowie Standard-Datenbankabfragen für Property-Entities.
 *
 * @extends ServiceEntityRepository<Property>
 *
 *
 */
class PropertyRepository extends ServiceEntityRepository
{
    /**
     * Konstruktor - Initialisiert das repository
     * @param ManagerRegistry $registry Doctrine-Kernkomponente zur Verwaltung von Datenbankzugriffen
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Property::class);
    }

    /**
     * Findet zufällige immobilien für die Startseite oder Empfehlungen
     *
     * Verwendet native SQL anstelle von QueryBuilder, wegen besserer Performance bei zufälliger Auswahl ->
     * Über Doctrine -> lädt alle datensätze in PHP Speicher -> mischt diese -> wählt aus
     * (z.B 100.000 Datensätze -> lädt er 100.000 Datensätze)
     * über SQL -> Datenbank mischt lokal -> sendet nur gewünschte Anzahl an PHP -> viel weniger Datentransfer
     *
     * @param int $limit Maximale Anzahl der zurückgebenden Immobilien
     * @return Property[] array von zufälligen Immobilien-Entitäten
     */
    public function findRandomProperties(int $limit=3):array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT id FROM property ORDER BY RAND() LIMIT :limit';


        $stmt = $conn->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();
        $ids = $result->fetchFirstColumn();

        if(empty($ids)) {
            return [];
        }
        return $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

    }

    /**
     * Findet alle Immobilien, sortiert nach Kategorie
     *
     * optionale Filterung nach spezifischer kategorie
     *
     * @return Property[] Array von Immobilien-entitäten, sortiert nach Kategorie
     */
    public function findAllOrderedByCategory(string $category = null): array
    {
        $queryBuilder =  $this->createQueryBuilder('filter')
            ->orderBy('filter.category');

        if ($category)
        {
            $queryBuilder->andWhere('filter.category = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtert Immobilien basierend auf komplexen Filterkriterien
     *
     * Unterstützt kombinierte Filterung nach Preis, Standort, Kategorie
     * und Volltextsuche über mehrere Entitäten hinweg.
     *
     * @param array $criteria Assoziatives Array (Array mit benannten Schlüsseln) mit Filterkriterien
     *      - 'preis': Array mit 'min' und 'max' Preisen
     *      - 'location_town': Stadt für Filterung
     *      - 'region': Region für Filterung
     *      - 'country': Land für Filterung
     *      - 'category': Kategorie für Filterung
     *      - 'search': Suchbegriff für Volltextsuche
     *
     * @param string|null $preis zusätzlicher Preisparameter
     *
     * @return Property[] Array von gefilterten Immobilien-Entitäten
     */
    public function findByFilters(array $criteria = [], ?string $preis = null):array
    {
        $qb = $this->createQueryBuilder('p')
        ->leftJoin('p.location','l')
        ->addSelect('l')
        ->leftJoin('p.category','c')
        ->addSelect('c');

        $locationFields = ['location_town', 'region', 'country'];

        // Preisbereich-Filterung
        foreach ($criteria as $field => $value) {
            if ($field == 'preis' && is_array($value)) {
                $minPreis = $value['min'] ?? null;
                $maxPreis = $value['max'] ?? null;

                if ($minPreis !== null) {
                    $qb->andWhere('p.preis >= :minPreis')
                        ->setParameter('minPreis', (int)$minPreis);
                }
                if ($maxPreis !== null) {
                    $qb->andWhere('p.preis <= :maxPreis')
                        ->setParameter('maxPreis', (int)$maxPreis);
                }
            }
        }

        // Stadt-Filter
        if (!empty($criteria['location_town'])) {
            $qb->andWhere('l.location_town IN (:town)')
                ->setParameter('town', $criteria['location_town']);
        }

        // Land-Filter
        if (!empty($criteria['country'])) {
            $qb->andWhere('l.country IN (:country)')
                ->setParameter('country', $criteria['country']);
        }

        // Region-Filter
        if (!empty($criteria['region'])) {
            $qb->andWhere('l.region = :region')
                ->setParameter('region', $criteria['region']);
        }

        // Kategorie-Filter
        if (!empty($criteria['category'])) {
            $qb->andWhere('c.id IN (:category)')
                ->setParameter('category', $criteria['category']);
        }

        // Volltextsuche über mehrere Felder
        if(!empty($criteria['search'])){
            $searchTerm = $criteria['search'];
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('p.property_title', ':search'),
                    $qb->expr()->like('p.property_discription', ':search'),
                    $qb->expr()->like('l.location_town', ':search'),
                    $qb->expr()->like('l.region', ':search'),
                    $qb->expr()->like('l.country', ':search'),
                    $qb->expr()->like('c.discription', ':search')
                )
            )
                ->setParameter('search', '%'.$searchTerm.'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * findet alle distinct (eindeutige Werte-ohne Duplikate) Preiswerte für Filter-Dropdown
     *
     * @return array Array von distinct Preisen, aufsteigend sortiert
     */
    public function findDistinctPreise():array
    {
        return $this->createQueryBuilder('p')
            ->select('Distinct p.preis')
            ->where('p.preis IS NOT NULL')
            ->orderBy('p.preis','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * findet alle distinct (eindeutige Werte-ohne Duplikate) Länder für Filter-Dropdown
     *
     * @return array
     */
    public function findDistinctCountries(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT l.country')
            ->join('p.location', 'l')
            ->orderBy('l.country', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Findet alle distinct (eindeutige Werte-ohne Duplikate) Städte für Filter-Dropdown
     *
     * @return array Array von distinct Städtenamen, alphabetisch sortiert
     */
    public function findDistinctCities(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT l.locationTown')
            ->join('p.location', 'l')
            ->orderBy('l.locationTown', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}

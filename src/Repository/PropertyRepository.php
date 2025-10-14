<?php

namespace App\Repository;

use App\Entity\Property;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Property>
 */
class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Property::class);
    }



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
     * @return Property[] Returns an array of Property objects
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

    public function findByFilters(array $criteria = [], ?string $preis = null):array
    {
        $qb = $this->createQueryBuilder('p')
        ->leftJoin('p.location','l')
        ->addSelect('l')
        ->leftJoin('p.category','c')
        ->addSelect('c');

        $locationFields = ['location_town', 'region', 'country'];

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

        // Such-Filter
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

    public function findDistinctPreise():array
    {
        return $this->createQueryBuilder('p')
            ->select('Distinct p.preis')
            ->where('p.preis IS NOT NULL')
            ->orderBy('p.preis','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findDistinctCountries(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT l.country')
            ->join('p.location', 'l')
            ->orderBy('l.country', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

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

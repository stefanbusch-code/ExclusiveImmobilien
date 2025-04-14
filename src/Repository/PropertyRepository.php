<?php

namespace App\Repository;

use App\Entity\Property;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
            if (in_array($field, $locationFields)) {
                $qb->andWhere("l.$field = :$field")->setParameter($field, $value);
            } else {

                if ($field == 'preis' && is_array($value)) {
                    $minPreis = $value['min'];
                    $maxPreis = $value['max'];

                    if ($minPreis) {
                        $qb->andWhere('p.preis >= :minPreis')->setParameter('minPreis', (int)$minPreis);
                    }
                    if ($maxPreis) {
                        $qb->andWhere('p.preis <= :maxPreis')->setParameter('maxPreis', (int)$maxPreis);
                    }
                } else {
                    $qb->andWhere("p.$field = :$field")->setParameter($field, $value);
                }
            }
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
}

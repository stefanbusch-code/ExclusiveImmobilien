<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function findDistinctTowns(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.location_town')
            ->orderBy('l.location_town','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findDistinctRegions(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.region')
            ->orderBy('l.region','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
    public function findDistinctCountries(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.country')
            ->orderBy('l.country','ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

}

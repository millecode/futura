<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Programme;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Programme>
 */
class ProgrammeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Programme::class);
    }

    public function findAllWhitePagination(): Query
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :active')
            ->setParameter('active', true)
            ->getQuery()
        ;
    }

    public function findAllWhitePaginationAdmin(): Query
    {
        return $this->createQueryBuilder('p')
            ->getQuery()
        ;
    }

    //    /**
    //     * @return Programme[] Returns an array of Programme objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Programme
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

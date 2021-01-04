<?php

namespace App\Repository;

use App\Entity\MarketItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MarketItems|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarketItems|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarketItems[]    findAll()
 * @method MarketItems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarketItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketItems::class);
    }

    // /**
    //  * @return MarketItems[] Returns an array of MarketItems objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MarketItems
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

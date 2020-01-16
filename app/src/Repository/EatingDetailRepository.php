<?php

namespace App\Repository;


use App\Entity\EatingDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EatingDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method EatingDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method EatingDetail[]    findAll()
 * @method EatingDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EatingDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EatingDetail::class);
    }

    // /**
    //  * @return EatingDetail[] Returns an array of EatingDetail objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EatingDetail
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

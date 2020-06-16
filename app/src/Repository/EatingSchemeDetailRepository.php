<?php

namespace App\Repository;

use App\Entity\EatingSchemeDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EatingSchemeDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method EatingSchemeDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method EatingSchemeDetail[]    findAll()
 * @method EatingSchemeDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EatingSchemeDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EatingSchemeDetail::class);
    }

    // /**
    //  * @return EatingSchemeDetail[] Returns an array of EatingSchemeDetail objects
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
    public function findOneBySomeField($value): ?EatingSchemeDetail
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

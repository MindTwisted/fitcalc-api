<?php

namespace App\Repository;

use App\Entity\PasswordRecovery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PasswordRecovery|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordRecovery|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordRecovery[]    findAll()
 * @method PasswordRecovery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordRecoveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordRecovery::class);
    }

    // /**
    //  * @return PasswordRecovery[] Returns an array of PasswordRecovery objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PasswordRecovery
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

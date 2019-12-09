<?php

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Email|null find($id, $lockMode = null, $lockVersion = null)
 * @method Email|null findOneBy(array $criteria, array $orderBy = null)
 * @method Email[]    findAll()
 * @method Email[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }

    /**
     * @param string $hash
     *
     * @return Email|null
     *
     * @throws NonUniqueResultException
     */
    public function findNotVerifiedOneByHash(string $hash): ?Email
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.hash = :hash')
            ->setParameter('hash', $hash)
            ->andWhere('e.verified = 0')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     *
     * @return Email|null
     *
     * @throws NonUniqueResultException
     */
    public function findVerifiedOneByEmailJoinedToUser(string $email): ?Email
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.email = :email')
            ->setParameter('email', $email)
            ->andWhere('e.verified = 1')
            ->join('e.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Email[] Returns an array of Email objects
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
    public function findOneBySomeField($value): ?Email
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

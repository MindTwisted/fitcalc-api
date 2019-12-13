<?php

namespace App\Repository;

use App\Entity\EmailConfirmation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method EmailConfirmation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailConfirmation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailConfirmation[]    findAll()
 * @method EmailConfirmation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailConfirmationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailConfirmation::class);
    }

    /**
     * @param string $hash
     *
     * @return EmailConfirmation|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByHashJoinedToUser(string $hash): ?EmailConfirmation
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.hash = :hash')
            ->setParameter('hash', $hash)
            ->join('e.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getOneOrNullResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

/**
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefreshToken[]    findAll()
 * @method RefreshToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    /**
     * @param int $value
     *
     * @return RefreshToken[]
     *
     * @throws Exception
     */
    public function findNotExpiredByUserId(int $value): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :val')
            ->setParameter('val', $value)
            ->andWhere('r.expiresAt > :now')
            ->setParameter('now', new DateTime())
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @param int $userId
     *
     * @return RefreshToken|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneNotExpiredByIdAndUserId(int $id, int $userId): ?RefreshToken
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :id')
            ->setParameter('id', $id)
            ->andWhere('r.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('r.expiresAt > :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $userId
     *
     * @throws Exception
     */
    public function deleteNotExpiredByUserId(int $userId): void
    {
        $this->createQueryBuilder('r')
            ->andWhere('r.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('r.expiresAt > :now')
            ->setParameter('now', new DateTime())
            ->delete()
            ->getQuery()
            ->getResult();
    }
}

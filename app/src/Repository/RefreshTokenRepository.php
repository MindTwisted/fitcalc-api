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
    public function findNotExpiredAndNotDeletedByUserId(int $value): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :val')
            ->setParameter('val', $value)
            ->andWhere('r.expiresAt > :now')
            ->setParameter('now', new DateTime())
            ->andWhere('r.deletedAt is NULL')
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
    public function findOneNotExpiredAndNotDeletedByIdAndUserId(int $id, int $userId): ?RefreshToken
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :id')
            ->setParameter('id', $id)
            ->andWhere('r.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('r.expiresAt > :now')
            ->setParameter('now', new DateTime())
            ->andWhere('r.deletedAt is NULL')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $userId
     *
     * @throws Exception
     */
    public function softDeleteNotExpiredAndNotDeletedByUserId(int $userId): void
    {
        $this->createQueryBuilder('r')
            ->update()
            ->set('r.deletedAt', ':now')
            ->andWhere('r.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('r.expiresAt > :now')
            ->setParameter('now', new DateTime())
            ->andWhere('r.deletedAt is NULL')
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $token
     *
     * @return RefreshToken|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneNotExpiredAndNotDeletedByTokenJoinedToUser(string $token): ?RefreshToken
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.token = :token')
            ->setParameter('token', $token)
            ->andWhere('r.expiresAt > :now')
            ->setParameter('now', new DateTime())
            ->andWhere('r.deletedAt is NULL')
            ->join('r.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getOneOrNullResult();
    }
}

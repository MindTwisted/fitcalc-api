<?php

namespace App\Repository;

use App\Entity\PasswordRecovery;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

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

    /**
     * @param string $token
     *
     * @return PasswordRecovery|null
     * @throws NonUniqueResultException
     */
    public function findOneByTokenJoinedToUser(string $token): ?PasswordRecovery
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.token = :token')
            ->setParameter('token', $token)
            ->join('p.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $hours
     *
     * @return PasswordRecovery[]
     *
     * @throws Exception
     */
    public function findOlderThan(int $hours): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.createdAt < :time')
            ->setParameter('time', (new DateTime())->modify("-$hours hours"))
            ->getQuery()
            ->getResult();
    }
}

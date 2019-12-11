<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @param UserInterface $user
     * @param string $newEncodedPassword
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param string $value
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByUsernameJoinedToVerifiedEmail(string $value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :val')
            ->setParameter('val', $value)
            ->join('u.emails', 'e')
            ->andWhere('e.verified = 1')
            ->addSelect('e')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $value
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByVerifiedEmail(string $value): ?User
    {
        return $this->createQueryBuilder('u')
            ->join('u.emails', 'e')
            ->andWhere('e.verified = 1')
            ->andWhere('e.email = :val')
            ->setParameter('val', $value)
            ->addSelect('e')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $value
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByIdJoinedToVerifiedEmail(int $value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :val')
            ->setParameter('val', $value)
            ->join('u.emails', 'e')
            ->andWhere('e.verified = 1')
            ->addSelect('e')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function findAdminUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"' . User::ROLE_ADMIN . '"%')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function findAppUsersJoinedToVerifiedEmail(int $offset = 0, int $limit = 50): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"' . User::ROLE_APP_USER . '"%')
            ->join('u.emails', 'e')
            ->andWhere('e.verified = 1')
            ->addSelect('e')
            ->orderBy('u.id', 'ASC')
            ->setFirstResult( $offset )
            ->setMaxResults( $limit )
            ->getQuery()
            ->getResult();
    }
}

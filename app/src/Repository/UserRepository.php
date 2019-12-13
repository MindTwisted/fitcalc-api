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
    /**
     * @var EmailConfirmationRepository
     */
    private $emailConfirmationRepository;

    /**
     * UserRepository constructor.
     *
     * @param ManagerRegistry $registry
     * @param EmailConfirmationRepository $emailConfirmationRepository
     */
    public function __construct(ManagerRegistry $registry, EmailConfirmationRepository $emailConfirmationRepository)
    {
        parent::__construct($registry, User::class);

        $this->emailConfirmationRepository = $emailConfirmationRepository;
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
     * @param string $email
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByConfirmedEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->andWhere("u.emailConfirmedAt is not NULL")
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneWithConfirmedEmailById(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->andWhere("u.emailConfirmedAt is not NULL")
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
     * @param string $name
     * @param string $email
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function findAppUsersWithConfirmedEmail(
        string $name = '',
        string $email = '',
        int $offset = 0,
        int $limit = 50
    ): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"' . User::ROLE_APP_USER . '"%')
            ->andWhere('u.name LIKE :name')
            ->setParameter('name', "%$name%")
            ->andWhere('u.email LIKE :email')
            ->setParameter('email', "%$email%")
            ->andWhere("u.emailConfirmedAt is not NULL")
            ->orderBy('u.id', 'ASC')
            ->setFirstResult( $offset )
            ->setMaxResults( $limit )
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    public function emailUniquenessCheck(array $payload): array
    {
        $users = $this->findBy($payload);
        $emails = $this->emailConfirmationRepository->findBy($payload);

        return array_merge($users, $emails);
    }
}

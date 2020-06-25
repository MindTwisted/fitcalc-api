<?php

namespace App\Repository;


use App\Entity\EatingScheme;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method EatingScheme|null find($id, $lockMode = null, $lockVersion = null)
 * @method EatingScheme|null findOneBy(array $criteria, array $orderBy = null)
 * @method EatingScheme[]    findAll()
 * @method EatingScheme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EatingSchemeRepository extends ServiceEntityRepository
{
    /**
     * EatingSchemeRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EatingScheme::class);
    }

    /**
     * @param array $data
     *
     * @return EatingScheme|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByNameAndUser(array $data): ?EatingScheme
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name = :name')
            ->setParameter('name', $data['name'])
            ->andWhere('e.user = :user')
            ->setParameter('user', $data['user'])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param User $user
     * @param int $offset
     * @param int $limit
     *
     * @return array
     * @throws Exception
     */
    public function findByUser(
        User $user,
        int $offset = 0,
        int $limit = 50
    ): array
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.eatingSchemeDetails', 'ed')
            ->addSelect('ed')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->addOrderBy('e.id', 'ASC')
            ->addOrderBy('ed.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query);

        return iterator_to_array($paginator->getIterator());
    }

    /**
     * @param User $user
     */
    public function removeDefaultEatingSchemeByUser(User $user): void
    {
        $this->createQueryBuilder('e')
            ->update()
            ->set('e.isDefault', ':isDefault')
            ->setParameter('isDefault', false)
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $id
     */
    public function setDefaultEatingSchemeById(int $id): void
    {
        $this->createQueryBuilder('e')
            ->update()
            ->set('e.isDefault', ':isDefault')
            ->setParameter('isDefault', true)
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}

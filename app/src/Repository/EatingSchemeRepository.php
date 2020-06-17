<?php

namespace App\Repository;


use App\Entity\EatingScheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EatingScheme|null find($id, $lockMode = null, $lockVersion = null)
 * @method EatingScheme|null findOneBy(array $criteria, array $orderBy = null)
 * @method EatingScheme[]    findAll()
 * @method EatingScheme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EatingSchemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EatingScheme::class);
    }

    // /**
    //  * @return EatingScheme[] Returns an array of EatingScheme objects
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

    /**
     * @param array $data
     *
     * @return EatingScheme|null
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
}

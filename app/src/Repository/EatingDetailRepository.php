<?php

namespace App\Repository;


use App\Entity\EatingDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method EatingDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method EatingDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method EatingDetail[]    findAll()
 * @method EatingDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EatingDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EatingDetail::class);
    }

    /**
     * @param int $id
     * @param int $eatingId
     *
     * @return EatingDetail|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneWithEatingByIdAndEatingId(int $id, int $eatingId): ?EatingDetail
    {
        return $this->createQueryBuilder('ed')
            ->leftJoin('ed.eating', 'e')
            ->addSelect('e')
            ->andWhere('ed.id = :id')
            ->setParameter('id', $id)
            ->andWhere('e.id = :eatingId')
            ->setParameter('eatingId', $eatingId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

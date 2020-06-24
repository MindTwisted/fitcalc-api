<?php

namespace App\Repository;

use App\Entity\EatingSchemeDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EatingSchemeDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method EatingSchemeDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method EatingSchemeDetail[]    findAll()
 * @method EatingSchemeDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EatingSchemeDetailRepository extends ServiceEntityRepository
{
    /**
     * EatingSchemeDetailRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EatingSchemeDetail::class);
    }

    /**
     * @param array $data
     *
     * @return EatingSchemeDetail|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByNameAndEatingScheme(array $data): ?EatingSchemeDetail
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name = :name')
            ->setParameter('name', $data['name'])
            ->andWhere('e.eatingScheme = :eatingScheme')
            ->setParameter('eatingScheme', $data['eatingScheme'])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @param int $eatingSchemeId
     *
     * @return EatingSchemeDetail|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneWithEatingSchemeByIdAndEatingSchemeId(int $id, int $eatingSchemeId): ?EatingSchemeDetail
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.eatingScheme', 'es')
            ->addSelect('es')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->andWhere('es.id = :eatingSchemeId')
            ->setParameter('eatingSchemeId', $eatingSchemeId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

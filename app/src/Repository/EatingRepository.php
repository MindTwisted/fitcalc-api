<?php

namespace App\Repository;


use App\Entity\Eating;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Exception;
use Gedmo\Translatable\TranslatableListener;

/**
 * @method Eating|null find($id, $lockMode = null, $lockVersion = null)
 * @method Eating|null findOneBy(array $criteria, array $orderBy = null)
 * @method Eating[]    findAll()
 * @method Eating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eating::class);
    }

    /**
     * @param int $userId
     * @param DateTime $occurredAt
     * @param string $locale
     *
     * @return array
     *
     * @throws Exception
     */
    public function findByUserIdAndOccurredAt(int $userId, DateTime $occurredAt, string $locale = 'en'): array
    {
        $from = new DateTime($occurredAt->format('Y-m-d') . ' 00:00:00');
        $to = new DateTime($occurredAt->format('Y-m-d') . ' 23:59:59');

        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->leftJoin('e.eatingDetails', 'ed')
            ->leftJoin('ed.product', 'p')
            ->addSelect(['u', 'ed', 'p'])
            ->andWhere('e.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->useQueryCache(false)
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            )
            ->setHint(
                TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                $locale
            )
            ->setHint(
                TranslatableListener::HINT_FALLBACK,
                1 // fallback to default values in case if record is not translated
            )
            ->getResult();
    }

    /**
     * @param int $id
     * @param string $locale
     *
     * @return Eating|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneWithDetailsById(int $id, string $locale = 'en'): ?Eating
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->leftJoin('e.eatingDetails', 'ed')
            ->leftJoin('ed.product', 'p')
            ->addSelect(['u', 'ed', 'p'])
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->useQueryCache(false)
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            )
            ->setHint(
                TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                $locale
            )
            ->setHint(
                TranslatableListener::HINT_FALLBACK,
                1 // fallback to default values in case if record is not translated
            )
            ->getOneOrNullResult();
    }
}

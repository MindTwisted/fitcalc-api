<?php

namespace App\Repository;


use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param string|null $name
     * @param int|null $userId
     * @param string $locale
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function findWithTranslation(
        string $name = '',
        ?int $userId = null,
        string $locale = 'en',
        int $offset = 0,
        int $limit = 50
    ): array
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.translations', 't')
            ->addSelect('t')
            ->andWhere('p.name LIKE :name')
            ->setParameter('name', "%$name%");

        if ($userId) {
            $query = $query->andWhere($query->expr()->orX(
                $query->expr()->isNull('p.user'),
                $query->expr()->eq('p.user', $userId)
            ));
        }

        $query = $query->orderBy('p.id', 'ASC')
            ->setFirstResult( $offset )
            ->setMaxResults($limit)
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

        return $query;
    }
}

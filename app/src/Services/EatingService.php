<?php

namespace App\Services;


use App\Entity\Eating;
use App\Entity\EatingDetail;
use App\Entity\Product;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\EatingRepository;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class EatingService
 *
 * @package App\Services
 */
class EatingService
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private ValidationService $validationService;

    /**
     * EatingService constructor.
     *
     * @param Security $security
     * @param EntityManagerInterface $entityManager
     * @param ValidationService $validationService
     */
    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        ValidationService $validationService
    )
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->validationService = $validationService;
    }

    /**
     * @param Request $request
     *
     * @return Eating[]
     *
     * @throws Exception
     */
    public function getAllEatingOfCurrentUser(Request $request): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        try {
            $occurredAt = new DateTime($request->get('occurred_at', 'now'));
        } catch (Exception $e) {
            $occurredAt = new DateTime();
        }

        /** @var EatingRepository $eatingRepository */
        $eatingRepository = $this->entityManager->getRepository(Eating::class);

        return $eatingRepository->findByUserIdAndOccurredAt(
            $user->getId(),
            $occurredAt,
            $request->getLocale()
        );
    }

    /**
     * @param Request $request
     * @param Eating|null $eating
     *
     * @return Eating
     *
     * @throws ValidationException
     */
    public function createOrUpdateEating(Request $request, ?Eating $eating = null): Eating
    {
        /** @var User $user */
        $user = $this->security->getUser();

        try {
            $occurredAt = new DateTime($request->get('occurred_at', 'now'));
        } catch (Exception $e) {
            $occurredAt = new DateTime();
        }

        $eating = $eating ?? new Eating();
        $eating->setName($request->get('name', ''));
        $eating->setOccurredAt($occurredAt);
        $eating->setUser($user);

        $this->validationService->validate($eating);
        $this->entityManager->persist($eating);
        $this->entityManager->flush();

        return $eating;
    }

    /**
     * @param Request $request
     * @param Eating $eating
     * @param EatingDetail|null $eatingDetail
     *
     * @return EatingDetail
     *
     * @throws ValidationException
     */
    public function createOrUpdateEatingDetail(
        Request $request,
        Eating $eating,
        ?EatingDetail $eatingDetail = null
    ): EatingDetail
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->findOneBy([
            'id' => $request->request->getInt('product_id', 0)
        ]);

        $eatingDetail = $eatingDetail ?? new EatingDetail();
        $eatingDetail->setProduct($product);
        $eatingDetail->setWeight($request->request->getInt('weight', 0));
        $eatingDetail->setEating($eating);

        $this->validationService->validate($eatingDetail);
        $this->entityManager->persist($eatingDetail);
        $this->entityManager->flush();

        return $eatingDetail;
    }

    /**
     * @param int $id
     * @param string $locale
     *
     * @return Eating|null
     *
     * @throws NonUniqueResultException
     */
    public function getOneWithDetailsById(int $id, string $locale = 'en'): ?Eating
    {
        /** @var EatingRepository $eatingRepository */
        $eatingRepository = $this->entityManager->getRepository(Eating::class);

        return $eatingRepository->findOneWithDetailsById($id, $locale);
    }

    /**
     * @param EatingDetail $eatingDetail
     */
    public function deleteEatingDetail(EatingDetail $eatingDetail): void
    {
        $this->entityManager->remove($eatingDetail);
        $this->entityManager->flush();
    }

    /**
     * @param Eating $eating
     */
    public function deleteEating(Eating $eating): void
    {
        $this->entityManager->remove($eating);
        $this->entityManager->flush();
    }
}
<?php

namespace App\Services;


use App\Entity\EatingScheme;
use App\Entity\EatingSchemeDetail;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\EatingSchemeRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class EatingSchemeService
 *
 * @package App\Services
 */
class EatingSchemeService
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private ValidationService $validationService;

    /**
     * EatingSchemeService constructor.
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
     * @return array
     *
     * @throws Exception
     */
    public function getAllEatingSchemeOfCurrentUser(Request $request): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var EatingSchemeRepository $eatingSchemeRepository */
        $eatingSchemeRepository = $this->entityManager->getRepository(EatingScheme::class);

        return $eatingSchemeRepository->findByUser($user, $request->query->getInt('offset', 0));
    }

    /**
     * @param Request $request
     * @param EatingScheme|null $eatingScheme
     *
     * @return EatingScheme
     *
     * @throws ValidationException
     */
    public function createOrUpdateEatingScheme(
        Request $request,
        ?EatingScheme $eatingScheme = null
    ): EatingScheme
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $eatingScheme = $eatingScheme ?? new EatingScheme();
        $eatingScheme->setName($request->get('name', ''));
        $eatingScheme->setUser($user);

        $this->validationService->validate($eatingScheme);
        $this->entityManager->persist($eatingScheme);
        $this->entityManager->flush();

        return $eatingScheme;
    }

    /**
     * @param EatingScheme $eatingScheme
     */
    public function deleteEatingScheme(EatingScheme $eatingScheme): void
    {
        $this->entityManager->remove($eatingScheme);
        $this->entityManager->flush();
    }

    /**
     * @param Request $request
     * @param EatingScheme $eatingScheme
     * @param EatingSchemeDetail|null $eatingSchemeDetail
     *
     * @return EatingSchemeDetail
     *
     * @throws ValidationException
     */
    public function createOrUpdateEatingSchemeDetail(
        Request $request,
        EatingScheme $eatingScheme,
        ?EatingSchemeDetail $eatingSchemeDetail = null
    ): EatingSchemeDetail
    {
        $eatingSchemeDetail = $eatingSchemeDetail ?? new EatingSchemeDetail();
        $eatingSchemeDetail->setName($request->get('name', ''));
        $eatingSchemeDetail->setEatingScheme($eatingScheme);

        $this->validationService->validate($eatingSchemeDetail);
        $this->entityManager->persist($eatingSchemeDetail);
        $this->entityManager->flush();

        return $eatingSchemeDetail;
    }

    /**
     * @param EatingSchemeDetail $eatingSchemeDetail
     */
    public function deleteEatingSchemeDetail(EatingSchemeDetail $eatingSchemeDetail): void
    {
        $this->entityManager->remove($eatingSchemeDetail);
        $this->entityManager->flush();
    }

    /**
     * @param EatingScheme $eatingScheme
     *
     * @throws ConnectionException
     */
    public function setDefaultEatingScheme(EatingScheme $eatingScheme): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        /** @var EatingSchemeRepository $eatingSchemeRepository */
        $eatingSchemeRepository = $this->entityManager->getRepository(EatingScheme::class);

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $eatingSchemeRepository->removeDefaultEatingSchemeByUser($user);
            $eatingSchemeRepository->setDefaultEatingSchemeById($eatingScheme->getId());

            $eatingScheme->setIsDefault(true);

            $this->entityManager->getConnection()->commit();
        } catch (Exception $e) {
            $this->entityManager->getConnection()->rollBack();

            throw $e;
        }
    }
}
<?php

namespace App\Services;


use App\Entity\EatingScheme;
use App\Entity\User;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
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
}
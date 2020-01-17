<?php

namespace App\Services;


use App\Entity\Eating;
use App\Entity\User;
use App\Exception\ValidationException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidationService
     */
    private $validationService;

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
}
<?php

namespace App\Services;


use App\Entity\EmailConfirmation;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\EmailConfirmationRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserService
 */
class UserService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * @var ValidationService
     */
    private $validationService;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * UserService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param ValidationService $validationService
     * @param EmailService $emailService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $userPasswordEncoder,
        ValidationService $validationService,
        EmailService $emailService
    )
    {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->validationService = $validationService;
        $this->emailService = $emailService;
    }

    /**
     * @param string $email
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function getUserByEmail(string $email): ?User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->findOneByConfirmedEmail($email);
    }

    /**
     * @param int $id
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function getUserById(int $id): ?User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->findOneWithConfirmedEmailById($id);
    }

    /**
     * @param User $user
     * @param string $token
     * @param string $device
     * @param string $ipAddress
     *
     * @return RefreshToken
     *
     * @throws ValidationException
     */
    public function storeRefreshToken(
        User $user,
        string $token,
        string $device,
        string $ipAddress
    ): RefreshToken
    {
        $tokenTTE = $user->isAdmin() ? $_ENV['REFRESH_TOKEN_ADMIN_TTE'] : $_ENV['REFRESH_TOKEN_USER_TTE'];
        $expiresAt = new DateTime('now');
        $expiresAt->modify("+$tokenTTE seconds");

        $refreshToken = new RefreshToken();
        $refreshToken->setDevice($device);
        $refreshToken->setToken($token);
        $refreshToken->setIpAddress($ipAddress);
        $refreshToken->setExpiresAt($expiresAt);

        $user->addRefreshToken($refreshToken);

        $this->validationService->validate($refreshToken);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $refreshToken;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function encodeUserPassword(User $user): User
    {
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword()));

        return $user;
    }

    /**
     * @param Request $request
     *
     * @return User
     *
     * @throws ValidationException
     */
    public function registerUser(Request $request): User
    {
        $user = $this->createUserForRegistration($request);

        try {
            $this->emailService->sendEmailConfirmationMessage($request, $user);
        } catch (TransportExceptionInterface $e) {
            throw new HttpException(
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'Unexpected error has been occurred, please try again later.'
            );
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param string $hash
     *
     * @return User
     *
     * @throws NonUniqueResultException
     * @throws HttpException
     */
    public function confirmEmail(string $hash): User
    {
        /** @var EmailConfirmationRepository $emailConfirmationRepository */
        $emailConfirmationRepository = $this->entityManager->getRepository(EmailConfirmation::class);
        $emailConfirmation = $emailConfirmationRepository->findOneByHashJoinedToUser($hash);

        if (!$emailConfirmation) {
            throw new HttpException(
                JsonResponse::HTTP_FORBIDDEN,
                'Forbidden.'
            );
        }

        $user = $emailConfirmation->getUser();
        $user->setEmail($emailConfirmation->getEmail());
        $user->setEmailConfirmedAt(new DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->remove($emailConfirmation);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param Request $request
     *
     * @return User
     *
     * @throws ValidationException
     * @throws Exception
     */
    private function createUserForRegistration(Request $request): User
    {
        $user = new User();
        $user->setName($request->get('name', ''));
        $user->setEmail($request->get('email', ''));
        $user->setPlainPassword($request->get('password', ''));
        $user->setRoles([User::ROLE_APP_USER]);
        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setEmail($request->get('email', ''));
        $emailConfirmation->setPrePersistDefaults();
        $user->addEmailConfirmation($emailConfirmation);

        $this->validationService->validate($user);
        $this->encodeUserPassword($user);

        return $user;
    }
}
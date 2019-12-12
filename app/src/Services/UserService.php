<?php

namespace App\Services;


use App\Entity\Email;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserService
 */
class UserService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var ValidationService */
    private $validationService;

    /**
     * UserService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param ValidationService $validationService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $userPasswordEncoder,
        ValidationService $validationService
    )
    {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->validationService = $validationService;
    }

    /**
     * @param string $username
     * @param string $email
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function getUserByUsernameOrEmail(string $username, string $email): ?User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        if ($username) {
            return $userRepository->findOneByUsernameJoinedToVerifiedEmail($username);
        }

        if ($email) {
            return $userRepository->findOneByVerifiedEmail($email);
        }

        return null;
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

        return $userRepository->findOneByIdJoinedToVerifiedEmail($id);
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
     * @param Request $request
     *
     * @return User
     *
     * @throws Exception
     */
    public function createUserFromRequest(Request $request): User
    {
        $user = new User();
        $user->setFullname($request->get('fullname', ''));
        $user->setUsername($request->get('username', ''));
        $user->setPlainPassword($request->get('password', ''));
        $email = new Email();
        $email->setEmail($request->get('email', ''));
        $email->setPrePersistDefaults();
        $user->addEmail($email);

        return $user;
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
}
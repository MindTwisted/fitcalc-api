<?php

namespace App\Services;


use App\Entity\Email;
use App\Entity\RefreshToken;
use App\Entity\User;
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

    /**
     * UserService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $userPasswordEncoder
    )
    {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
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
     *
     * @throws Exception
     */
    public function storeRefreshToken(User $user, string $token, string $device): void
    {
        $tokenTTE = $user->isAdmin() ? $_ENV['REFRESH_TOKEN_ADMIN_TTE'] : $_ENV['REFRESH_TOKEN_USER_TTE'];
        $expiresAt = new DateTime('now');
        $expiresAt->modify("+$tokenTTE seconds");

        $refreshToken = new RefreshToken();
        $refreshToken->setDevice($device);
        $refreshToken->setToken($token);
        $refreshToken->setExpiresAt($expiresAt);

        $user->addRefreshToken($refreshToken);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
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
        $user->setPassword($request->get('password', ''));
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
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPassword()));

        return $user;
    }
}
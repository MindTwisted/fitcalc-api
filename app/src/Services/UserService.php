<?php

namespace App\Services;


use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

/**
 * Class UserService
 */
class UserService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * UserService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $username
     * @param string $email
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     */
    public function getUser(string $username, string $email): ?User
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
}
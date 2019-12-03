<?php

namespace App\Services;


use App\Entity\User;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class AuthService
 */
class AuthService
{
    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var UserService */
    private $userService;

    /** @var JwtService */
    private $jwtService;

    /**
     * AuthService constructor.
     *
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param UserService $userService
     * @param JwtService $jwtService
     */
    public function __construct(
        UserPasswordEncoderInterface $userPasswordEncoder,
        UserService $userService,
        JwtService $jwtService
    )
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    /**
     * @param User $user
     * @param string $password
     *
     * @return bool
     */
    public function isPasswordValid(User $user, ?string $password): bool
    {
        if (!$password) {
            return false;
        }

        return $this->userPasswordEncoder->isPasswordValid($user, $password);
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function generateRefreshToken(): string
    {
        return md5(random_bytes(10)) . md5(random_bytes(10)) . md5(random_bytes(10));
    }

    /**
     * @param User $user
     *
     * @return string
     *
     * @throws Exception
     */
    public function generateAccessToken(User $user): string
    {
        return $this->jwtService->createTokenFromUser($user);
    }

    /**
     * @param string $accessToken
     *
     * @return User|null
     *
     * @throws NonUniqueResultException
     * @throws AuthenticationException
     */
    public function getUserByAccessToken(string $accessToken): ?User
    {
        $token = $this->jwtService->parseToken($accessToken);
        $this->jwtService->validateToken($token);
        $this->jwtService->verifyToken($token);

        try {
            $userId = $token->getClaim('user_id');
        } catch (Exception $exception) {
            throw new AuthenticationException("Can't find user credentials in token claims.");
        }

        return $this->userService->getUserById($userId);
    }
}
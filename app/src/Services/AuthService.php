<?php

namespace App\Services;


use App\Entity\User;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/**
 * Class AuthService
 */
class AuthService
{
    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /**
     * AuthService constructor.
     *
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
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
        $signer = new Sha256();
        $privateKeyPath = $_ENV['APP_ROOT'] . $_ENV['JWT_KEYS_PATH'] . '/' . $_ENV['JWT_PRIVATE_KEY_NAME'];
        $privateKey = new Key("file://$privateKeyPath", $_ENV['KEYS_PASSPHRASE']);
        $time = time();

        $token = (new Builder())
            ->identifiedBy(md5(random_bytes(10)), true)
            ->issuedAt($time)
            ->canOnlyBeUsedAfter($time)
            ->expiresAt($time + $_ENV['JWT_TTE'])
            ->withClaim('user_id', $user->getId())
            ->getToken($signer, $privateKey);

        return $token;
    }
}
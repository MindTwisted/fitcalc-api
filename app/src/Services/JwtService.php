<?php

namespace App\Services;


use App\Entity\AccessToken;
use App\Entity\User;
use DateTime;
use Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

class JwtService
{
    /**
     * @var string
     */
    private $privateKeyPath;

    /**
     * @var string
     */
    private $publicKeyPath;

    /**
     * JwtService constructor.
     */
    public function __construct()
    {
        $this->privateKeyPath = $_ENV['APP_ROOT'] . $_ENV['JWT_KEYS_PATH'] . '/' . $_ENV['JWT_PRIVATE_KEY_NAME'];
        $this->publicKeyPath = $_ENV['APP_ROOT'] . $_ENV['JWT_KEYS_PATH'] . '/' . $_ENV['JWT_PUBLIC_KEY_NAME'];
    }

    /**
     * @param User $user
     *
     * @return AccessToken
     *
     * @throws Exception
     */
    public function createTokenFromUser(User $user): AccessToken
    {
        $signer = new Sha256();
        $privateKey = new Key("file://{$this->privateKeyPath}", $_ENV['KEYS_PASSPHRASE']);
        $time = time();
        $expiresAt = $time + $_ENV['JWT_TTE'];
        $accessToken = new AccessToken();
        $accessToken->setToken((string) (new Builder())
            ->identifiedBy(md5(random_bytes(10)), true)
            ->issuedAt($time)
            ->canOnlyBeUsedAfter($time)
            ->expiresAt($expiresAt)
            ->withClaim('user_id', $user->getId())
            ->getToken($signer, $privateKey));
        $accessToken->setExpiresAt((new DateTime())->setTimestamp($expiresAt));

        return $accessToken;
    }

    /**
     * @param string $token
     *
     * @return Token|string
     */
    public function parseToken(string $token): Token
    {
        try {
            $token = (new Parser())->parse($token);
        } catch (Throwable $exception) {
            throw new AuthenticationException('Token parsing failed.');
        }

        return $token;
    }

    /**
     * @param Token $token
     */
    public function validateToken(Token $token): void
    {
        $time = time();
        $data = new ValidationData();
        $data->setCurrentTime($time);

        if (!$token->validate($data)) {
            throw new AuthenticationException('Token validation failed.');
        }
    }

    /**
     * @param Token $token
     */
    public function verifyToken(Token $token): void
    {
        $signer = new Sha256();
        $publicKey = new Key("file://{$this->publicKeyPath}");

        if (!$token->verify($signer, $publicKey)) {
            throw new AuthenticationException('Token verification failed.');
        }
    }
}
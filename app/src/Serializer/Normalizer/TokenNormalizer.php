<?php

namespace App\Serializer\Normalizer;


use DateTime;
use Exception;
use Lcobucci\JWT\Token;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TokenNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private DateTimeNormalizer $dateTimeNormalizer;

    /**
     * AccessTokenNormalizer constructor.
     *
     * @param DateTimeNormalizer $dateTimeNormalizer
     */
    public function __construct(DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    /**
     * @param Token $object
     * @param null $format
     * @param array $context
     *
     * @return array
     *
     * @throws Exception
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'token' => (string) $object,
            'expires_at' => $this->dateTimeNormalizer
                ->normalize((new DateTime())->setTimestamp($object->getClaim('exp')))
        ];
    }

    /**
     * @param mixed $data
     * @param null $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Token;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

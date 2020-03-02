<?php

namespace App\Serializer\Normalizer;


use App\Entity\AccessToken;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AccessTokenNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var DateTimeNormalizer
     */
    private $dateTimeNormalizer;

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
     * @param AccessToken $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'token' => $object->getToken(),
            'expires_at' => $this->dateTimeNormalizer->normalize($object->getExpiresAt())
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
        return $data instanceof AccessToken;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

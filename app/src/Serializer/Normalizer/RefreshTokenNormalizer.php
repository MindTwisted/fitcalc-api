<?php

namespace App\Serializer\Normalizer;

use App\Entity\RefreshToken;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RefreshTokenNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * RefreshTokenNormalizer constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param RefreshToken $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array()): array
    {
        return [
            'id' => $object->getId(),
            'device' => $object->getDevice(),
            'expires_at' => $object->getExpiresAt()
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
        return $data instanceof RefreshToken;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

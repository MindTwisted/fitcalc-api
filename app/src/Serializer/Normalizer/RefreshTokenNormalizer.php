<?php

namespace App\Serializer\Normalizer;


use App\Entity\RefreshToken;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RefreshTokenNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var DateTimeNormalizer
     */
    private $dateTimeNormalizer;

    /**
     * RefreshTokenNormalizer constructor.
     *
     * @param DateTimeNormalizer $dateTimeNormalizer
     */
    public function __construct(DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    /**
     * @param RefreshToken $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [
            'id' => $object->getId(),
            'device' => $object->getDevice(),
            'expires_at' => $this->dateTimeNormalizer->normalize($object->getExpiresAt())
        ];

        if (isset($context['group']) && $context['group'] === 'login') {
            $data['token'] = $object->getToken();

            unset($data['device']);
        }

        return $data;
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

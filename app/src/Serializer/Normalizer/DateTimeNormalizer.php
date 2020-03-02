<?php

namespace App\Serializer\Normalizer;


use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * DateTimeNormalizer constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param DateTimeInterface $object
     * @param null $format
     * @param array $context
     *
     * @return string
     */
    public function normalize($object, $format = null, array $context = array()): string
    {
        return $object->format('M d Y H:i:s T');
    }

    /**
     * @param mixed $data
     * @param null $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof DateTimeInterface;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

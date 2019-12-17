<?php

namespace App\Serializer\Normalizer;

use App\Entity\ProductTranslation;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTranslationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * ProductTranslationNormalizer constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param ProductTranslation $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array()): array
    {
        $data = [
            'locale' => $object->getLocale(),
            'field' => $object->getField(),
            'content' => $object->getContent()
        ];

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
        return $data instanceof ProductTranslation;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

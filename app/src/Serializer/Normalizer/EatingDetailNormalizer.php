<?php

namespace App\Serializer\Normalizer;


use App\Entity\EatingDetail;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EatingDetailNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private ProductNormalizer $productNormalizer;

    /**
     * EatingDetailNormalizer constructor.
     *
     * @param ProductNormalizer $productNormalizer
     */
    public function __construct(ProductNormalizer $productNormalizer)
    {
        $this->productNormalizer = $productNormalizer;
    }

    /**
     * @param EatingDetail $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'id' => $object->getId(),
            'product' => $this->productNormalizer->normalize($object->getProduct()),
            'weight' => $object->getWeight()
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
        return $data instanceof EatingDetail;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

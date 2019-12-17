<?php

namespace App\Serializer\Normalizer;

use App\Entity\Product;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var UserNormalizer
     */
    private $userNormalizer;

    /**
     * ProductNormalizer constructor.
     *
     * @param UserNormalizer $userNormalizer
     */
    public function __construct(UserNormalizer $userNormalizer)
    {
        $this->userNormalizer = $userNormalizer;
    }

    /**
     * @param Product $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'proteins' => $object->getProteins(),
            'fats' => $object->getFats(),
            'carbohydrates' => $object->getCarbohydrates(),
            'calories' => $object->getCalories()
        ];

        if ($object->getUser()) {
            $data['user'] = $this->userNormalizer->normalize($object->getUser());
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
        return $data instanceof Product;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

<?php

namespace App\Serializer\Normalizer;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var UserNormalizer
     */
    private $userNormalizer;

    /**
     * @var ProductTranslationNormalizer
     */
    private $productTranslationNormalizer;

    /**
     * ProductNormalizer constructor.
     *
     * @param UserNormalizer $userNormalizer
     * @param ProductTranslationNormalizer $productTranslationNormalizer
     */
    public function __construct(
        UserNormalizer $userNormalizer,
        ProductTranslationNormalizer $productTranslationNormalizer
    )
    {
        $this->userNormalizer = $userNormalizer;
        $this->productTranslationNormalizer = $productTranslationNormalizer;
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
            'calories' => $object->getCalories(),
            'translations' => array_map(
                function (ProductTranslation $productTranslation) {
                    return $this->productTranslationNormalizer->normalize($productTranslation);
                },
                $object->getTranslations()->toArray()
            ),
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

<?php

namespace App\Serializer\Normalizer;


use App\Entity\Product;
use App\Entity\ProductTranslation;
use Symfony\Component\Security\Core\Security;
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
     * @var Security
     */
    private $security;

    /**
     * ProductNormalizer constructor.
     *
     * @param UserNormalizer $userNormalizer
     * @param ProductTranslationNormalizer $productTranslationNormalizer
     * @param Security $security
     */
    public function __construct(
        UserNormalizer $userNormalizer,
        ProductTranslationNormalizer $productTranslationNormalizer,
        Security $security
    )
    {
        $this->userNormalizer = $userNormalizer;
        $this->productTranslationNormalizer = $productTranslationNormalizer;
        $this->security = $security;
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
            'fiber' => $object->getFiber(),
            'calories' => $object->getCalories()
        ];

        if ($object->getUser()) {
            $data['user'] = $this->userNormalizer->normalize($object->getUser());
        }

        $user = $this->security->getUser();

        if ($user && $user->isAdmin()) {
            $data['translations'] = $object->getTranslations()->map(
                function (ProductTranslation $productTranslation) {
                    return $this->productTranslationNormalizer->normalize($productTranslation);
                }
            )->toArray();
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

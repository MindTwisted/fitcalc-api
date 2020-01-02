<?php

namespace App\Serializer\Normalizer;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
     * @var User
     */
    private $user;

    /**
     * ProductNormalizer constructor.
     *
     * @param UserNormalizer $userNormalizer
     * @param ProductTranslationNormalizer $productTranslationNormalizer
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        UserNormalizer $userNormalizer,
        ProductTranslationNormalizer $productTranslationNormalizer,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->userNormalizer = $userNormalizer;
        $this->productTranslationNormalizer = $productTranslationNormalizer;
        $this->user = $tokenStorage->getToken()->getUser();
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

        if ($this->user->isAdmin()) {
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

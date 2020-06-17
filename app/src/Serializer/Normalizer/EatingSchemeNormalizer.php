<?php

namespace App\Serializer\Normalizer;


use App\Entity\EatingScheme;
use App\Entity\EatingSchemeDetail;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EatingSchemeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private UserNormalizer $userNormalizer;
    private EatingSchemeDetailNormalizer $eatingSchemeDetailNormalizer;

    /**
     * EatingNormalizer constructor.
     *
     * @param UserNormalizer $userNormalizer
     * @param EatingSchemeDetailNormalizer $eatingSchemeDetailNormalizer
     */
    public function __construct(
        UserNormalizer $userNormalizer,
        EatingSchemeDetailNormalizer $eatingSchemeDetailNormalizer
    )
    {
        $this->userNormalizer = $userNormalizer;
        $this->eatingSchemeDetailNormalizer = $eatingSchemeDetailNormalizer;
    }

    /**
     * @param EatingScheme $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'isDefault' => $object->getIsDefault(),
            'eatingSchemeDetails' => $object->getEatingSchemeDetails()->map(
                function (EatingSchemeDetail $eatingSchemeDetail) {
                    return $this->eatingSchemeDetailNormalizer->normalize($eatingSchemeDetail);
                })->toArray(),
            'user' => $this->userNormalizer->normalize($object->getUser())
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
        return $data instanceof EatingScheme;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

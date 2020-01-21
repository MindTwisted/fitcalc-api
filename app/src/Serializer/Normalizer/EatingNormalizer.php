<?php

namespace App\Serializer\Normalizer;


use App\Entity\Eating;
use App\Entity\EatingDetail;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EatingNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var UserNormalizer
     */
    private $userNormalizer;

    /**
     * @var EatingDetailNormalizer
     */
    private $eatingDetailNormalizer;

    /**
     * EatingNormalizer constructor.
     *
     * @param UserNormalizer $userNormalizer
     * @param EatingDetailNormalizer $eatingDetailNormalizer
     */
    public function __construct(UserNormalizer $userNormalizer, EatingDetailNormalizer $eatingDetailNormalizer)
    {
        $this->userNormalizer = $userNormalizer;
        $this->eatingDetailNormalizer = $eatingDetailNormalizer;
    }

    /**
     * @param Eating $object
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
            'occurredAt' => $object->getOccurredAt(),
            'eatingDetails' => $object->getEatingDetails()->map(
                function (EatingDetail $eatingDetail) {
                    return $this->eatingDetailNormalizer->normalize($eatingDetail);
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
        return $data instanceof Eating;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

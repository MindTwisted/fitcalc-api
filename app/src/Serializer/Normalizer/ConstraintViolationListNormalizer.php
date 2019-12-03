<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationListNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param mixed $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $violations = $this->getViolations($object);

        return compact('violations');
    }

    /**
     * @param mixed $data
     * @param null $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ConstraintViolationListInterface;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param ConstraintViolationListInterface $constraintViolationList
     *
     * @return array
     */
    private function getViolations(ConstraintViolationListInterface $constraintViolationList): array
    {
        $violations = [];

        /** @var ConstraintViolation $violation */
        foreach ($constraintViolationList as $violation) {
            $violations[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $violations;
    }
}

<?php

namespace App\Exception;


use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends Exception
{
    /** @var ConstraintViolationListInterface */
    private $violations;

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    /**
     * @param ConstraintViolationListInterface $violations
     */
    public function setViolations(ConstraintViolationListInterface $violations): void
    {
        $this->violations = $violations;
    }
}
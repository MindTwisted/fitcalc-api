<?php

namespace App\Services;


use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    /** @var ValidatorInterface */
    private $validator;

    /**
     * ValidationService constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param $entity
     *
     * @throws ValidationException
     */
    public function validate($entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            $exception = new ValidationException('Invalid data have been provided.');
            $exception->setViolations($errors);

            throw $exception;
        }
    }
}
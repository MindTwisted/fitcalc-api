<?php

namespace App\Services;


use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ValidationService
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * ValidationService constructor.
     *
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     */
    public function __construct(ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $this->validator = $validator;
        $this->translator = $translator;
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
            $exception = new ValidationException($this->translator->trans('Invalid data have been provided.'));
            $exception->setViolations($errors);

            throw $exception;
        }
    }
}
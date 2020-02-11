<?php

namespace App\Services;


use App\Exception\ValidationException;
use LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ValidationService
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var TranslatorInterface
     */
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

    /**
     * @param FormInterface $form
     *
     * @throws ValidationException
     */
    public function validateForm(FormInterface $form): void
    {
        if (!$form->isSubmitted()) {
            throw new LogicException('Form should be submitted before validation.');
        }

        $errors = $form->getErrors(true);

        if (!count($errors)) {
            return;
        }

        $violations = new ConstraintViolationList();

        foreach ($errors as $error) {
            if ($error->getCause() instanceof CsrfToken) {
                throw new HttpException(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $error->getMessage());
            }

            $violations->add($error->getCause());
        }

        $exception = new ValidationException($this->translator->trans('Invalid data have been provided.'));
        $exception->setViolations($violations);

        throw $exception;
    }
}
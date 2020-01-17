<?php

namespace App\EventSubscriber;

use App\Exception\ValidationException;
use App\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConstraintViolationListNormalizer
     */
    private $constraintViolationListNormalizer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ExceptionSubscriber constructor.
     *
     * @param ConstraintViolationListNormalizer $constraintViolationListNormalizer
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ConstraintViolationListNormalizer $constraintViolationListNormalizer,
        TranslatorInterface $translator
    )
    {
        $this->constraintViolationListNormalizer = $constraintViolationListNormalizer;
        $this->translator = $translator;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function httpException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof HttpExceptionInterface)) {
            return;
        }

        $response = new JsonResponse(
            [
                'message' => $this->translator->trans(
                    $exception->getMessage(),
                    [],
                    'messages'
                )
            ],
            $exception->getStatusCode()
        );

        $event->setResponse($response);
    }

    /**
     * @param ExceptionEvent $event
     */
    public function validationException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof ValidationException)) {
            return;
        }

        $response = new JsonResponse(
            [
                'message' => $exception->getMessage(),
                'data' => $this->constraintViolationListNormalizer->normalize($exception->getViolations())
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );

        $event->setResponse($response);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => [
                ['httpException'],
                ['validationException']
            ]
        ];
    }
}

<?php

namespace App\EventSubscriber;

use App\Exception\ValidationException;
use App\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var ConstraintViolationListNormalizer */
    private $constraintViolationListNormalizer;

    /**
     * ExceptionSubscriber constructor.
     *
     * @param ConstraintViolationListNormalizer $constraintViolationListNormalizer
     */
    public function __construct(ConstraintViolationListNormalizer $constraintViolationListNormalizer)
    {
        $this->constraintViolationListNormalizer = $constraintViolationListNormalizer;
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
                'message' => $exception->getMessage()
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

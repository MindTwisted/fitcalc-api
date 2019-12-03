<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
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

    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => [
                'httpException'
            ]
        ];
    }
}

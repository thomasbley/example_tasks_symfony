<?php

namespace App\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        } else {
            // log uncaught exceptions as E_USER_WARNING
            trigger_error((string) $exception, E_USER_WARNING);

            $response = new JsonResponse(['error' => 'internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}

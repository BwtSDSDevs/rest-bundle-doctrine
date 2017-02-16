<?php

namespace Dontdrinkandroot\RestBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class KernelExceptionListener
{
    /**
     * @var string[]
     */
    private $paths;

    function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        if (!$this->isInterceptionPath($request)) {
            return;
        }

        $data = [
            'message' => $exception->getMessage()
        ];

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $response = new JsonResponse();

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->add($exception->getHeaders());
        }

        $response->setStatusCode($statusCode);

        $event->setResponse($response);
    }

    private function isInterceptionPath(?Request $request): bool
    {
        if (null === $request) {
            return false;
        }

        foreach ($this->paths as $path) {
            if (0 === strpos($request->getPathInfo(), $path)) {
                return true;
            }
        }

        return false;
    }
}

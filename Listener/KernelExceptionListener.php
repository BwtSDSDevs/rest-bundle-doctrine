<?php

namespace Dontdrinkandroot\RestBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class KernelExceptionListener
{
    /**
     * @var string[]
     */
    private $paths;

    /**
     * @var bool
     */
    private $debug = false;

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

        $data = null;
        if ($this->debug) {
            $data = [
                'class'   => get_class($exception),
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTrace()
            ];
        }

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof InsufficientAuthenticationException) {
            $statusCode = Response::HTTP_UNAUTHORIZED;
        }

        if ($exception instanceof BadCredentialsException) {
            $statusCode = Response::HTTP_UNAUTHORIZED;
        }

        $response = new JsonResponse($data);

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

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }
}

<?php

namespace SdsDev\RestBundleDoctrine\Listener;

use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

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

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
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

        $statusCode = $this->resolveStatusCode($exception);

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

    /**
     * @param $exception
     *
     * @return int
     */
    protected function resolveStatusCode($exception): int
    {
        if ($exception instanceof InsufficientAuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        if ($exception instanceof BadCredentialsException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        if ($exception instanceof OptimisticLockException) {
            return Response::HTTP_PRECONDITION_FAILED;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}

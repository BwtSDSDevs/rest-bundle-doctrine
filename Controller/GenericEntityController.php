<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Service\EntityServiceInterface;
use Dontdrinkandroot\Service\UuidEntityServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GenericEntityController extends DdrRestController
{
    private $service;

    public function listAction(Request $request)
    {
        $service = $this->getService($request);
        $paginatedResult = $service->listPaginated(1, 10);
        $view = $this->view($paginatedResult->getResults());
        $this->addPaginationHeaders($paginatedResult->getPagination(), $view);

        return $this->handleView($view);
    }

    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->handleView($this->view(null, Response::HTTP_CREATED));
    }

    public function getAction(Request $request, $id)
    {
    }

    public function putAction(Request $request, $id)
    {
    }

    public function deleteAction(Request $request, $id)
    {
    }

    /**
     * @param Request $request
     *
     * @return EntityServiceInterface|UuidEntityServiceInterface
     */
    private function getService(Request $request)
    {
        if (null === $this->service) {
            $serviceName = $request->attributes->get('_service');
            if (null === $serviceName) {
                throw new \RuntimeException('No service given');
            }
            /** @var EntityServiceInterface|UuidEntityServiceInterface $service */
            $service = $this->get($serviceName);

            return $service;
        }

        return $this->service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }
}

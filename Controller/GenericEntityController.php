<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Entity\EntityInterface;
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
        $entity = $this->parseRequest($request);
        $entity = $this->postProcessPostedEntity($request, $entity);
        $errors = $this->validate($entity);
        if ($errors->count() > 0) {
            return $this->handleView($this->view($errors, Response::HTTP_BAD_REQUEST));
        }
        $entity = $this->getService($request)->save($entity);

        return $this->handleView($this->view($entity, Response::HTTP_CREATED));
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
    protected function getService(Request $request)
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

    private function parseRequest(Request $request, EntityInterface $entity = null)
    {
        return $this->get('ddr.rest.parser.request')->parseEntity(
            $request,
            $this->getService($request)->getEntityClass(),
            $entity
        );
    }

    /**
     * @param Request         $request
     * @param EntityInterface $entity
     *
     * @return EntityInterface
     */
    protected function postProcessPostedEntity(Request $request, EntityInterface $entity)
    {
        return $entity;
    }
}

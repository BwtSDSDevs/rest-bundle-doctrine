<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Entity\EntityInterface;
use Dontdrinkandroot\Entity\UuidEntityInterface;
use Dontdrinkandroot\FullStackTestBundle\Entity\BlogPost;
use Dontdrinkandroot\Repository\UuidEntityRepositoryInterface;
use Dontdrinkandroot\Service\EntityService;
use Dontdrinkandroot\Service\EntityServiceInterface;
use Dontdrinkandroot\Service\UuidEntityService;
use Dontdrinkandroot\Service\UuidEntityServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityController extends DdrRestController
{
    public function listAction(Request $request)
    {
        $this->assertListGranted();
        $service = $this->getService();
        $paginatedResult = $service->listPaginated(1, 10);
        $view = $this->view($paginatedResult->getResults());
        $this->addPaginationHeaders($paginatedResult->getPagination(), $view);

        return $this->handleView($view);
    }

    public function postAction(Request $request)
    {
        $this->assertPostGranted();
        $entity = $this->parseRequest($request);
        $entity = $this->postProcessPostedEntity($entity);
        $errors = $this->validate($entity);
        if ($errors->count() > 0) {
            return $this->handleView($this->view($errors, Response::HTTP_BAD_REQUEST));
        }
        $entity = $this->getService()->save($entity);

        return $this->handleView($this->view($entity, Response::HTTP_CREATED));
    }

    public function getAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertGetGranted($entity);
        $view = $this->view($entity);
        $view->getContext()->addGroups(['Default', 'ddr.rest.get']);

        return $this->handleView($view);
    }

    public function putAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertPutGranted($entity);
        $entity = $this->parseRequest($request, $entity);
        $entity = $this->postProcessPuttedEntity($entity);
        $errors = $this->validate($entity);
        if ($errors->count() > 0) {
            return $this->handleView($this->view($errors, Response::HTTP_BAD_REQUEST));
        }
        $entity = $this->getService()->save($entity);

        return $this->handleView($this->view($entity));
    }

    public function deleteAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertDeleteGranted($entity);
        $this->getService()->remove($entity);

        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * @return EntityServiceInterface|UuidEntityServiceInterface
     */
    protected function getService()
    {
        $serviceId = $this->getServiceId();
        if (null === $serviceId) {
            $entityClass = $this->getEntityClass();
            if (null === $entityClass) {
                throw new \RuntimeException('No service or entity class given');
            }
            $repository = $this->get('doctrine')->getRepository($entityClass);
            if ($repository instanceof UuidEntityRepositoryInterface) {
                return new EntityService($repository);
            } else {
                return new UuidEntityService($repository);
            }
        } else {
            /** @var EntityServiceInterface|UuidEntityServiceInterface $service */
            $service = $this->get($serviceId);

            return $service;
        }
    }

    protected function parseRequest(Request $request, EntityInterface $entity = null)
    {
        return $this->get('ddr.rest.parser.request')->parseEntity(
            $request,
            $this->getService()->getEntityClass(),
            $entity
        );
    }

    /**
     * @param EntityInterface $entity
     *
     * @return EntityInterface
     */
    protected function postProcessPostedEntity(EntityInterface $entity)
    {
        return $entity;
    }

    /**
     * @param EntityInterface $entity
     *
     * @return EntityInterface
     */
    protected function postProcessPuttedEntity(EntityInterface $entity)
    {
        return $entity;
    }

    protected function fetchEntity($id)
    {
        if (is_a($this->getEntityClass(), UuidEntityInterface::class, true)) {
            return $this->getService()->fetchByUuid($id);
        } else {
            return $this->getService()->fetchById($id);
        }
    }

    protected function isUuid($id)
    {
        return preg_match('/' . UuidEntityInterface::VALID_UUID_PATTERN . '/', $id);
    }

    protected function getEntityClass()
    {
        return $this->getCurrentRequest()->attributes->get('_entityClass');
    }

    protected function getServiceId()
    {
        return $this->getCurrentRequest()->attributes->get('_service');
    }

    protected function getCurrentRequest()
    {
        return $this->get('request_stack')->getCurrentRequest();
    }

    protected function assertListGranted()
    {
    }

    protected function assertPostGranted()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
    }

    protected function assertGetGranted(EntityInterface $entity)
    {
    }

    protected function assertPutGranted(EntityInterface $entity)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }

        /** @var BlogPost $entity */
        if (!($entity->getAuthor()->getId() === $this->getUser()->getId())) {
            throw $this->createAccessDeniedException();
        }
    }

    protected function assertDeleteGranted(EntityInterface $entity)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException();
        }

        /** @var BlogPost $entity */
        if (!($entity->getAuthor()->getId() === $this->getUser()->getId())) {
            throw $this->createAccessDeniedException();
        }
    }
}

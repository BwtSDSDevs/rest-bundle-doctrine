<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Doctrine\Common\Util\Inflector;
use Dontdrinkandroot\Entity\EntityInterface;
use Dontdrinkandroot\Entity\UuidEntityInterface;
use Dontdrinkandroot\Pagination\PaginatedResult;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Service\CrudServiceInterface;
use Dontdrinkandroot\RestBundle\Service\DoctrineEntityRepositoryCrudService;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityController extends DdrRestController
{
    public function listAction(Request $request)
    {
        $this->assertListGranted();
        $result = $this->listEntities($request->query->get('page', 1), $request->query->get('perPage', 50));

        return new JsonResponse(iterator_to_array($result));
        //$view = $this->createViewFromListResult($result);
        //$view->getContext()->addGroups(['Default', 'ddr.rest.list']);

        //return $this->handleView($view);
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
        $entity = $this->createEntity($entity);

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
        $entity = $this->updateEntity($entity);

        return $this->handleView($this->view($entity));
    }

    public function deleteAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertDeleteGranted($entity);
        $this->getService()->remove($entity);

        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    public function listSubresourceAction(Request $request, $id)
    {
        $subresource = $this->getSubresource();
        $entity = $this->fetchEntity($id);
        $this->assertSubresourceListGranted($entity, $subresource);
        $result = $this->listSubresource(
            $entity,
            $subresource,
            $request->query->get('page', 1),
            $request->query->get('perPage', 50)
        );
        $view = $this->createViewFromListResult($result);
        $view->getContext()->addGroups($this->getSubresourceSerializationGroups($subresource));

        return $this->handleView($view);
    }

    public function postSubresourceAction(Request $request, $id)
    {
        $subresource = $this->getSubresource();
        $parent = $this->fetchEntity($id);
        $this->assertSubresourcePostGranted($parent, $subresource);
        $entity = $this->parseRequest($request, null, $this->getSubResourceEntityClass($subresource));
        $entity = $this->postProcessSubResourcePostedEntity($subresource, $entity, $parent);
        $errors = $this->validate($entity);
        if ($errors->count() > 0) {
            return $this->handleView($this->view($errors, Response::HTTP_BAD_REQUEST));
        }
        $entity = $this->saveSubResource($subresource, $entity);

        return $this->handleView($this->view($entity, Response::HTTP_CREATED));
    }

    /**
     * @return CrudServiceInterface
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

            return new DoctrineEntityRepositoryCrudService($repository);
        } else {
            /** @var CrudServiceInterface $service */
            $service = $this->get($serviceId);

            return $service;
        }
    }

    protected function parseRequest(Request $request, EntityInterface $entity = null, $entityClass = null)
    {
        if (null === $entityClass) {
            $entityClass = $this->getService()->getEntityClass();
        }

        return $this->get('ddr.rest.parser.request')->parseEntity($request, $entityClass, $entity);
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

    /**
     * @param string          $subresource
     * @param EntityInterface $parent
     * @param EntityInterface $entity
     *
     * @return EntityInterface
     */
    protected function postProcessSubResourcePostedEntity(
        $subresource,
        EntityInterface $entity,
        EntityInterface $parent
    ) {
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

    protected function listEntities($page = 1, $perPage = 50)
    {
        $service = $this->getService();

        return $service->listPaginated($page, $perPage);
    }

    protected function createEntity(EntityInterface $entity)
    {
        return $this->getService()->save($entity);
    }

    protected function updateEntity(EntityInterface $entity)
    {
        return $this->getService()->save($entity);
    }

    protected function listSubresource(EntityInterface $entity, $subresource, $page = 1, $perPage = 50)
    {
        $propertyAccessor = $this->container->get('property_accessor');

        return $propertyAccessor->getValue($entity, $subresource);
    }

    protected function isUuid($id)
    {
        return preg_match('/' . UuidEntityInterface::VALID_UUID_PATTERN . '/', $id);
    }

    protected function getEntityClass()
    {
        return $this->getCurrentRequest()->attributes->get('_entityClass');
    }

    protected function getShortName()
    {
        return Inflector::tableize($this->getClassMetadata()->reflection->getShortName());
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
        $classMetadata = $this->getClassMetadata();
        $right = $classMetadata->getListRight();
        if (null === $right) {
            return;
        }

        $this->denyAccessUnlessGranted($right->attributes);
    }

    protected function assertPostGranted()
    {
        $classMetadata = $this->getClassMetadata();
        $right = $classMetadata->getPostRight();
        if (null === $right) {
            throw $this->createAccessDeniedException();
        }

        $this->denyAccessUnlessGranted($right->attributes);
    }

    protected function assertGetGranted(EntityInterface $entity)
    {
        $classMetadata = $this->getClassMetadata();
        $right = $classMetadata->getGetRight();
        if (null === $right) {
            return;
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertPutGranted(EntityInterface $entity)
    {
        $classMetadata = $this->getClassMetadata();
        $right = $classMetadata->getPutRight();
        if (null === $right) {
            throw $this->createAccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertDeleteGranted(EntityInterface $entity)
    {
        $classMetadata = $this->getClassMetadata();
        $right = $classMetadata->getDeleteRight();
        if (null === $right) {
            throw $this->createAccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertSubresourceListGranted(EntityInterface $entity, $subresource)
    {
        $classMetadata = $this->getClassMetadata();
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata[$subresource];
        $right = $propertyMetadata->getSubResourceListRight();
        if (null === $right) {
            return;
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertSubresourcePostGranted(EntityInterface $entity, $subresource)
    {
        $classMetadata = $this->getClassMetadata();
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata[$subresource];
        $right = $propertyMetadata->getSubResourcePostRight();
        if (null === $right) {
            throw $this->createAccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    /**
     * @param array|PaginatedResult $result
     *
     * @return View
     */
    protected function createViewFromListResult($result)
    {
        if ($result instanceof PaginatedResult) {
            $view = $this->view($result->getResults());
            $this->addPaginationHeaders($result->getPagination(), $view);

            return $view;
        }

        return $this->view($result);
    }

    /**
     * @return ClassMetadata
     */
    protected function getClassMetadata()
    {
        $metaDataFactory = $this->get('ddr_rest.metadata.factory');
        /** @var ClassMetadata $classMetaData */
        $classMetaData = $metaDataFactory->getMetadataForClass($this->getEntityClass());

        return $classMetaData;
    }

    protected function getSubResourceEntityClass($subresource)
    {
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $this->getClassMetadata()->propertyMetadata[$subresource];

        return $propertyMetadata->getSubResourceEntityClass();
    }

    protected function resolveSubject(EntityInterface $entity, $propertyPath)
    {
        if ('this' === $propertyPath) {
            return $entity;
        }
        $propertyAccessor = $this->get('property_accessor');

        return $propertyAccessor->getValue($entity, $propertyPath);
    }

    /**
     * @param EntityInterface $entity
     * @param Right           $right
     */
    protected function assertRightGranted(EntityInterface $entity, Right $right)
    {
        $propertyPath = $right->propertyPath;
        if (null === $propertyPath) {
            $this->denyAccessUnlessGranted($right->attributes);
        } else {
            $subject = $this->resolveSubject($entity, $propertyPath);
            $this->denyAccessUnlessGranted($right->attributes, $subject);
        }
    }

    /**
     * @return string[]
     */
    protected function getSubresourceSerializationGroups($subresource)
    {
        return ['Default', 'ddr.rest.subresource', 'ddr.rest.' . $this->getShortName() . '.' . $subresource];
    }

    /**
     * @param string          $subresource
     * @param EntityInterface $entity
     *
     * @return EntityInterface
     */
    protected function saveSubResource($subresource, EntityInterface $entity)
    {
        return $this->getService()->save($entity);
    }

    /**
     * @return string|null
     */
    protected function getSubresource()
    {
        return $this->getCurrentRequest()->attributes->get('_subresource');
    }
}

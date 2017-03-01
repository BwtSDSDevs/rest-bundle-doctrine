<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Service\Normalizer;
use Dontdrinkandroot\RestBundle\Service\RestRequestParser;
use Dontdrinkandroot\Service\CrudServiceInterface;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RestResourceController implements ContainerAwareInterface, RestResourceControllerInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 50);

        $this->assertListGranted();

        $listResult = $this->listEntities($page, $perPage);

        $response = new JsonResponse();

        if ($listResult instanceof Paginator) {
            $entities = $listResult->getIterator()->getArrayCopy();
            $total = $listResult->count();
            $this->addPaginationHeaders($response, $page, $perPage, $total);
        } else {
            $entities = $listResult;
        }

        $content = $this->getNormalizer()->normalize($entities, $this->parseIncludes($request));

        $response->setData($content);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function postAction(Request $request)
    {
        $this->assertPostGranted();
        $entity = $this->getRequestParser()->parseEntity($request, $this->getEntityClass());
        $entity = $this->postProcessPostedEntity($entity);

        $errors = $this->getValidator()->validate($entity);
        if ($errors->count() > 0) {
            return new JsonResponse($this->parseConstraintViolations($errors), Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->createEntity($entity);

        $content = $this->getNormalizer()->normalize($entity, $this->parseIncludes($request));

        return new JsonResponse($content, Response::HTTP_CREATED);
    }

    /**
     * {@inheritdoc}
     */
    public function getAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertGetGranted($entity);

        $content = $this->getNormalizer()->normalize($entity, $this->parseIncludes($request));

        return new JsonResponse($content);
    }

    /**
     * {@inheritdoc}
     */
    public function putAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertPutGranted($entity);
        $entity = $this->getRequestParser()->parseEntity($request, $this->getEntityClass(), $entity);
        $entity = $this->postProcessPuttedEntity($entity);

        $errors = $this->getValidator()->validate($entity);
        if ($errors->count() > 0) {
            return new JsonResponse($this->parseConstraintViolations($errors), Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->updateEntity($entity);

        $content = $this->getNormalizer()->normalize($entity, $this->parseIncludes($request));

        return new JsonResponse($content);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertDeleteGranted($entity);
        $this->getService()->remove($entity);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function listSubresourceAction(Request $request, $id)
    {
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 50);

        $subresource = $this->getSubresource();
        $entity = $this->fetchEntity($id);
        $this->assertSubresourceListGranted($entity, $subresource);

        $listResult = $this->listSubresource($entity, $subresource, $page, $perPage);

        $response = new JsonResponse();

        if ($listResult instanceof Paginator) {
            $entities = $listResult->getIterator()->getArrayCopy();
            $total = $listResult->count();
            $this->addPaginationHeaders($response, $page, $perPage, $total);
        } else {
            $entities = $listResult;
        }

        $content = $this->getNormalizer()->normalize($entities, $this->parseIncludes($request));

        $response->setData($content);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function postSubresourceAction(Request $request, $id)
    {
        $subresource = $this->getSubresource();
        $parent = $this->fetchEntity($id);
        $this->assertSubresourcePostGranted($parent, $subresource);
        $entity = $this->getRequestParser()->parseEntity(
            $request,
            $this->getSubResourceEntityClass($subresource)
        );
        $entity = $this->postProcessSubResourcePostedEntity($subresource, $entity, $parent);

        $errors = $this->getValidator()->validate($entity);

        if ($errors->count() > 0) {
            return new JsonResponse($this->parseConstraintViolations($errors), Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->createSubResource($parent, $subresource, $entity);

        $content = $this->getNormalizer()->normalize($entity, $this->parseIncludes($request));

        return new JsonResponse($content, Response::HTTP_CREATED);
    }

    /**
     * {@inheritdoc}
     */
    public function putSubresourceAction(Request $request, $id, $subId)
    {
        $subresource = $this->getSubresource();
        $parent = $this->fetchEntity($id);
        $this->assertSubresourcePutGranted($parent, $subresource);
        $this->getService()->addToCollection($parent, $subresource, $subId);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSubresourceAction(Request $request, $id, $subId)
    {
        $subresource = $this->getSubresource();
        $parent = $this->fetchEntity($id);
        $this->assertSubresourceDeleteGranted($parent, $subresource);
        $this->getService()->removeFromCollection($parent, $subresource, $subId);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return CrudServiceInterface
     */
    protected function getService(): CrudServiceInterface
    {
        $serviceId = $this->getServiceId();
        if (null === $serviceId) {
            $entityClass = $this->getEntityClass();
            if (null === $entityClass) {
                throw new \RuntimeException('No service or entity class given');
            }
            /** @var EntityManagerInterface $entityManager */
            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $repository = $entityManager->getRepository($entityClass);
            if (!$repository instanceof CrudServiceInterface) {
                throw new \RuntimeException(
                    'Your Entity Repository needs to be an instance of ' . CrudServiceInterface::class . '.'
                );
            }

            return $repository;
        } else {
            /** @var CrudServiceInterface $service */
            $service = $this->container->get($serviceId);

            return $service;
        }
    }

    /**
     * @param object $entity
     *
     * @return object
     */
    protected function postProcessPostedEntity($entity)
    {
        return $entity;
    }

    /**
     * @param object $entity
     *
     * @return object
     */
    protected function postProcessPuttedEntity($entity)
    {
        return $entity;
    }

    /**
     * @param string $subresource
     * @param object $parent
     * @param object $entity
     *
     * @return object
     */
    protected function postProcessSubResourcePostedEntity($subresource, $entity, $parent)
    {
        return $entity;
    }

    protected function fetchEntity($id)
    {
        $entity = $this->getService()->find($id);
        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    /**
     * @param int $page
     * @param int $perPage
     *
     * @return Paginator|array
     */
    protected function listEntities(int $page = 1, int $perPage = 50)
    {
        return $this->getService()->findAllPaginated($page, $perPage);
    }

    protected function createEntity($entity)
    {
        return $this->getService()->create($entity);
    }

    protected function updateEntity($entity)
    {
        return $this->getService()->update($entity);
    }

    /**
     * @param object $entity
     * @param string $property
     * @param int    $page
     * @param int    $perPage
     *
     * @return Paginator|array
     */
    protected function listSubresource($entity, string $property, int $page = 1, int $perPage = 50)
    {
        return $this->getService()->findAssociationPaginated($entity, $property, $page, $perPage);
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
        return $this->getRequestStack()->getCurrentRequest();
    }

    protected function assertListGranted()
    {
        $method = $this->getClassMetadata()->getMethod(Method::LIST);
        if ($method !== null && null !== $right = $method->right) {
            $this->denyAccessUnlessGranted($right->attributes);
        }
    }

    protected function assertPostGranted()
    {
        $method = $this->getClassMetadata()->getMethod(Method::POST);
        $right = $method->right;
        if (null === $right) {
            throw new AccessDeniedException();
        }

        $this->denyAccessUnlessGranted($right->attributes);
    }

    protected function assertGetGranted($entity)
    {
        $method = $this->getClassMetadata()->getMethod(Method::GET);
        if ($method !== null && null !== $right = $method->right) {
            $this->assertRightGranted($entity, $right);
        }
    }

    protected function assertPutGranted($entity)
    {
        $method = $this->getClassMetadata()->getMethod(Method::PUT);
        $right = $method->right;
        if (null === $right) {
            throw new AccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertDeleteGranted($entity)
    {
        $method = $this->getClassMetadata()->getMethod(Method::POST);
        $right = $method->right;
        if (null === $right) {
            throw new AccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertSubresourceListGranted($entity, $subresource)
    {
        $classMetadata = $this->getClassMetadata();
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata[$subresource];
        $method = $propertyMetadata->getMethod(Method::LIST);
        $right = $method->right;
        if (null === $right) {
            return;
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertSubresourcePostGranted($entity, $subresource)
    {
        $classMetadata = $this->getClassMetadata();
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata[$subresource];
        $method = $propertyMetadata->getMethod(Method::POST);
        $right = $method->right;
        if (null === $right) {
            throw new AccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertSubresourcePutGranted($entity, $subresource)
    {
        $classMetadata = $this->getClassMetadata();
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata[$subresource];
        $method = $propertyMetadata->getMethod(Method::PUT);
        $right = $method->right;
        if (null === $right) {
            throw new AccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    protected function assertSubresourceDeleteGranted($entity, $subresource)
    {
        $classMetadata = $this->getClassMetadata();
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata[$subresource];
        $method = $propertyMetadata->getMethod(Method::DELETE);
        $right = $method->right;
        if (null === $right) {
            throw new AccessDeniedException();
        }

        $this->assertRightGranted($entity, $right);
    }

    /**
     * @return ClassMetadata
     */
    protected function getClassMetadata()
    {
        $metaDataFactory = $this->getMetadataFactory();
        /** @var ClassMetadata $classMetaData */
        $classMetaData = $metaDataFactory->getMetadataForClass($this->getEntityClass());

        return $classMetaData;
    }

    protected function getSubResourceEntityClass($subresource)
    {
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $this->getClassMetadata()->propertyMetadata[$subresource];

        return $propertyMetadata->getType();
    }

    protected function resolveSubject($entity, $propertyPath)
    {
        if ('this' === $propertyPath) {
            return $entity;
        }
        $propertyAccessor = $this->getPropertyAccessor();

        return $propertyAccessor->getValue($entity, $propertyPath);
    }

    /**
     * @param object $entity
     * @param Right  $right
     */
    protected function assertRightGranted($entity, Right $right)
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
     * @param object $parent
     * @param string $subresource
     * @param object $entity
     *
     * @return
     */
    protected function createSubResource($parent, $subresource, $entity)
    {
        return $this->getService()->createAssociation($parent, $subresource, $entity);
    }

    /**
     * @return string|null
     */
    protected function getSubresource()
    {
        return $this->getCurrentRequest()->attributes->get('_subresource');
    }

    protected function parseIncludes(Request $request)
    {
        $defaultIncludes = $request->attributes->get('_defaultincludes');
        if (null == $defaultIncludes) {
            $defaultIncludes = [];
        }

        $includeString = $request->query->get('include');
        if (empty($includeString)) {
            $includes = [];
        } else {
            $includes = explode(',', $includeString);
        }

        return array_merge($defaultIncludes, $includes);
    }

    private function parseConstraintViolations(ConstraintViolationListInterface $errors)
    {
        $data = [];
        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $data[] = [
                'propertyPath' => $error->getPropertyPath(),
                'message'      => $error->getMessage(),
                'value'        => $error->getInvalidValue()
            ];
        }

        return $data;
    }

    private function addPaginationHeaders(Response $response, int $page, int $perPage, int $total)
    {
        $response->headers->add(
            [
                'x-pagination-current-page' => $page,
                'x-pagination-per-page'     => $perPage,
                'x-pagination-total'        => $total,
                'x-pagination-total-pages'  => (int)(($total - 1) / $perPage + 1)
            ]
        );
    }

    protected function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
    {
        if (!$this->getAuthorizationChecker()->isGranted($attributes, $object)) {
            throw new AccessDeniedException($message);
        }
    }

    /**
     * @return Normalizer
     */
    protected function getNormalizer()
    {
        return $this->container->get('ddr_rest.normalizer');
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->container->get('validator');
    }

    /**
     * @return RestRequestParser
     */
    protected function getRequestParser()
    {
        return $this->container->get('ddr.rest.parser.request');
    }

    /**
     * @return RequestStack
     */
    protected function getRequestStack()
    {
        return $this->container->get('request_stack');
    }

    /**
     * @return MetadataFactoryInterface
     */
    protected function getMetadataFactory()
    {
        return $this->container->get('ddr_rest.metadata.factory');
    }

    /**
     * @return PropertyAccessorInterface
     */
    protected function getPropertyAccessor()
    {
        return $this->container->get('property_accessor');
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    protected function getAuthorizationChecker()
    {
        return $this->container->get('security.authorization_checker');
    }
}

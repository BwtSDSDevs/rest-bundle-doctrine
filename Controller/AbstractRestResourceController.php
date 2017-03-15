<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Service\Normalizer;
use Dontdrinkandroot\RestBundle\Service\RestRequestParser;
use Metadata\MetadataFactoryInterface;
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

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
abstract class AbstractRestResourceController implements RestResourceControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 50);

        $this->assertMethodGranted(Method::LIST);

        $listResult = $this->listEntities($page, $perPage);

        $response = new JsonResponse();

        if ($listResult instanceof Paginator) {
            $entities = iterator_to_array($listResult->getIterator());
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
        $this->assertMethodGranted(Method::POST);

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
        $this->assertMethodGranted(Method::GET, $entity);

        $content = $this->getNormalizer()->normalize($entity, $this->parseIncludes($request));

        return new JsonResponse($content);
    }

    /**
     * {@inheritdoc}
     */
    public function putAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->assertMethodGranted(Method::PUT, $entity);
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
        $this->assertMethodGranted(Method::DELETE, $entity);
        $this->removeEntity($entity);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function listSubresourceAction(Request $request, $id, string $subresource)
    {
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 50);

        $entity = $this->fetchEntity($id);
        $this->assertSubResourceMethodGranted(Method::LIST, $entity, $subresource);

        $listResult = $this->listSubresource($entity, $subresource, $page, $perPage);

        $response = new JsonResponse();

        if ($listResult instanceof Paginator) {
            $entities = iterator_to_array($listResult->getIterator());
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
    public function postSubresourceAction(Request $request, $id, string $subresource)
    {
        $parent = $this->fetchEntity($id);
        $this->assertSubResourceMethodGranted(Method::POST, $parent, $subresource);

        $restRequestParser = $this->getRequestParser();
        $entity = $this->createAssociation($parent, $subresource);
        $entity = $restRequestParser->parseEntity($request, $this->getSubResourceEntityClass($subresource), $entity);

        $entity = $this->postProcessSubResourcePostedEntity($parent, $subresource, $entity);

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
    public function putSubresourceAction(Request $request, $id, string $subresource, $subId)
    {
        $parent = $this->fetchEntity($id);
        $this->assertSubResourceMethodGranted(Method::PUT, $parent, $subresource);
        $this->addAssociation($parent, $subresource, $subId);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSubresourceAction(Request $request, $id, string $subresource, $subId = null)
    {
        $parent = $this->fetchEntity($id);
        $this->assertSubResourceMethodGranted(Method::DELETE, $parent, $subresource);
        $this->removeAssociation($parent, $subresource, $subId);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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
     * @param object $parent
     * @param string $subresource
     * @param object $entity
     *
     * @return object
     */
    protected function postProcessSubResourcePostedEntity($parent, $subresource, $entity)
    {
        return $entity;
    }

    protected function getEntityClass()
    {
        return $this->getCurrentRequest()->attributes->get('_entityClass');
    }

    protected function getSubResourceEntityClass($subresource)
    {
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $this->getClassMetadata()->propertyMetadata[$subresource];

        return $propertyMetadata->getType();
    }

    protected function getCurrentRequest()
    {
        return $this->getRequestStack()->getCurrentRequest();
    }

    protected function assertMethodGranted(string $methodName, $entity = null)
    {
        $method = $this->getClassMetadata()->getMethod($methodName);
        if ($method !== null && null !== $right = $method->right) {
            $this->assertRightGranted($right, $entity);
        }
    }

    /**
     * @param string $methodName
     * @param object $entity
     * @param string $subresource
     */
    protected function assertSubResourceMethodGranted($methodName, $entity, string $subresource): void
    {
        $classMetadata = $this->getClassMetadata();
        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata[$subresource];
        $method = $propertyMetadata->getMethod($methodName);
        if (null !== $right = $method->right) {
            $this->assertRightGranted($right, $entity);
        }
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

    protected function resolveSubject($entity, $propertyPath)
    {
        if ('this' === $propertyPath) {
            return $entity;
        }
        $propertyAccessor = $this->getPropertyAccessor();

        return $propertyAccessor->getValue($entity, $propertyPath);
    }

    /**
     * @param Right  $right
     * @param object $entity
     */
    protected function assertRightGranted(Right $right, $entity = null)
    {
        $propertyPath = $right->propertyPath;
        if (null === $propertyPath || null == $entity) {
            $this->denyAccessUnlessGranted($right->attributes);
        } else {
            $subject = $this->resolveSubject($entity, $propertyPath);
            $this->denyAccessUnlessGranted($right->attributes, $subject);
        }
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

    protected function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
    {
        if (!$this->getAuthorizationChecker()->isGranted($attributes, $object)) {
            throw new AccessDeniedException($message);
        }
    }

    protected function parseConstraintViolations(ConstraintViolationListInterface $errors)
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

    protected function addPaginationHeaders(Response $response, int $page, int $perPage, int $total)
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

    /**
     * @param int $page
     * @param int $perPage
     *
     * @return Paginator|array
     */
    abstract protected function listEntities(int $page = 1, int $perPage = 50);

    /**
     * @param int|string $id
     *
     * @return object
     *
     * @throws NotFoundHttpException Thrown if entity with the given id could not be found.
     */
    abstract protected function fetchEntity($id);

    /**
     * @param object $entity
     *
     * @return object
     */
    abstract protected function createEntity($entity);

    /**
     * @param object $entity
     *
     * @return object
     *
     * @throws NotFoundHttpException Thrown if entity with the given id could not be found.
     */
    abstract protected function updateEntity($entity);

    /**
     * @param $entity
     *
     * @throws NotFoundHttpException Thrown if entity with the given id could not be found.
     */
    abstract protected function removeEntity($entity);

    /**
     * @param object $entity
     * @param string $property
     * @param int    $page
     * @param int    $perPage
     *
     * @return Paginator|array
     */
    abstract protected function listSubresource($entity, string $property, int $page = 1, int $perPage = 50);

    /**
     * @param object $parent
     * @param string $subresource
     *
     * @return object
     */
    abstract protected function createAssociation($parent, string $subresource);

    /**
     * @param object $parent
     * @param string $subresource
     * @param object $entity
     *
     * @return object
     */
    abstract protected function createSubResource($parent, $subresource, $entity);

    /**
     * @param object     $parent
     * @param string     $subresource
     * @param int|string $subId
     *
     * @return object
     */
    abstract protected function addAssociation($parent, string $subresource, $subId);

    /**
     * @param object          $parent
     * @param string          $subresource
     * @param int|string|null $subId
     *
     * @return mixed
     */
    abstract protected function removeAssociation($parent, string $subresource, $subId = null);

    /**
     * @return Normalizer
     */
    abstract protected function getNormalizer();

    /**
     * @return ValidatorInterface
     */
    abstract protected function getValidator();

    /**
     * @return RestRequestParser
     */
    abstract protected function getRequestParser();

    /**
     * @return RequestStack
     */
    abstract protected function getRequestStack();

    /**
     * @return MetadataFactoryInterface
     */
    abstract protected function getMetadataFactory();

    /**
     * @return PropertyAccessorInterface
     */
    abstract protected function getPropertyAccessor();

    /**
     * @return AuthorizationCheckerInterface
     */
    abstract protected function getAuthorizationChecker();

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}

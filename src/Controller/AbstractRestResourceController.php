<?php

namespace Niebvelungen\RestBundleDoctrine\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Dontdrinkandroot\Common\CrudOperation;
use Niebvelungen\RestBundleDoctrine\Defaults\Defaults;
use Niebvelungen\RestBundleDoctrine\Exceptions\InvalidFilterException;
use Niebvelungen\RestBundleDoctrine\Metadata\Attribute\Right;
use Niebvelungen\RestBundleDoctrine\Metadata\ClassMetadata;
use Niebvelungen\RestBundleDoctrine\Metadata\PropertyMetadata;
use Niebvelungen\RestBundleDoctrine\Metadata\RestMetadataFactory;
use Niebvelungen\RestBundleDoctrine\Serializer\RestDenormalizer;
use Niebvelungen\RestBundleDoctrine\Serializer\RestNormalizer;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRestResourceController implements RestResourceControllerInterface
{
    private ValidatorInterface $validator;

    private RequestStack $requestStack;

    private RestMetadataFactory $metadataFactory;

    private PropertyAccessorInterface $propertyAccessor;

    private AuthorizationCheckerInterface $authorizationChecker;

    private SerializerInterface $serializer;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        RestMetadataFactory $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        SerializerInterface $serializer
    ) {
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function searchEntityAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 50);

        $this->assertMethodGranted(CrudOperation::LIST);

        try {
            $listResult = $this->searchEntities($request);
        }
        catch (InvalidFilterException $exception){
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $response = new JsonResponse();

        if ($listResult instanceof Paginator) {
            $entities = iterator_to_array($listResult->getIterator());
            $total = $listResult->count();
            $this->addPaginationHeaders($response, $page, $perPage, $total);
        } else {
            $entities = $listResult;
        }

        $json = $this->getSerializer()->serialize(
            $entities,
            Defaults::SERIALIZE_FORMAT,
            [
                RestNormalizer::DDR_REST_INCLUDES => $this->parseIncludes($request),
                RestNormalizer::DDR_REST_DEPTH => 0,
                RestNormalizer::DDR_REST_PATH => ''
            ]
        );
        $response->setJson($json);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function updateEntityAction(Request $request)
    {
        $this->assertMethodGranted(CrudOperation::CREATE);

        $entity = $this->serializer->deserialize(
            $request->getContent(),
            $this->getEntityClass(),
            'json',
            [RestDenormalizer::DDR_REST_METHOD => CrudOperation::CREATE]
        );
        $entity = $this->postProcessPostedEntity($entity);

        $errors = $this->getValidator()->validate($entity);
        if ($errors->count() > 0) {
            return new JsonResponse($this->parseConstraintViolations($errors), Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->createEntity($entity);

        $response = new JsonResponse(null, Response::HTTP_CREATED);

        $json = $this->getSerializer()->serialize(
            $entity,
            Defaults::SERIALIZE_FORMAT,
            [
                RestNormalizer::DDR_REST_INCLUDES => $this->parseIncludes($request),
                RestNormalizer::DDR_REST_DEPTH => 0,
                RestNormalizer::DDR_REST_PATH => ''
            ]
        );
        $response->setJson($json);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityByIdAction(Request $request, $id)
    {
        $entity = $this->getEntityById($id);
        $this->assertMethodGranted(CrudOperation::READ, $entity);

        $response = new JsonResponse();
        $json = $this->getSerializer()->serialize(
            $entity,
            Defaults::SERIALIZE_FORMAT,
            [
                RestNormalizer::DDR_REST_INCLUDES => $this->parseIncludes($request),
                RestNormalizer::DDR_REST_DEPTH => 0,
                RestNormalizer::DDR_REST_PATH => ''
            ]
        );
        $response->setJson($json);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function insertEntityAction(Request $request, $id)
    {
        $entity = $this->getEntityById($id);
        $this->assertMethodGranted(CrudOperation::UPDATE, $entity);

        $entity = $this->serializer->deserialize(
            $request->getContent(),
            $this->getEntityClass(),
            Defaults::SERIALIZE_FORMAT,
            [RestDenormalizer::DDR_REST_METHOD => CrudOperation::UPDATE, RestDenormalizer::DDR_REST_ENTITY => $entity]
        );
        $entity = $this->postProcessPuttedEntity($entity);

        $errors = $this->getValidator()->validate($entity);
        if ($errors->count() > 0) {
            return new JsonResponse($this->parseConstraintViolations($errors), Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->updateEntity($entity);

        $response = new JsonResponse();

        $json = $this->getSerializer()->serialize(
            $entity,
            Defaults::SERIALIZE_FORMAT,
            [
                RestNormalizer::DDR_REST_INCLUDES => $this->parseIncludes($request),
                RestNormalizer::DDR_REST_DEPTH => 0,
                RestNormalizer::DDR_REST_PATH => ''
            ]
        );
        $response->setJson($json);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEntityAction(Request $request, $id)
    {
        $entity = $this->getEntityById($id);
        $this->assertMethodGranted(CrudOperation::DELETE, $entity);
        $this->deleteEntity($entity);

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

    protected function assertMethodGranted(CrudOperation $method, $entity = null)
    {
        $operation = $this->getClassMetadata()->getOperation($method);
        if ($operation !== null) {
            if (null !== $operation->granted) {
                $this->denyAccessUnlessGranted($operation->granted);
            }

            if (null !== $operation->grantedExpression) {
                $this->denyAccessUnlessGranted(new Expression($operation->grantedExpression));
            }
        }
    }

    protected function getClassMetadata(): ?ClassMetadata
    {
        return $this->getMetadataFactory()->getMetadataForClass($this->getEntityClass());
    }

    protected function resolveSubject($entity, $propertyPath)
    {
        if ('this' === $propertyPath) {
            return $entity;
        }
        return $this->getPropertyAccessor()->getValue($entity, $propertyPath);
    }

    protected function parseIncludes(Request $request)
    {
        $requestContent = $request->toArray();
        if (!isset($requestContent['associations'])) {
            $includes = [];
        } else {
            $includes = $requestContent['associations'];
        }

        return $includes;
    }

    protected function denyAccessUnlessGranted($attribute, $object = null, $message = 'Access Denied.')
    {
        $authorizationChecker = $this->getAuthorizationChecker();
        if (null === $authorizationChecker) {
            throw new AccessDeniedException('No authorization checker configured');
        }

        if (!$authorizationChecker->isGranted($attribute, $object)) {
            throw new AccessDeniedException($message);
        }
    }

    protected function parseConstraintViolations(ConstraintViolationListInterface $errors): array
    {
        $data = [];
        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $data[] = [
                'propertyPath' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
                'value' => $error->getInvalidValue()
            ];
        }

        return $data;
    }

    protected function addPaginationHeaders(Response $response, int $page, int $perPage, int $total)
    {
        $response->headers->add(
            [
                'x-pagination-current-page' => $page,
                'x-pagination-per-page' => $perPage,
                'x-pagination-total' => $total,
                'x-pagination-total-pages' => (int)(($total - 1) / $perPage + 1)
            ]
        );
    }

    protected function getValidator()
    {
        return $this->validator;
    }

    protected function getRequestStack()
    {
        return $this->requestStack;
    }

    protected function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    protected function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }

    protected function getAuthorizationChecker(): ?AuthorizationCheckerInterface
    {
        return $this->authorizationChecker;
    }

    protected function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param int $page
     * @param int $perPage
     *
     * @throws InvalidFilterException
     *
     * @return Paginator|array
     */
    abstract protected function searchEntities(Request $request);

    /**
     * @param int|string $id
     *
     * @return object
     *
     * @throws NotFoundHttpException Thrown if entity with the given id could not be found.
     */
    abstract protected function getEntityById($id);

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
    abstract protected function deleteEntity($entity);

    /**
     * @param object $entity
     * @param string $subresource
     * @param int $page
     * @param int $perPage
     *
     * @return Paginator|array
     */
    abstract protected function listSubresource($entity, string $subresource, int $page = 1, int $perPage = 50);

    /**
     * @param object $parent
     * @param string $subresource
     *
     * @return object
     */
    abstract protected function buildAssociation($parent, string $subresource, $entity);

    /**
     * @param object $associatedEntity
     *
     * @return object
     */
    abstract protected function createAssociation($associatedEntity);

    /**
     * @param object $parent
     * @param string $subresource
     * @param int|string $subId
     *
     * @return object
     */
    abstract protected function addAssociation($parent, string $subresource, $subId);

    /**
     * @param object $parent
     * @param string $subresource
     * @param int|string|null $subId
     *
     * @return mixed
     */
    abstract protected function removeAssociation($parent, string $subresource, $subId = null);
}

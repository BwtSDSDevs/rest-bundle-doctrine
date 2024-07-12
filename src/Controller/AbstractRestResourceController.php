<?php

namespace SdsDev\RestBundleDoctrine\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use SdsDev\RestBundleDoctrine\Metadata\Common\CrudOperation;
use SdsDev\RestBundleDoctrine\Defaults\Defaults;
use SdsDev\RestBundleDoctrine\Exceptions\InvalidFilterException;
use SdsDev\RestBundleDoctrine\Metadata\ClassMetadata;
use SdsDev\RestBundleDoctrine\Metadata\RestMetadataFactory;
use SdsDev\RestBundleDoctrine\Serializer\RestDenormalizer;
use SdsDev\RestBundleDoctrine\Serializer\RestNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRestResourceController implements RestResourceControllerInterface
{
    private ValidatorInterface $validator;

    private RequestStack $requestStack;

    private RestMetadataFactory $metadataFactory;

    private SerializerInterface $serializer;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        RestMetadataFactory $metadataFactory,
        SerializerInterface $serializer
    ) {
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->metadataFactory = $metadataFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function searchEntityAction(Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 50);

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
    public function updateEntityAction(Request $request, $id): JsonResponse
    {
        $entity = $this->getEntityById($id);

        $entity = $this->serializer->deserialize(
            $request->getContent(),
            $this->getEntityClass(),
            Defaults::SERIALIZE_FORMAT,
            [RestDenormalizer::DDR_REST_METHOD => CrudOperation::UPDATE, RestDenormalizer::DDR_REST_ENTITY => $entity]
        );
        $entity = $this->postProcessPostedEntity($entity);

        $errors = $this->getValidator()->validate($entity);
        if ($errors->count() > 0) {
            return new JsonResponse($this->parseConstraintViolations($errors), Response::HTTP_BAD_REQUEST);
        }

        $this->updateEntity($entity);

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityByIdAction(Request $request, $id): JsonResponse
    {
        $entity = $this->getEntityById($id);

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
    public function insertEntityAction(Request $request): JsonResponse
    {
        $entity = $this->serializer->deserialize(
            $request->getContent(),
            $this->getEntityClass(),
            Defaults::SERIALIZE_FORMAT,
            [RestDenormalizer::DDR_REST_METHOD => CrudOperation::CREATE, RestDenormalizer::DDR_REST_ENTITY => null]
        );

        $errors = $this->getValidator()->validate($entity);
        if ($errors->count() > 0) {
            return new JsonResponse($this->parseConstraintViolations($errors), Response::HTTP_BAD_REQUEST);
        }

        $this->insertEntity($entity);

        return new JsonResponse([], Response::HTTP_CREATED);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEntityAction(Request $request, $id): JsonResponse
    {
        $entity = $this->getEntityById($id);
        $this->deleteEntity($entity);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function postProcessPostedEntity(object $entity): object
    {
        return $entity;
    }

    protected function getEntityClass()
    {
        return $this->getCurrentRequest()->attributes->get('_entityClass');
    }

    protected function getCurrentRequest(): ?Request
    {
        return $this->getRequestStack()->getCurrentRequest();
    }

    protected function getClassMetadata(): ?ClassMetadata
    {
        return $this->getMetadataFactory()->getMetadataForClass($this->getEntityClass());
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

    protected function getSerializer(): SerializerInterface
    {
        return $this->serializer;
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
     * @param int|string $id
     *
     * @return object
     *
     * @throws NotFoundHttpException Thrown if entity with the given id could not be found.
     */
    abstract protected function checkEntityWithIdExists($id);

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

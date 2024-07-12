<?php

namespace SdsDev\RestBundleDoctrine\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SdsDev\RestBundleDoctrine\Exceptions\InvalidFilterException;
use SdsDev\RestBundleDoctrine\Metadata\RestMetadataFactory;
use SdsDev\RestBundleDoctrine\Service\QueryMapperService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DoctrineRestResourceController extends AbstractRestResourceController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        RestMetadataFactory $metadataFactory,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        private readonly QueryMapperService $queryMapperService,
        private readonly string $projectBasePath
    ) {
        parent::__construct(
            $validator,
            $requestStack,
            $metadataFactory,
            $serializer,
            $projectBasePath
        );
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @throws QueryException
     * @throws InvalidFilterException
     */
    protected function searchEntities(Request $request): Paginator
    {
        $body = $request->toArray();

        $page = 1;
        $limit = 50;

        if(isset($body['page']))
            $page = $body['page'];

        if(isset($body['limit']))
            $limit = $body['limit'];


        $queryBuilder = $this->createFindAllQueryBuilder();
        $queryBuilder->setFirstResult(($page - 1) * $limit);
        $queryBuilder->setMaxResults($limit);

        $criteria = Criteria::create();

        if(isset($body['filter'])){
            $this->queryMapperService->validateFilters($body['filter']);
            $joins = $this->queryMapperService->getJoinsForFilter($body['filter']);
            /** @var string $join */
            foreach ($joins as $join => $selectTable){
                $queryBuilder->innerJoin('entity.'.$join, $join);
                if($selectTable)
                    $queryBuilder->addSelect($join);
            }
            $criteria = $this->queryMapperService->applyFiltersToCriteria($body['filter'], $criteria);
        }

        if(isset($body['sort'])){
            $this->queryMapperService->validateSorting($body['sort']);
            $criteria = $this->queryMapperService->applySortingToCriteria($body['sort'], $criteria);
        }

        $queryBuilder->addCriteria($criteria);

        return new Paginator($queryBuilder);
    }

    protected function createFindAllQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('entity')
            ->from($this->getEntityClass(), 'entity');

        return $queryBuilder;
    }

    protected function getEntityById($id)
    {
        $entity = $this->entityManager->find($this->getEntityClass(), $id);
        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }
    protected function checkEntityWithIdExists($id): bool
    {
        $entity = $this->entityManager->find($this->getEntityClass(), $id);
        if (null === $entity) {
            return false;
        }

        return true;
    }

    protected function createEntity($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    protected function updateEntity($entity)
    {
        $this->entityManager->flush();

        return $entity;
    }

    protected function insertEntity($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    protected function deleteEntity($entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    protected function listSubresource($entity, string $subresource, int $page = 1, int $perPage = 50)
    {
        $classMetadata = $this->getEntityManager()->getClassMetadata($this->getEntityClass());
        $association = $classMetadata->associationMappings[$subresource];
        $targetClass = $classMetadata->getAssociationTargetClass($subresource);
        $inverseFieldName = $this->getInverseFieldName($subresource, $classMetadata);

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('association');
        $queryBuilder->from($targetClass, 'association');
        $queryBuilder->join('association.' . $inverseFieldName, 'entity');
        $queryBuilder->where('entity = :entity');

        if (array_key_exists('orderBy', $association)) {
            $orderBy = $association['orderBy'];
            foreach ($orderBy as $fieldName => $order) {
                $queryBuilder->addOrderBy('association.' . $fieldName, $order);
            }
        }

        $queryBuilder->setParameter('entity', $entity);

        $queryBuilder->setFirstResult(($page - 1) * $perPage);
        $queryBuilder->setMaxResults($perPage);

        return new Paginator($queryBuilder);
    }

    protected function buildAssociation($parent, string $subresource, $entity)
    {
        $classMetadata = $this->getEntityManager()->getClassMetadata($this->getEntityClass());

        $inverseFieldName = $this->getInverseFieldName($subresource, $classMetadata);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyAccessor->setValue($entity, $inverseFieldName, $parent);

        return $entity;
    }

    protected function createAssociation($associatedEntity)
    {
        $this->getEntityManager()->persist($associatedEntity);
        $this->getEntityManager()->flush();

        return $associatedEntity;
    }

    protected function addAssociation($parent, string $subresource, $subId)
    {
        $classMetadata = $this->getEntityManager()->getClassMetadata($this->getEntityClass());
        $collection = $classMetadata->isCollectionValuedAssociation($subresource);
        $targetClass = $classMetadata->getAssociationTargetClass($subresource);
        $inverse = $classMetadata->isAssociationInverseSide($subresource);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $reference = $this->getEntityManager()->getReference($targetClass, $subId);

        if (!$inverse) {
            if ($collection) {
                /** @var Collection $collection */
                $collection = $propertyAccessor->getValue($parent, $subresource);
                $collection->add($reference);
            } else {
                $propertyAccessor->setValue($parent, $subresource, $reference);
            }
            $this->getEntityManager()->flush($parent);
        } else {
            $inverseClassMetadata = $this->getEntityManager()->getClassMetadata($targetClass);
            $association = $classMetadata->getAssociationMapping($subresource);
            $inverseFieldName = $association['mappedBy'];
            $inverseCollection = $inverseClassMetadata->isCollectionValuedAssociation($inverseFieldName);
            if ($inverseCollection) {
                /** @var Collection $collection */
                $collection = $propertyAccessor->getValue($reference, $inverseFieldName);
                $collection->add($parent);
            } else {
                $propertyAccessor->setValue($reference, $inverseFieldName, $parent);
            }
            $this->getEntityManager()->flush($reference);
        }
    }

    protected function removeAssociation($parent, string $subresource, $subId = null)
    {
        $classMetadata = $this->getEntityManager()->getClassMetadata($this->getEntityClass());
        $collection = $classMetadata->isCollectionValuedAssociation($subresource);
        $targetClass = $classMetadata->getAssociationTargetClass($subresource);
        $inverse = $classMetadata->isAssociationInverseSide($subresource);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($inverse) {
            $reference = $this->getEntityManager()->getReference($targetClass, $subId);
            $inverseClassMetadata = $this->getEntityManager()->getClassMetadata($targetClass);
            $association = $classMetadata->getAssociationMapping($subresource);
            $inverseFieldName = $association['mappedBy'];
            $inverseCollection = $inverseClassMetadata->isCollectionValuedAssociation($inverseFieldName);
            if ($inverseCollection) {
                /** @var Collection $collection */
                $collection = $propertyAccessor->getValue($reference, $inverseFieldName);
                $collection->removeElement($parent);
            } else {
                $propertyAccessor->setValue($reference, $inverseFieldName, null);
            }
            $this->getEntityManager()->flush($reference);
        } else {
            if ($collection) {
                $reference = $this->getEntityManager()->getReference($targetClass, $subId);
                /** @var Collection $collection */
                $collection = $propertyAccessor->getValue($parent, $subresource);
                $collection->removeElement($reference);
            } else {
                $propertyAccessor->setValue($parent, $subresource, null);
            }
            $this->getEntityManager()->flush($parent);
        }
    }

    protected function getInverseFieldName(string $fieldName, ClassMetadata $classMetadata): string
    {
        $association = $classMetadata->getAssociationMapping($fieldName);
        if ($classMetadata->isAssociationInverseSide($fieldName)) {
            return $association['mappedBy'];
        } else {
            return $association['inversedBy'];
        }
    }
}

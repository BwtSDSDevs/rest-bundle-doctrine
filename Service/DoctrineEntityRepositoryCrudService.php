<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DoctrineEntityRepositoryCrudService extends EntityRepository implements CrudServiceInterface
{
    public function __construct($entityManager, $entityClass)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($entityClass));
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        if ($this->isUuid($id)) {
            return $this->findOneBy(['uuid' => $id]);
        }

        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function listPaginated(int $page, int $perPage = 50): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('entity');
        $queryBuilder->setFirstResult(($page - 1) * $perPage);
        $queryBuilder->setMaxResults($perPage);

        return new Paginator($queryBuilder);
    }

    protected function isUuid($id)
    {
        return 1 === preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function create($entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function update($entity)
    {
        $this->getEntityManager()->flush($entity);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function listAssociationPaginated($entity, string $fieldName, int $page = 1, $perPage = 50)
    {
        $classMetadata = $this->getEntityManager()->getClassMetadata(get_class($entity));
        $association = $classMetadata->getAssociationMapping($fieldName);
        $targetClass = $classMetadata->getAssociationTargetClass($fieldName);

        $inverseFieldName = null;
        if ($classMetadata->isAssociationInverseSide($fieldName)) {
            $inverseFieldName = $association['mappedBy'];
        } else {
            $inverseFieldName = $association['inversedBy'];
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('association');
        $queryBuilder->from($targetClass, 'association');
        $queryBuilder->join('association.' . $inverseFieldName, 'entity');
        $queryBuilder->where('entity = :entity');
        $queryBuilder->setParameter('entity', $entity);

        $queryBuilder->setFirstResult(($page - 1) * $perPage);
        $queryBuilder->setMaxResults($perPage);

        $queryBuilder->getQuery();

        return new Paginator($queryBuilder);
    }
}

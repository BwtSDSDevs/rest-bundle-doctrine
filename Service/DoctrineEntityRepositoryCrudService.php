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
}

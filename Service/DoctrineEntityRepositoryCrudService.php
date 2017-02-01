<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DoctrineEntityRepositoryCrudService implements CrudServiceInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        if ($this->isUuid($id)) {
            return $this->repository->findOneBy(['uuid' => $id]);
        }

        return $this->repository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function listPaginated(int $page, int $perPage = 50): Paginator
    {
        $queryBuilder = $this->repository->createQueryBuilder('entity');
        $queryBuilder->setFirstResult(($page - 1) * $perPage);
        $queryBuilder->setMaxResults($perPage);

        return new Paginator($queryBuilder);
    }

    protected function isUuid($id)
    {
        return 1 === preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $id);
    }
}

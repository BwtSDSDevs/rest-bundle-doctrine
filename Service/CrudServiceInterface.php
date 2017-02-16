<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;

interface CrudServiceInterface
{
    /**
     * @param string|int $id
     *
     * @return object|null
     */
    public function findById($id);

    public function listPaginated(int $page, int $perPage = 50): Paginator;

    /**
     * @param object $entity
     *
     * @return object
     */
    public function create($entity);
}

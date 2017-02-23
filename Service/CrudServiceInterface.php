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

    /**
     * @param object $entity
     *
     * @return object
     */
    public function update($entity);

    /**
     * @param object $entity
     *
     * @return object mixed
     */
    public function remove($entity);

    /**
     * @param object $entity
     * @param string $relation
     * @param int    $page
     * @param int    $perPage
     */
    public function listAssociationPaginated($entity, string $relation, int $page = 1, $perPage = 50);

    /**
     * @param object     $entity
     * @param string     $fieldName
     * @param string|int $id
     */
    public function addToCollection($entity, string $fieldName, $id);

    /**
     * @param object     $entity
     * @param string     $fieldName
     * @param string|int $id
     */
    public function removeFromCollection($entity, string $fieldName, $id);
}

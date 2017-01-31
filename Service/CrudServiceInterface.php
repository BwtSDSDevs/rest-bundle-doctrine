<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;

interface CrudServiceInterface
{
    public function listPaginated(int $page, int $perPage = 50): Paginator;
}

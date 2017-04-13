<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface RestRequestParserInterface
{
    /**
     * @param Request     $request
     * @param string      $entityClass
     * @param object|null $entity
     *
     * @return object
     */
    function parseEntity(Request $request, $entityClass, $entity = null);

    /**
     * @param Request $request
     *
     * @return array
     */
    function getRequestContent(Request $request);
}

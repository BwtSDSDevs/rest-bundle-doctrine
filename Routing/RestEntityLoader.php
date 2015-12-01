<?php

namespace Dontdrinkandroot\RestBundle\Routing;

use Dontdrinkandroot\UtilsBundle\Controller\EntityControllerInterface;
use Dontdrinkandroot\UtilsBundle\Routing\EntityLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RestEntityLoader extends EntityLoader
{

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'ddr_entity_rest';
    }

    /**
     * {@inheritdoc}
     */
    protected function createRouteCollection(EntityControllerInterface $controller, $resource)
    {
        $routePrefix = $controller->getRoutePrefix();
        $pathPrefix = $controller->getPathPrefix();

        $routes = new RouteCollection();

        /* Edit */
        $route = new Route($pathPrefix . '{id}', ['_controller' => $resource . ':edit']);
        $route->setMethods(['PUT']);
        $routes->add($routePrefix . '.edit', $route);

        /* Delete */
        $route = new Route($pathPrefix . '{id}', ['_controller' => $resource . ':delete']);
        $route->setMethods(['DELETE']);
        $routes->add($routePrefix . '.delete', $route);

        /* Detail */
        new Route($pathPrefix . '{id}', ['_controller' => $resource . ':detail']);
        $route->setMethods(['GET']);
        $routes->add($routePrefix . '.detail', $route);

        /* Create */
        $route = new Route($pathPrefix, ['_controller' => $resource . ':edit']);
        $route->setMethods(['POST']);
        $routes->add($routePrefix . '.create', $route);

        /* List */
        $route = new Route($pathPrefix, ['_controller' => $resource . ':list']);
        $route->setMethods(['GET']);
        $routes->add($routePrefix . '.list', $route);

        return $routes;
    }
}

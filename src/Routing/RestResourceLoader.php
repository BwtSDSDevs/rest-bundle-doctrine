<?php

namespace SdsDev\RestBundleDoctrine\Routing;

use Doctrine\ORM\EntityManagerInterface;
use SdsDev\RestBundleDoctrine\Controller\DoctrineRestResourceController;
use SdsDev\RestBundleDoctrine\Defaults\Defaults;
use SdsDev\RestBundleDoctrine\Metadata\ClassMetadata;
use Exception;
use Metadata\MetadataFactoryInterface;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RestResourceLoader extends Loader
{
    const SEARCH_ACTION = ':searchEntityAction';
    const GET_ACTION    = ':getEntityByIdAction';
    const INSERT_ACTION = ':insertEntityAction';
    const UPDATE_ACTION = ':updateEntityAction';
    const DELETE_ACTION = ':deleteEntityAction';

    const ROUTE_PREFIX = 'api/doctrine/';


    public function __construct(
        private readonly MetadataFactoryInterface $metadataFactory,
        private readonly EntityManagerInterface   $entityManager,
    ) {
        parent::__construct();
    }

    private bool $isLoaded = false;

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): RouteCollection
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $entityMetaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $routes = new RouteCollection();

        /** @var \Doctrine\Persistence\Mapping\ClassMetadata $entity */
        foreach ($entityMetaData as $entity) {
            $class = $entity->getName();
            if (false === $class) {
                throw new Exception(sprintf('Couldn\'t find class for %s', $class));
            }
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass($class);

            $namePrefix = $classMetadata->getNamePrefix();
            $pathPrefix = $classMetadata->getPathPrefix();
            $controller = $this->getController($classMetadata);

            $defaults = [
                '_entityClass' => $class,
                '_format'      => 'json'
            ];

            $searchRoute = new Route(self::ROUTE_PREFIX . 'search/' . $pathPrefix);
            $searchRoute->setMethods(Request::METHOD_POST);
            $searchRoute->setDefaults(array_merge($defaults, [ '_controller' => $controller . self::SEARCH_ACTION]));
            $routes->add($namePrefix . '.search', $searchRoute);

            $getRoute = new Route(self::ROUTE_PREFIX . 'get/' . $pathPrefix . '/{id}');
            $getRoute->setMethods(Request::METHOD_GET);
            $getRoute->setDefaults(array_merge($defaults,['_controller' => $controller . self::GET_ACTION]));
            $routes->add($namePrefix . '.get', $getRoute);

            $updateRoute = new Route(self::ROUTE_PREFIX . 'update/' . $pathPrefix. '/{id}');
            $updateRoute->setMethods([Request::METHOD_PUT, Request::METHOD_PATCH]);
            $updateRoute->setDefaults(array_merge($defaults,['_controller' => $controller . self::UPDATE_ACTION]));
            $routes->add($namePrefix . '.update', $updateRoute);

            $insertRoute = new Route(self::ROUTE_PREFIX . 'insert/' . $pathPrefix);
            $insertRoute->setMethods(Request::METHOD_POST);
            $insertRoute->setDefaults(array_merge($defaults,['_controller' => $controller . self::INSERT_ACTION]));
            $routes->add($namePrefix . '.insert', $insertRoute);

            $deleteRoute = new Route(self::ROUTE_PREFIX .  'delete/' . $pathPrefix . '/{id}');
            $deleteRoute->setMethods(Request::METHOD_DELETE);
            $deleteRoute->setDefaults(array_merge($defaults,['_controller' => $controller . self::DELETE_ACTION]));
            $routes->add($namePrefix . '.delete', $deleteRoute);
        }

        $this->isLoaded = true;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, ?string $type = null): bool
    {
        return Defaults::SERIALIZE_FORMAT === $type;
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return string
     */
    protected function getController(ClassMetadata $classMetadata): string
    {
        $controller = DoctrineRestResourceController::class;
        if (null !== $classMetadata->getController()) {
            $controller = $classMetadata->getController();
        }

        if (strpos($controller, ':') !== false) {
            throw new RuntimeException(sprintf('Short controller notation is not permitted for "%s"', $controller));
        }

        if (strpos($controller, '\\') !== false) {
            $controller .= ':';
        }

        return $controller;
    }
}

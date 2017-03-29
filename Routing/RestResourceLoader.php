<?php

namespace Dontdrinkandroot\RestBundle\Routing;

use Dontdrinkandroot\RestBundle\Controller\ContainerAwareRestResourceController;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class RestResourceLoader extends Loader
{
    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var bool
     */
    private $securityEnabled;

    public function __construct(
        FileLocatorInterface $fileLocator,
        MetadataFactoryInterface $metadataFactory,
        bool $securityEnabled
    ) {
        $this->fileLocator = $fileLocator;
        $this->metadataFactory = $metadataFactory;
        $this->securityEnabled = $securityEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $locatedResource = $this->fileLocator->locate($resource);
        $files = [];
        if (is_dir($locatedResource)) {
            $finder = new Finder();
            foreach ($finder->in($locatedResource)->name('*.php')->files() as $file) {
                /** @var SplFileInfo $file */
                $files[] = $file->getRealPath();
            }
        } else {
            $files[] = $locatedResource;
        }

        $routes = new RouteCollection();

        if ($this->securityEnabled) {
            $this->loadAccessTokenController($routes);
        }

        foreach ($files as $file) {
            $class = $this->findClass($file);
            if (false === $class) {
                throw new \Exception(sprintf('Couldn\'t find class for %s', $file));
            }
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass($class);
            if ($classMetadata->isRestResource()) {

                $namePrefix = $classMetadata->getNamePrefix();
                $pathPrefix = $classMetadata->getPathPrefix();
                $controller = $this->getController($classMetadata);

                $defaults = [
                    '_entityClass' => $class,
                    '_format'      => 'json'
                ];

                if (null !== $classMetadata->getService()) {
                    $defaults['_service'] = $classMetadata->getService();
                }

                if (null !== $method = $classMetadata->getMethod(Method::LIST)) {
                    $listRoute = new Route($pathPrefix);
                    $listRoute->setMethods(Request::METHOD_GET);
                    $listRoute->setDefaults(
                        array_merge(
                            $defaults,
                            [
                                '_controller'      => $controller . ':listAction',
                                '_defaultincludes' => $method->defaultIncludes
                            ]
                        )
                    );
                    $routes->add($namePrefix . '.list', $listRoute);
                }

                if (null !== $method = $classMetadata->getMethod(Method::POST)) {
                    $postRoute = new Route($pathPrefix);
                    $postRoute->setMethods(Request::METHOD_POST);
                    $postRoute->setDefaults(
                        array_merge(
                            $defaults,
                            [
                                '_controller'      => $controller . ':postAction',
                                '_defaultincludes' => $method->defaultIncludes
                            ]
                        )
                    );
                    $routes->add($namePrefix . '.post', $postRoute);
                }

                if (null !== $method = $classMetadata->getMethod(Method::GET)) {
                    $getRoute = new Route($pathPrefix . '/{id}');
                    $getRoute->setMethods(Request::METHOD_GET);
                    $getRoute->setDefaults(
                        array_merge(
                            $defaults,
                            [
                                '_controller'      => $controller . ':getAction',
                                '_defaultincludes' => $method->defaultIncludes
                            ]
                        )
                    );
                    $routes->add($namePrefix . '.get', $getRoute);
                }

                if (null !== $method = $classMetadata->getMethod(Method::PUT)) {
                    $putRoute = new Route($pathPrefix . '/{id}');
                    $putRoute->setMethods([Request::METHOD_PUT, Request::METHOD_PATCH]);
                    $putRoute->setDefaults(
                        array_merge(
                            $defaults,
                            [
                                '_controller'      => $controller . ':putAction',
                                '_defaultincludes' => $method->defaultIncludes
                            ]
                        )
                    );
                    $routes->add($namePrefix . '.put', $putRoute);
                }

                if (null !== $method = $classMetadata->getMethod(Method::DELETE)) {
                    $deleteRoute = new Route($pathPrefix . '/{id}');
                    $deleteRoute->setMethods(Request::METHOD_DELETE);
                    $deleteRoute->setDefaults(
                        array_merge(
                            $defaults,
                            [
                                '_controller'      => $controller . ':deleteAction',
                                '_defaultincludes' => $method->defaultIncludes
                            ]
                        )
                    );
                    $routes->add($namePrefix . '.delete', $deleteRoute);
                }

                /** @var PropertyMetadata $propertyMetadata */
                foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
                    if ($propertyMetadata->isSubResource()) {

                        $subResourcePath = strtolower($propertyMetadata->name);
                        if (null !== $propertyMetadata->getSubResourcePath()) {
                            $subResourcePath = $propertyMetadata->getSubResourcePath();
                        }

                        if (null !== $method = $propertyMetadata->getMethod(Method::LIST)) {
                            $subResourceFullPath = $pathPrefix . '/{id}/' . $subResourcePath;
                            $subResourceRoute = new Route($subResourceFullPath);
                            $subResourceRoute->setMethods(Request::METHOD_GET);
                            $subResourceRoute->setDefaults(
                                array_merge(
                                    $defaults,
                                    [
                                        '_controller'      => $controller . ':listSubresourceAction',
                                        'subresource'      => $propertyMetadata->name,
                                        '_defaultincludes' => $method->defaultIncludes
                                    ]
                                )
                            );
                            $routes->add($namePrefix . '.' . $propertyMetadata->name . '.list', $subResourceRoute);
                        }

                        if (null !== $method = $propertyMetadata->getMethod(Method::POST)) {
                            $subResourceFullPath = $pathPrefix . '/{id}/' . $subResourcePath;
                            $subResourceRoute = new Route($subResourceFullPath);
                            $subResourceRoute->setMethods(Request::METHOD_POST);
                            $subResourceRoute->setDefaults(
                                array_merge(
                                    $defaults,
                                    [
                                        '_controller'      => $controller . ':postSubresourceAction',
                                        'subresource'      => $propertyMetadata->name,
                                        '_defaultincludes' => $method->defaultIncludes
                                    ]
                                )
                            );
                            $routes->add($namePrefix . '.' . $propertyMetadata->name . '.post', $subResourceRoute);
                        }

                        if (null !== $method = $propertyMetadata->getMethod(Method::PUT)) {
                            $subResourceFullPath = $pathPrefix . '/{id}/' . $subResourcePath . '/{subId}';
                            $subResourceRoute = new Route($subResourceFullPath);
                            $subResourceRoute->setMethods(Request::METHOD_PUT);
                            $subResourceRoute->setDefaults(
                                array_merge(
                                    $defaults,
                                    [
                                        '_controller'      => $controller . ':putSubresourceAction',
                                        'subresource'      => $propertyMetadata->name,
                                        '_defaultincludes' => $method->defaultIncludes
                                    ]
                                )
                            );
                            $routes->add($namePrefix . '.' . $propertyMetadata->name . '.put', $subResourceRoute);
                        }

                        if (null !== $method = $propertyMetadata->getMethod(Method::DELETE)) {
                            if ($propertyMetadata->isCollection()) {
                                $subResourceFullPath = $pathPrefix . '/{id}/' . $subResourcePath . '/{subId}';
                            } else {
                                $subResourceFullPath = $pathPrefix . '/{id}/' . $subResourcePath;
                            }
                            $subResourceRoute = new Route($subResourceFullPath);
                            $subResourceRoute->setMethods(Request::METHOD_DELETE);
                            $subResourceRoute->setDefaults(
                                array_merge(
                                    $defaults,
                                    [
                                        '_controller' => $controller . ':deleteSubresourceAction',
                                        'subresource' => $propertyMetadata->name,
                                    ]
                                )
                            );
                            $routes->add($namePrefix . '.' . $propertyMetadata->name . '.delete', $subResourceRoute);
                        }
                    }
                }
            }
        }

        return $routes;
    }

    /**
     * Taken from {@see AnnotationFileLoader}
     * TODO: Evaluate if there is a library method or extract.
     *
     * @param $file
     *
     * @return bool|string
     */
    protected function findClass($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];

            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace . '\\' . $token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1]) && in_array($tokens[$i][0], array(T_NS_SEPARATOR, T_STRING))) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'ddr_rest' === $type;
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return string
     */
    protected function getController(ClassMetadata $classMetadata)
    {
        $controller = ContainerAwareRestResourceController::class;
        if (null !== $classMetadata->getController()) {
            $controller = $classMetadata->getController();
        }

        if (strpos($controller, ':') !== false) {
            throw new \RuntimeException(sprintf('Short controller notation is not permitted for "%s"', $controller));
        }

        if (strpos($controller, '\\') !== false) {
            $controller .= ':';
        }

        return $controller;
    }

    private function loadAccessTokenController(RouteCollection $routes)
    {
        $route = new Route('accesstokens');
        $route->setMethods('POST');
        $route->setDefault('_controller', 'ddr_rest.controller.access_token:createAction');
        $route->setDefault('_format', 'json');
        $routes->add('ddr_rest.accesstoken.create', $route);

        $route = new Route('accesstokens');
        $route->setMethods('GET');
        $route->setDefault('_controller', 'ddr_rest.controller.access_token:listAction');
        $route->setDefault('_format', 'json');
        $routes->add('ddr_rest.accesstoken.list', $route);

        $route = new Route('accesstokens/{token}');
        $route->setMethods('DELETE');
        $route->setDefault('_controller', 'ddr_rest.controller.access_token:deleteAction');
        $route->setDefault('_format', 'json');
        $routes->add('ddr_rest.accesstoken.delete', $route);
    }
}

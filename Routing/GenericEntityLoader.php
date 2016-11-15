<?php

namespace Dontdrinkandroot\RestBundle\Routing;

use Doctrine\Common\Util\Inflector;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class GenericEntityLoader extends Loader
{
    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    public function __construct(FileLocatorInterface $fileLocator, MetadataFactoryInterface $metadataFactory)
    {

        $this->fileLocator = $fileLocator;
        $this->metadataFactory = $metadataFactory;
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
        foreach ($files as $file) {
            $class = $this->findClass($file);
            if (false === $class) {
                throw new \Exception(sprintf('Couldn\'t find class for %s', $file));
            }
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass($class);
            if ($classMetadata->isRestResource()) {

                $namePrefix = $this->getNamePrefix($classMetadata);
                $pathPrefix = $this->getPathPrefix($classMetadata);
                $controller = $this->getController($classMetadata);

                $defaults = [
                    '_entityClass' => $class
                ];

                if (null !== $classMetadata->getService()) {
                    $defaults['_service'] = $classMetadata->getService();
                }

                $listRoute = new Route($pathPrefix . '/');
                $listRoute->setMethods(Request::METHOD_GET);
                $listRoute->setDefaults(array_merge($defaults, ['_controller' => $controller . ':list']));
                $routes->add($namePrefix . '.list', $listRoute);

                $postRoute = new Route($pathPrefix . '/');
                $postRoute->setMethods(Request::METHOD_POST);
                $postRoute->setDefaults(array_merge($defaults, ['_controller' => $controller . ':post']));
                $routes->add($namePrefix . '.post', $postRoute);

                $getRoute = new Route($pathPrefix . '/{id}');
                $getRoute->setMethods(Request::METHOD_GET);
                $getRoute->setDefaults(array_merge($defaults, ['_controller' => $controller . ':get']));
                $routes->add($namePrefix . '.get', $getRoute);

                $putRoute = new Route($pathPrefix . '/{id}');
                $putRoute->setMethods(Request::METHOD_PUT);
                $putRoute->setDefaults(array_merge($defaults, ['_controller' => $controller . ':put']));
                $routes->add($namePrefix . '.put', $putRoute);

                $deleteRoute = new Route($pathPrefix . '/{id}');
                $deleteRoute->setMethods(Request::METHOD_DELETE);
                $deleteRoute->setDefaults(array_merge($defaults, ['_controller' => $controller . ':delete']));
                $routes->add($namePrefix . '.delete', $deleteRoute);
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
        return 'ddr_rest_generic' === $type;
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return string
     */
    private function getNamePrefix(ClassMetadata $classMetadata)
    {
        if (null !== $classMetadata->getNamePrefix()) {
            return $classMetadata->getNamePrefix();
        }

        return Inflector::tableize($classMetadata->reflection->getShortName());
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return string
     */
    private function getPathPrefix(ClassMetadata $classMetadata)
    {
        if (null !== $classMetadata->getPathPrefix()) {
            return $classMetadata->getPathPrefix();
        }

        return Inflector::pluralize(strtolower($classMetadata->reflection->getShortName()));
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return string
     */
    protected function getController(ClassMetadata $classMetadata)
    {
        $controller = 'DdrRestBundle:GenericEntity';
        if (null !== $classMetadata->getController()) {
            $controller = $classMetadata->getController();
        }

        return $controller;
    }
}

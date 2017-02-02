<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

class ClassMetadata extends MergeableClassMetadata
{
    /**
     * @var bool
     */
    public $restResource;

    /**
     * @var string
     */
    public $namePrefix;

    /**
     * @var string
     */
    public $pathPrefix;

    /**
     * @var string
     */
    public $service;

    /**
     * @var string
     */
    public $controller;

    /**
     * @var Right|null
     */
    public $postRight;

    /**
     * @var Right|null
     */
    public $putRight;

    /**
     * @var Right|null
     */
    public $deleteRight;

    /**
     * @var Right|null
     */
    public $listRight;

    /**
     * @var Right|null
     */
    public $getRight;

    /**
     * @var string[]
     */
    public $methods = ['LIST', 'POST', 'GET', 'PUT', 'DELETE'];

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object)
    {
        parent::merge($object);
        /** @var ClassMetadata $object */
        $this->restResource = $object->restResource;
        $this->namePrefix = $object->namePrefix;
        $this->pathPrefix = $object->pathPrefix;
        $this->service = $object->service;
        $this->controller = $object->controller;
        $this->listRight = $object->listRight;
        $this->getRight = $object->getRight;
        $this->postRight = $object->postRight;
        $this->putRight = $object->putRight;
        $this->deleteRight = $object->deleteRight;
        $this->methods = $this->mergeMethods($this->methods, $object->methods);
    }

    /**
     * @param bool $restResource
     */
    public function setRestResource($restResource)
    {
        $this->restResource = $restResource;
    }

    /**
     * @return boolean
     */
    public function isRestResource()
    {
        return $this->restResource;
    }

    /**
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * @param string $namePrefix
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * @return string
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * @param string $pathPrefix
     */
    public function setPathPrefix(string $pathPrefix)
    {
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return Right|null
     */
    public function getDeleteRight()
    {
        return $this->deleteRight;
    }

    /**
     * @param Right|null $deleteRight
     */
    public function setDeleteRight(Right $deleteRight)
    {
        $this->deleteRight = $deleteRight;
    }

    /**
     * @return Right|null
     */
    public function getPostRight()
    {
        return $this->postRight;
    }

    /**
     * @param Right|null $postRight
     */
    public function setPostRight(Right $postRight)
    {
        $this->postRight = $postRight;
    }

    /**
     * @return Right|null
     */
    public function getPutRight()
    {
        return $this->putRight;
    }

    /**
     * @param Right|null $putRight
     */
    public function setPutRight(Right $putRight)
    {
        $this->putRight = $putRight;
    }

    /**
     * @return Right|null
     */
    public function getListRight()
    {
        return $this->listRight;
    }

    /**
     * @param Right|null $listRight
     */
    public function setListRight(Right $listRight)
    {
        $this->listRight = $listRight;
    }

    /**
     * @return Right|null
     */
    public function getGetRight()
    {
        return $this->getRight;
    }

    /**
     * @param Right|null $getRight
     */
    public function setGetRight(Right $getRight)
    {
        $this->getRight = $getRight;
    }

    /**
     * @return string[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param string[] $methods
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }

    private function mergeMethods($thisMethods, $otherMethods)
    {
        $mergedMethods = [];
        foreach ($thisMethods as $thisMethod) {
            if (in_array($thisMethod, $otherMethods)) {
                $mergedMethods[] = $thisMethod;
            }
        }

        return $mergedMethods;
    }
}

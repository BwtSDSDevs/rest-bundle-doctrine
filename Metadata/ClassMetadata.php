<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
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
     * @var Method[]|null
     */
    public $methods;

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object)
    {
        if (!$object instanceof MergeableClassMetadata) {
            throw new \InvalidArgumentException('$object must be an instance of MergeableClassMetadata.');
        }

        $this->name = $object->name;
        $this->reflection = $object->reflection;
        $this->methodMetadata = array_merge($this->methodMetadata, $object->methodMetadata);
        $this->propertyMetadata = $this->mergePropertyMetadata($object);
        $this->fileResources = array_merge($this->fileResources, $object->fileResources);

        if ($object->createdAt < $this->createdAt) {
            $this->createdAt = $object->createdAt;
        }

        /** @var ClassMetadata $object */
        $this->restResource = $object->restResource;
        $this->methods = $object->methods;
        $this->namePrefix = $object->namePrefix;
        $this->pathPrefix = $object->pathPrefix;
        $this->service = $object->service;
        $this->controller = $object->controller;
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
     * @return Method[]
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

    public function getPropertyMetadata(string $property): ?PropertyMetadata
    {
        if (array_key_exists($property, $this->propertyMetadata)) {
            return $this->propertyMetadata[$property];
        }

        return null;
    }

    /**
     * @param MergeableInterface $object
     *
     * @return array
     */
    protected function mergePropertyMetadata(MergeableInterface $object): array
    {
        /** @var ClassMetadata $object */
        /** @var PropertyMetadata[] $mergedMetadata */
        $mergedMetadata = $this->propertyMetadata;

        foreach ($object->propertyMetadata as $otherMetadata) {
            /** @var PropertyMetadata $otherMetadata */
            if (array_key_exists($otherMetadata->name, $mergedMetadata)) {
                $mergedMetadata[$otherMetadata->name] = $mergedMetadata[$otherMetadata->name]->merge($otherMetadata);
            } else {
                $mergedMetadata[$otherMetadata->name] = $otherMetadata;
            }
        }

        return $mergedMetadata;
    }

    public function getMethod(string $methodName): ?Method
    {
        if (null === $this->methods) {
            return null;
        }

        foreach ($this->methods as $method) {
            if ($methodName === $method->name) {
                return $method;
            }
        }

        return null;
    }
}

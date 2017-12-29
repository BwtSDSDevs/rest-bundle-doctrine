<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Doctrine\Common\Inflector\Inflector;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
    public $idField;

    /**
     * @var string
     */
    public $controller;

    /**
     * @var Method[]|null
     */
    public $methods;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->namePrefix = Inflector::tableize($this->reflection->getShortName());
        $this->pathPrefix = Inflector::pluralize(strtolower($this->reflection->getShortName()));
    }

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object)
    {
        assert($object instanceof ClassMetadata);

        $this->name = $object->name;
        $this->reflection = $object->reflection;
        $this->methodMetadata = array_merge($this->methodMetadata, $object->methodMetadata);
        $this->propertyMetadata = $this->mergePropertyMetadata($object);
        $this->fileResources = array_merge($this->fileResources, $object->fileResources);

        if ($object->createdAt < $this->createdAt) {
            $this->createdAt = $object->createdAt;
        }

        /** @var ClassMetadata $object */
        $this->restResource = $this->mergeField($this->restResource, $object->restResource);
        $this->idField = $this->mergeField($this->idField, $object->idField);
        $this->methods = $this->mergeField($this->methods, $object->methods);
        $this->namePrefix = $this->mergeField($this->namePrefix, $object->namePrefix);
        $this->pathPrefix = $this->mergeField($this->pathPrefix, $object->pathPrefix);
        $this->controller = $this->mergeField($this->controller, $object->controller);
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
     * @return Method[]|null
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param Method[]|null $methods
     */
    public function setMethods($methods)
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
        assert($object instanceof ClassMetadata);

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

    public function hasMethod($methodName)
    {
        return null !== $this->getMethod($methodName);
    }

    private function mergeField($existing, $toMerge)
    {
        if (null !== $toMerge) {
            return $toMerge;
        }

        return $existing;
    }

    public function getIdField(string $default = 'id'): string
    {
        if (null !== $this->idField) {
            return $this->idField;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                $this->name,
                $this->methodMetadata,
                $this->propertyMetadata,
                $this->fileResources,
                $this->createdAt,
                $this->restResource,
                $this->namePrefix,
                $this->pathPrefix,
                $this->idField,
                $this->controller,
                $this->methods
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->restResource,
            $this->namePrefix,
            $this->pathPrefix,
            $this->idField,
            $this->controller,
            $this->methods
            ) = unserialize($str);

        $this->reflection = new \ReflectionClass($this->name);
    }
}

<?php

namespace Niebvelungen\RestBundleDoctrine\Metadata;

use Doctrine\Inflector\InflectorFactory;
use Dontdrinkandroot\Common\CrudOperation;
use Niebvelungen\RestBundleDoctrine\Metadata\Attribute\Operation;
use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;
use ReflectionClass;

class ClassMetadata extends MergeableClassMetadata
{
    public ?bool $restResource = null;

    public ?string $namePrefix = null;

    public ?string $pathPrefix = null;

    public ?string $idField = null;

    public ?string $controller = null;

    /** @var array<string,CrudOperation>|null */
    public ?array $operations = null;

    public function __construct($name)
    {
        parent::__construct($name);

        $reflection = new ReflectionClass($name);
        $inflector = InflectorFactory::create()->build();
        $this->namePrefix = $inflector->tableize($reflection->getShortName());
        $this->pathPrefix = $inflector->pluralize(strtolower($reflection->getShortName()));
    }

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object): void
    {
        parent::merge($object);

        /** @var ClassMetadata $object */
        $this->restResource = $this->mergeField($this->restResource, $object->restResource);
        $this->idField = $this->mergeField($this->idField, $object->idField);
        $this->operations = $this->mergeField($this->operations, $object->operations);
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
     * @return Operation[]|null
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * @param array<string,Operation>|null $operations
     */
    public function setOperations($operations)
    {
        $this->operations = $operations;
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

    public function getOperation(CrudOperation $method): ?Operation
    {
        if (null === $this->operations) {
            return null;
        }

        foreach ($this->operations as $operation) {
            if ($method === $operation->method) {
                return $operation;
            }
        }

        return null;
    }

    public function hasOperation(CrudOperation $method)
    {
        return null !== $this->getOperation($method);
    }

    private function mergeField($existing, $toMerge)
    {
        return $toMerge ?? $existing;
    }

    public function getIdField(string $default = 'id'): string
    {
        return $this->idField ?? $default;
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
                $this->operations
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
            $this->operations
            ) = unserialize($str);
    }
}

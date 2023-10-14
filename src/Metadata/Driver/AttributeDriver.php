<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Driver;

use Dontdrinkandroot\RestBundle\Metadata\Attribute\Excluded;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Includable;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Postable;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Puttable;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\RootResource;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\SubResource;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Virtual;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\Driver\DriverInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class AttributeDriver implements DriverInterface
{
    private $doctrineDriver;

    public function __construct(DriverInterface $doctrineDriver)
    {
        $this->doctrineDriver = $doctrineDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(ReflectionClass $class): \Metadata\ClassMetadata
    {
        /** @var ClassMetadata $ddrRestClassMetadata */
        $ddrRestClassMetadata = $this->doctrineDriver->loadMetadataForClass($class);
        if (null === $ddrRestClassMetadata) {
            $ddrRestClassMetadata = new ClassMetadata($class->getName());
        }
        if (null !== ($rootResourceAttribute = $this->getRootResourceAttribute($class))) {
            $ddrRestClassMetadata->setRestResource(true);

            if (null !== $rootResourceAttribute->namePrefix) {
                $ddrRestClassMetadata->setNamePrefix($rootResourceAttribute->namePrefix);
            }

            if (null !== $rootResourceAttribute->pathPrefix) {
                $ddrRestClassMetadata->setPathPrefix($rootResourceAttribute->pathPrefix);
            }

            if (null !== $rootResourceAttribute->controller) {
                $ddrRestClassMetadata->setController($rootResourceAttribute->controller);
            }

            $ddrRestClassMetadata->idField = $rootResourceAttribute->idField;

            if (null !== $rootResourceAttribute->operations) {
                $operations = [];
                $operationAnnotations = $rootResourceAttribute->operations;
                foreach ($operationAnnotations as $operationAnnotation) {
                    $operations[$operationAnnotation->method->value] = $operationAnnotation;
                }
                $ddrRestClassMetadata->setOperations($operations);
            }

            if (null !== $rootResourceAttribute->operations) {
                $ddrRestClassMetadata->setOperations($rootResourceAttribute->operations);
            }
        }

        foreach ($class->getProperties() as $reflectionProperty) {
            $propertyMetadata = $ddrRestClassMetadata->getPropertyMetadata($reflectionProperty->getName());
            if (null === $propertyMetadata) {
                $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());
            }

            if (null !== ($puttable = $this->getSinglePropertyAttribute($reflectionProperty, Puttable::class))) {
                $propertyMetadata->setPuttable($puttable);
            }

            if (null !== ($postable = $this->getSinglePropertyAttribute($reflectionProperty, Postable::class))) {
                $propertyMetadata->setPostable($postable);
            }

            if (null !== ($includable = $this->getSinglePropertyAttribute($reflectionProperty, Includable::class))) {
                $this->parseIncludable($propertyMetadata, $includable);
            }

            if (null !== ($this->getSinglePropertyAttribute($reflectionProperty, Excluded::class))) {
                $propertyMetadata->setExcluded(true);
            }

            if (null !== ($subResourceAttribute = $this->getSinglePropertyAttribute(
                    $reflectionProperty,
                    SubResource::class
                ))) {
                $propertyMetadata->setSubResource(true);

                if (null !== $subResourceAttribute->path) {
                    $propertyMetadata->setSubResourcePath($subResourceAttribute->path);
                }

                if (null !== $subResourceAttribute->operations) {
                    $operations = [];
                    $operationAnnotations = $subResourceAttribute->operations;
                    foreach ($operationAnnotations as $operationAnnotation) {
                        $operations[$operationAnnotation->method->value] = $operationAnnotation;
                    }
                    $propertyMetadata->setOperations($operations);
                }
            }

            $ddrRestClassMetadata->addPropertyMetadata($propertyMetadata);
        }

        foreach ($class->getMethods() as $reflectionMethod) {
            if (null !== ($virtualAttribute = $this->getSingeMethodAttribute($reflectionMethod, Virtual::class))) {
                $name = $this->methodToPropertyName($reflectionMethod);

                $propertyMetadata = $ddrRestClassMetadata->getPropertyMetadata($name);
                if (null === $propertyMetadata) {
                    $propertyMetadata = new PropertyMetadata($class->getName(), $name);
                }
                $propertyMetadata->setVirtual(true);

                if (null !== ($includable = $this->getSingeMethodAttribute($reflectionMethod, Includable::class))) {
                    $this->parseIncludable($propertyMetadata, $includable);
                }

                $ddrRestClassMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $ddrRestClassMetadata;
    }

    private function methodToPropertyName(ReflectionMethod $reflectionMethod): string
    {
        $name = $reflectionMethod->getName();
        if (0 === strpos($name, 'get')) {
            return lcfirst(substr($name, 3));
        }

        if (0 === strpos($name, 'is')) {
            return lcfirst(substr($name, 2));
        }

        if (0 === strpos($name, 'has')) {
            return lcfirst(substr($name, 3));
        }

        return $name;
    }

    public function parseIncludable(PropertyMetadata $propertyMetadata, Includable $includableAnnotation): void
    {
        $paths = $includableAnnotation->paths;
        if (null === $paths) {
            $paths = [$propertyMetadata->name];
        }
        $propertyMetadata->setIncludable(true);
        $propertyMetadata->setIncludablePaths($paths);
    }

    public function getRootResourceAttribute(ReflectionClass $class): ?RootResource
    {
        return ($attr = $class->getAttributes(RootResource::class)) && count($attr) === 1
            ? $attr[0]->newInstance()
            : null;
    }

    /**
     * @template T
     * @param class-string<T> $name
     * @return T|null
     */
    public function getSingeClassAttribute(ReflectionClass $class, string $name): ?object
    {
        return ($attr = $class->getAttributes($name)) && count($attr) === 1
            ? $attr[0]->newInstance()
            : null;
    }

    /**
     * @template T
     * @param class-string<T> $name
     * @return T|null
     */
    public function getSinglePropertyAttribute(ReflectionProperty $property, string $name): ?object
    {
        return ($attr = $property->getAttributes($name)) && count($attr) === 1
            ? $attr[0]->newInstance()
            : null;
    }

    /**
     * @template T
     * @param class-string<T> $name
     * @return T|null
     */
    public function getSingeMethodAttribute(ReflectionMethod $method, string $name): ?object
    {
        return ($attr = $method->getAttributes($name)) && count($attr) === 1
            ? $attr[0]->newInstance()
            : null;
    }
}

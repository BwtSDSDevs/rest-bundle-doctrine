<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\Entity\EntityInterface;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use JMS\Serializer\SerializerInterface;
use Metadata\MetadataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class RestRequestParser
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(
        MetadataFactory $metadataFactory,
        SerializerInterface $serializer,
        PropertyAccessor $propertyAccessor
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->serializer = $serializer;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param Request              $request
     * @param string               $entityClass
     * @param EntityInterface|null $entity
     *
     * @return EntityInterface
     */
    public function parseEntity(Request $request, $entityClass, EntityInterface $entity = null)
    {
        $method = $request->getMethod();
        $content = $request->getContent();
        $format = $request->getRequestFormat();

        $parsedEntity = $this->serializer->deserialize(
            $content,
            $entityClass,
            $format
        );

        if (null === $entity) {
            $entity = new $entityClass;
        }

        $this->updateObject($entity, $method, $parsedEntity);

        return $entity;
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param mixed  $data
     */
    protected function updateObject(&$object, $method, $data)
    {
        $classMetadata = $this->metadataFactory->getMetadataForClass(get_class($object));
        /** @var PropertyMetadata $propertyMetadata */
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            $propertyName = $propertyMetadata->name;
            if ($this->isUpdateable($method, $propertyMetadata)) {
                $this->updateProperty($object, $method, $data, $propertyName);
            }
        }
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param mixed  $data
     * @param string $propertyName
     */
    protected function updateProperty(&$object, $method, $data, $propertyName)
    {
        $dataValue = $this->propertyAccessor->getValue($data, $propertyName);
        if (is_object($dataValue)) {
            $this->updatePropertyObject($object, $method, $propertyName, $dataValue);
        } else {
            $this->propertyAccessor->setValue($object, $propertyName, $dataValue);
        }
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param string $propertyName
     * @param object $value
     */
    protected function updatePropertyObject(&$object, $method, $propertyName, $value)
    {
        $objectValue = $this->propertyAccessor->getValue($object, $propertyName);
        if (null === $objectValue) {
            $dataValueClass = get_class($value);
            $objectValue = new $dataValueClass;
        }
        $this->updateObject($objectValue, $method, $value);
        $this->propertyAccessor->setValue($object, $propertyName, $objectValue);
    }

    /**
     * @param string           $method
     * @param PropertyMetadata $propertyMetadata
     *
     * @return bool
     */
    protected function isUpdateable($method, PropertyMetadata $propertyMetadata)
    {
        if (Request::METHOD_PUT === $method) {
            return $propertyMetadata->isPuttable();
        }
        if (Request:: METHOD_POST === $method) {
            return $propertyMetadata->isPostable();
        }

        return false;
    }
}

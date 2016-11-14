<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\Entity\EntityInterface;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use JMS\Serializer\SerializerInterface;
use Metadata\MetadataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

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

    public function __construct(MetadataFactory $metadataFactory, SerializerInterface $serializer)
    {

        $this->metadataFactory = $metadataFactory;
        $this->serializer = $serializer;
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
        $parsedEntity = $this->serializer->deserialize(
            $request->getContent(),
            $entityClass,
            $request->getRequestFormat()
        );

        if (null === $entity) {
            $entity = new $entityClass;
        }

        $classMetadata = $this->metadataFactory->getMetadataForClass($entityClass);
        $accessor = PropertyAccess::createPropertyAccessor();

        /** @var PropertyMetadata $propertyMetadata */
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            $propertyName = $propertyMetadata->name;
            if (Request::METHOD_POST === $method) {
                if ($propertyMetadata->isPostable()) {
                    $value = $accessor->getValue($parsedEntity, $propertyName);
                    $accessor->setValue($entity, $propertyName, $value);
                }
            }

            if (Request::METHOD_PUT === $method) {
                if ($propertyMetadata->isPuttable()) {
                    $value = $accessor->getValue($parsedEntity, $propertyName);
                    $accessor->setValue($entity, $propertyName, $value);
                }
            }
        }

        return $entity;
    }
}

<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(MetadataFactory $metadataFactory, SerializerInterface $serializer)
    {

        $this->metadataFactory = $metadataFactory;
        $this->serializer = $serializer;
    }

    public function parseEntity(Request $request, string $entityClass, EntityInterface $entity = null): EntityInterface
    {
        $post = null === $entity;

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
            if ($post) {
                if ($propertyMetadata->isPostable()) {
                    $value = $accessor->getValue($parsedEntity, $propertyMetadata->name);
                    $accessor->setValue($entity, $propertyMetadata->name, $value);
                }
            } else {
                if ($propertyMetadata->isPostable()) {
                    $value = $accessor->getValue($parsedEntity, $propertyMetadata->name);
                    $accessor->setValue($entity, $propertyMetadata->name, $value);
                }
            }
        }

        return $entity;
    }
}

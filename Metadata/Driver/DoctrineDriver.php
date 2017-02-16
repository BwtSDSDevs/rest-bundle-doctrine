<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Driver;

use Doctrine\ORM\EntityManagerInterface;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\Driver\DriverInterface;

class DoctrineDriver implements DriverInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $doctrineClassMetadata = $this->entityManager->getClassMetadata($class->getName());
        $ddrRestClassMetadata = new ClassMetadata($class->getName());

        foreach ($doctrineClassMetadata->fieldMappings as $fieldMapping) {
            $ddrRestPropertyMetadata = new PropertyMetadata($class->getName(), $fieldMapping['fieldName']);
            $ddrRestPropertyMetadata->setType($fieldMapping['type']);
            $ddrRestClassMetadata->addPropertyMetadata($ddrRestPropertyMetadata);
        }

        foreach ($doctrineClassMetadata->associationMappings as $associationMapping) {
            $ddrRestPropertyMetadata = new PropertyMetadata($class->getName(), $associationMapping['fieldName']);
            $ddrRestPropertyMetadata->setAssociation(true);
            $ddrRestPropertyMetadata->setTargetClass($associationMapping['targetEntity']);
            $ddrRestPropertyMetadata->setCollection(
                $doctrineClassMetadata->isCollectionValuedAssociation($associationMapping['fieldName'])
            );
            $ddrRestClassMetadata->addPropertyMetadata($ddrRestPropertyMetadata);
        }

        return $ddrRestClassMetadata;
    }
}

<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Driver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
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
        $ddrRestClassMetadata = new ClassMetadata($class->getName());
        try {
            $doctrineClassMetadata = $this->entityManager->getClassMetadata($class->getName());
        } catch (MappingException $e) {
            return $ddrRestClassMetadata;
        }

        //TODO: Handle Embedded Entities

        foreach ($doctrineClassMetadata->fieldMappings as $fieldMapping) {
            if (!array_key_exists('declared', $fieldMapping) && !array_key_exists('declaredField', $fieldMapping)) {
                $ddrRestPropertyMetadata = new PropertyMetadata(
                    $doctrineClassMetadata->getName(),
                    $fieldMapping['fieldName']
                );
                $ddrRestPropertyMetadata->setType($fieldMapping['type']);
                $ddrRestClassMetadata->addPropertyMetadata($ddrRestPropertyMetadata);
            }
        }

        foreach ($doctrineClassMetadata->associationMappings as $associationMapping) {
            $ddrRestPropertyMetadata = new PropertyMetadata(
                $doctrineClassMetadata->getName(),
                $associationMapping['fieldName']
            );
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

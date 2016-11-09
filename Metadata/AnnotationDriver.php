<?php
namespace Dontdrinkandroot\RestBundle\Metadata;

use Doctrine\Common\Annotations\Reader;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Postable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Puttable;
use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;

class AnnotationDriver implements DriverInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new MergeableClassMetadata($class->getName());

        foreach ($class->getProperties() as $reflectionProperty) {
            $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());

            $puttableAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Puttable::class);
            if (null !== $puttableAnnotation) {
                $propertyMetadata->setPuttable(true);
            }

            $postableAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Postable::class);
            if (null !== $postableAnnotation) {
                $propertyMetadata->setPostable(true);
            }

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }
}

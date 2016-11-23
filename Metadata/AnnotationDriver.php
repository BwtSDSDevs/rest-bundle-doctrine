<?php
namespace Dontdrinkandroot\RestBundle\Metadata;

use Doctrine\Common\Annotations\Reader;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Includable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Postable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Puttable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\RootResource;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\SubResource;
use Metadata\Driver\DriverInterface;

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
        $classMetadata = new ClassMetadata($class->getName());

        /** @var RootResource $restResourceAnnotation */
        $restResourceAnnotation = $this->reader->getClassAnnotation($class, RootResource::class);
        if (null !== $restResourceAnnotation) {
            $classMetadata->setRestResource(true);
            if (null !== $restResourceAnnotation->namePrefix) {
                $classMetadata->setNamePrefix($restResourceAnnotation->namePrefix);
            }
            if (null !== $restResourceAnnotation->pathPrefix) {
                $classMetadata->setPathPrefix($restResourceAnnotation->pathPrefix);
            }
            if (null !== $restResourceAnnotation->service) {
                $classMetadata->setService($restResourceAnnotation->service);
            }
            if (null !== $restResourceAnnotation->controller) {
                $classMetadata->setController($restResourceAnnotation->controller);
            }
        }

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

            $includableAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Includable::class);
            if (null !== $includableAnnotation) {
                $propertyMetadata->setIncludable(true);
            }

            $subResourceAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, SubResource::class);
            if (null !== $subResourceAnnotation) {
                $propertyMetadata->setSubResource(true);
            }

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }
}

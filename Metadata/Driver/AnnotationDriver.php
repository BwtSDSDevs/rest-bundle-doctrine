<?php
namespace Dontdrinkandroot\RestBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Excluded;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Includable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Postable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Puttable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\RootResource;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\SubResource;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\Driver\DriverInterface;

class AnnotationDriver implements DriverInterface
{
    private $reader;

    /**
     * @var DriverInterface
     */
    private $doctrineDriver;

    public function __construct(Reader $reader, DriverInterface $doctrineDriver)
    {
        $this->reader = $reader;
        $this->doctrineDriver = $doctrineDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        /** @var ClassMetadata $ddrRestClassMetadata */
        $ddrRestClassMetadata = $this->doctrineDriver->loadMetadataForClass($class);
        if (null === $ddrRestClassMetadata) {
            $ddrRestClassMetadata = new ClassMetadata($class->getName());
        }

        /** @var RootResource $restResourceAnnotation */
        $restResourceAnnotation = $this->reader->getClassAnnotation($class, RootResource::class);
        if (null !== $restResourceAnnotation) {

            $ddrRestClassMetadata->setRestResource(true);

            if (null !== $restResourceAnnotation->namePrefix) {
                $ddrRestClassMetadata->setNamePrefix($restResourceAnnotation->namePrefix);
            }

            if (null !== $restResourceAnnotation->pathPrefix) {
                $ddrRestClassMetadata->setPathPrefix($restResourceAnnotation->pathPrefix);
            }

            if (null !== $restResourceAnnotation->service) {
                $ddrRestClassMetadata->setService($restResourceAnnotation->service);
            }

            if (null !== $restResourceAnnotation->controller) {
                $ddrRestClassMetadata->setController($restResourceAnnotation->controller);
            }

            if (null !== $restResourceAnnotation->methods) {
                $methods = [];
                $methodAnnotations = $restResourceAnnotation->methods;
                foreach ($methodAnnotations as $methodAnnotation) {
                    $methods[$methodAnnotation->name] = $methodAnnotation;
                }
                $ddrRestClassMetadata->setMethods($methods);
            }

            if (null !== $restResourceAnnotation->methods) {
                $ddrRestClassMetadata->setMethods($restResourceAnnotation->methods);
            }
        }

        foreach ($class->getProperties() as $reflectionProperty) {

            $propertyMetadata = $ddrRestClassMetadata->getPropertyMetadata($reflectionProperty->getName());
            if (null === $propertyMetadata) {
                $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());
            }

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
                $paths = $includableAnnotation->paths;
                if (null === $paths) {
                    $paths = [$reflectionProperty->name];
                }
                $propertyMetadata->setIncludable(true);
                $propertyMetadata->setIncludablePaths($paths);
            }

            $excludedAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Excluded::class);
            if (null !== $excludedAnnotation) {
                $propertyMetadata->setExcluded(true);
            }

            /** @var SubResource $subResourceAnnotation */
            $subResourceAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, SubResource::class);
            if (null !== $subResourceAnnotation) {

                $propertyMetadata->setSubResource(true);
                if (null !== $subResourceAnnotation->listRight) {
                    $propertyMetadata->setSubResourceListRight($subResourceAnnotation->listRight);
                }

                if (null !== $subResourceAnnotation->path) {
                    $propertyMetadata->setSubResourcePath($subResourceAnnotation->path);
                }

                if (null !== $subResourceAnnotation->postRight) {
                    $propertyMetadata->setSubResourcePostRight($subResourceAnnotation->postRight);
                }

                if (null !== $subResourceAnnotation->putRight) {
                    $propertyMetadata->setSubResourcePutRight($subResourceAnnotation->putRight);
                }

                if (null !== $subResourceAnnotation->deleteRight) {
                    $propertyMetadata->setSubResourceDeleteRight($subResourceAnnotation->deleteRight);
                }
            }

            $ddrRestClassMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $ddrRestClassMetadata;
    }
}

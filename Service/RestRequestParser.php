<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
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
    private $ddrRestMetadataFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(

        MetadataFactory $ddrRestMetadataFactory,
        PropertyAccessor $propertyAccessor,
        EntityManagerInterface $entityManager
    ) {
        $this->ddrRestMetadataFactory = $ddrRestMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
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
        $content = $this->getRequestContent($request);
        $format = $request->getRequestFormat();

        $data = $request->request->all();

        if (null === $entity) {
            $entity = new $entityClass;
        }

        $this->updateObject($entity, $method, $data);

        return $entity;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getRequestContent(Request $request)
    {
        $content = $request->getContent();
        if ('' !== $content) {
            return $content;
        }

        $parameters = $request->request->all();
        if (count($parameters) > 0) {
            return json_encode($parameters);
        }

        return '{}';
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param array  $data
     */
    protected function updateObject(&$object, $method, $data)
    {
        $classMetadata = $this->ddrRestMetadataFactory->getMetadataForClass(get_class($object));

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $classMetadata->propertyMetadata)) {
                continue;
                //throw new \RuntimeException(sprintf('No field %s for Class %s', $key, get_class($object)));
            }
            /** @var PropertyMetadata $propertyMetadata */
            $propertyMetadata = $classMetadata->propertyMetadata[$key];
            if ($this->isUpdateable($method, $propertyMetadata)) {
                $this->updateProperty($object, $method, $key, $value);
            }
        }
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param string $propertyName
     * @param mixed  $value
     */
    protected function updateProperty(&$object, $method, $propertyName, $value)
    {
        $doctrineClassMetadata = $this->entityManager->getClassMetadata(get_class($object));

        if (array_key_exists($propertyName, $doctrineClassMetadata->embeddedClasses)) {
            $embeddedClass = $doctrineClassMetadata->embeddedClasses[$propertyName]['class'];
            $this->updatePropertyObject($object, $method, $embeddedClass, $propertyName, $value);

            return;
        }

        if (array_key_exists($propertyName, $doctrineClassMetadata->associationMappings)) {
            $associatedClass = $doctrineClassMetadata->associationMappings[$propertyName]['targetEntity'];
            $this->updatePropertyObject($object, $method, $associatedClass, $propertyName, $value);

            return;
        }

        $doctrineFieldMetadata = $doctrineClassMetadata->fieldMappings[$propertyName];
        $type = $doctrineFieldMetadata['type'];

        $convertedValue = $this->convert($type, $value);

        $this->propertyAccessor->setValue($object, $propertyName, $convertedValue);
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param string $propertyName
     * @param [] $value
     */
    protected function updatePropertyObject(&$object, $method, $class, $propertyName, $value)
    {
        $propertyObject = $this->propertyAccessor->getValue($object, $propertyName);
        if (null === $propertyObject) {
            $propertyObject = new $class;
        }

        $this->updateObject($propertyObject, $method, $value);
        $this->propertyAccessor->setValue($object, $propertyName, $propertyObject);
    }

    /**
     * @param string           $method
     * @param PropertyMetadata $propertyMetadata
     *
     * @return bool
     */
    protected function isUpdateable($method, PropertyMetadata $propertyMetadata)
    {
        if (Request::METHOD_PUT === $method || Request::METHOD_PATCH === $method) {
            return $propertyMetadata->isPuttable();
        }
        if (Request:: METHOD_POST === $method) {
            return $propertyMetadata->isPostable();
        }

        return false;
    }

    private function convert($type, $value)
    {
        if (null === $value) {
            return $value;
        }

        switch ($type) {
            case 'datetime':
                return new \DateTime($value);
            default:
                return $value;
        }
    }
}

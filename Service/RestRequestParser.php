<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
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
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(MetadataFactory $ddrRestMetadataFactory, PropertyAccessor $propertyAccessor)
    {
        $this->ddrRestMetadataFactory = $ddrRestMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param Request     $request
     * @param string      $entityClass
     * @param object|null $entity
     *
     * @return object
     */
    public function parseEntity(
        Request $request,
        $entityClass,
        $entity = null
    ) {
        $method = $request->getMethod();
        $format = $request->getRequestFormat();

        if ('json' !== $format) {
            throw new \RuntimeException(sprintf('Unsupported format "%s"', $format));
        }

        $data = $this->getRequestContent($request);

        if (null === $entity) {
            $entity = new $entityClass;
        }

        $this->updateObject($entity, $method, $data);

        return $entity;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestContent(Request $request)
    {
        $content = $request->getContent();
        if ('' !== $content) {
            return json_decode($content, true);
        }

        return $request->request->all();
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param array  $data
     */
    protected function updateObject(
        &$object,
        $method,
        $data
    ) {
        $classMetadata = $this->ddrRestMetadataFactory->getMetadataForClass(ClassUtils::getClass($object));

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $classMetadata->propertyMetadata)) {
                continue;
                //throw new \RuntimeException(sprintf('No field %s for Class %s', $key, get_class($object)));
            }
            /** @var PropertyMetadata $propertyMetadata */
            $propertyMetadata = $classMetadata->propertyMetadata[$key];
            if ($this->isUpdateable($method, $propertyMetadata)) {
                $this->updateProperty($object, $method, $key, $value, $propertyMetadata);
            }
        }
    }

    /**
     * @param object           $object Access by reference.
     * @param string           $method
     * @param string           $propertyName
     * @param mixed            $value
     * @param PropertyMetadata $propertyMetadata
     */
    protected function updateProperty(
        &$object,
        $method,
        $propertyName,
        $value,
        PropertyMetadata $propertyMetadata
    ) {

        // TODO: Reenable embedded
//        if (array_key_exists($propertyName, $doctrineClassMetadata->embeddedClasses)) {
//            $embeddedClass = $doctrineClassMetadata->embeddedClasses[$propertyName]['class'];
//            $this->updatePropertyObject($object, $method, $embeddedClass, $propertyName, $value);
//
//            return;
//        }

        //TODO: Reenable associations
//        if (array_key_exists($propertyName, $doctrineClassMetadata->associationMappings)) {
//            $associatedClass = $doctrineClassMetadata->associationMappings[$propertyName]['targetEntity'];
//            $this->updatePropertyObject($object, $method, $associatedClass, $propertyName, $value);
//
//            return;
//        }

        if (array_key_exists($propertyMetadata->getType(), Type::getTypesMap())) {
            $convertedValue = $this->convert($propertyMetadata->getType(), $value);
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, $convertedValue);
        } else {
            $this->updatePropertyObject($object, $method, $propertyMetadata, $value);
        }
    }

    protected function updatePropertyObject(
        &$object,
        string $method,
        PropertyMetadata $propertyMetadata,
        $value
    ) {
        $propertyObject = $this->propertyAccessor->getValue($object, $propertyMetadata->name);
        if (null === $propertyObject) {
            $type = $propertyMetadata->getType();
            $propertyObject = new $type;
        }

        $this->updateObject($propertyObject, $method, $value);
        $this->propertyAccessor->setValue($object, $propertyMetadata->name, $propertyObject);
    }

    /**
     * @param string           $method
     * @param PropertyMetadata $propertyMetadata
     *
     * @return bool
     */
    protected function isUpdateable(
        $method,
        PropertyMetadata $propertyMetadata
    ) {
        if (Request::METHOD_PUT === $method || Request::METHOD_PATCH === $method) {
            return $propertyMetadata->isPuttable();
        }
        if (Request:: METHOD_POST === $method) {
            return $propertyMetadata->isPostable();
        }

        return false;
    }

    private function convert(?string $type, $value)
    {
        if (null === $value) {
            return $value;
        }

        switch ($type) {
            case 'datetime':
            case 'date':
            case 'time':
                return new \DateTime($value);
            default:
                return $value;
        }
    }
}

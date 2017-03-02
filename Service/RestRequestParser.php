<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\MetadataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        MetadataFactory $ddrRestMetadataFactory,
        PropertyAccessor $propertyAccessor,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->ddrRestMetadataFactory = $ddrRestMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->authorizationChecker = $authorizationChecker;
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
            if ($this->isUpdateable($object, $method, $propertyMetadata)) {
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
     * @param object           $object
     * @param PropertyMetadata $propertyMetadata
     *
     * @return bool
     */
    protected function isUpdateable(
        $object,
        $method,
        PropertyMetadata $propertyMetadata
    ) {
        if ((Request::METHOD_PUT === $method || Request::METHOD_PATCH === $method) && $propertyMetadata->isPuttable()) {
            return $this->isGranted($object, $propertyMetadata->getPuttableRight());
        }
        if (Request:: METHOD_POST === $method && $propertyMetadata->isPostable()) {
            return $this->isGranted($object, $propertyMetadata->getPostableRight());
        }

        return false;
    }

    private function isGranted($object, ?Right $right)
    {
        if (null === $right) {
            return true;
        }

        $propertyPath = $right->propertyPath;
        if (null === $propertyPath) {
            return $this->authorizationChecker->isGranted($right->attributes);
        } else {
            $subject = $this->resolveSubject($object, $propertyPath);

            return $this->authorizationChecker->isGranted($right->attributes, $subject);
        }
    }

    private function resolveSubject($entity, $propertyPath)
    {
        if ('this' === $propertyPath) {
            return $entity;
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
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

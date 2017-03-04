<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
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
    private $metadataFactory;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        MetadataFactory $metadataFactory,
        PropertyAccessor $propertyAccessor,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
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
        $classMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getClass($object));

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $classMetadata->propertyMetadata)) {
                /** @var PropertyMetadata $propertyMetadata */
                $propertyMetadata = $classMetadata->propertyMetadata[$key];
                if ($this->isUpdateable($object, $method, $propertyMetadata)) {
                    $this->updateProperty($object, $method, $propertyMetadata, $value);
                }
            }
        }
    }

    /**
     * @param object           $object Access by reference.
     * @param string           $method
     * @param PropertyMetadata $propertyMetadata
     * @param mixed            $value
     */
    protected function updateProperty(
        &$object,
        string $method,
        PropertyMetadata $propertyMetadata,
        $value
    ) {
        $byReference = $this->isUpdateableByReference($propertyMetadata, $method);
        if ($byReference) {
            $this->updateByReference($object, $propertyMetadata, $value);
        } elseif (array_key_exists($propertyMetadata->getType(), Type::getTypesMap())) {
            $convertedValue = $this->convert($propertyMetadata->getType(), $value);
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, $convertedValue);
        } else {
            $this->updatePropertyObject($object, $method, $propertyMetadata, $value);
        }
    }

    private function updateByReference(&$object, PropertyMetadata $propertyMetadata, $value)
    {
        $type = $propertyMetadata->getType();
        $classMetadata = $this->entityManager->getClassMetadata($type);
        $identifiers = $classMetadata->getIdentifier();
        $id = [];
        foreach ($identifiers as $idName) {
            $id[$idName] = $value[$idName];
        }
        $reference = $this->entityManager->getReference($type, $id);
        $this->propertyAccessor->setValue($object, $propertyMetadata->name, $reference);
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
        string $method,
        PropertyMetadata $propertyMetadata
    ): bool {
        if ((Request::METHOD_PUT === $method || Request::METHOD_PATCH === $method) && $propertyMetadata->isPuttable()) {
            return $this->isGranted($object, $propertyMetadata->getPuttable()->right);
        }

        if (Request:: METHOD_POST === $method && $propertyMetadata->isPostable()) {
            return $this->isGranted($object, $propertyMetadata->getPostable()->right);
        }

        return false;
    }

    private function isGranted($object, ?Right $right): bool
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

    private function isUpdateableByReference(PropertyMetadata $propertyMetadata, string $method)
    {
        if (
            Method::PUT === $method
            && null !== $propertyMetadata->getPuttable() && true === $propertyMetadata->getPuttable()->byReference
        ) {
            return true;
        }

        if (
            Method::POST === $method
            && null !== $propertyMetadata->getPostable() && true === $propertyMetadata->getPostable()->byReference
        ) {
            return true;
        }

        return false;
    }
}

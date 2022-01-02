<?php

namespace Dontdrinkandroot\RestBundle\Serializer;

use BadMethodCallException;
use DateTime;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Metadata\RestMetadataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RestDenormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface
{
    const DDR_REST_METHOD = 'ddrRestMethod';
    const DDR_REST_ENTITY = 'ddrRestEntity';

    private ?AuthorizationCheckerInterface $authorizationChecker = null;

    public function __construct(
        private RestMetadataFactory $metadataFactory,
        private PropertyAccessorInterface $propertyAccessor,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!array_key_exists(self::DDR_REST_METHOD, $context)) {
            throw new BadMethodCallException('No REST Method specified');
        }

        $method = $context[self::DDR_REST_METHOD];
        $entity = array_key_exists(self::DDR_REST_ENTITY, $context) ? $context[self::DDR_REST_ENTITY] : null;

        if (null === $entity) {
            $entity = new $class;
        }

        $this->updateObject($entity, $method, $data);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return 'json' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param array  $data
     */
    protected function updateObject(&$object, $method, $data)
    {
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
    protected function updateProperty(&$object, string $method, PropertyMetadata $propertyMetadata, $value)
    {
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
        if (null === $value) {
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, null);
        } else {
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
    }

    protected function updatePropertyObject(&$object, string $method, PropertyMetadata $propertyMetadata, $value)
    {
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
    protected function isUpdateable($object, string $method, PropertyMetadata $propertyMetadata): bool
    {
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

        /* If no Security is enabled always deny access */
        if (null === $this->authorizationChecker) {
            return false;
        }

        $propertyPath = $right->propertyPath;
        if (null === $propertyPath) {
            foreach ($right->attributes as $attribute) {
                if (!$this->authorizationChecker->isGranted($attribute)) {
                    return false;
                }
            }
            return true;
        }

        $subject = $this->resolveSubject($object, $propertyPath);
        foreach ($right->attributes as $attribute) {
            if (!$this->authorizationChecker->isGranted($attribute, $subject)) {
                return false;
            }
        }
        return true;
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
            case Types::DATETIME_MUTABLE:
            case Types::DATE_MUTABLE:
            case Types::TIME_MUTABLE:
                return new DateTime($value);
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

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
}

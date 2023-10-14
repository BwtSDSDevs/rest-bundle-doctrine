<?php

namespace Dontdrinkandroot\RestBundle\Serializer;

use BadMethodCallException;
use DateTime;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Right;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Writeable;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Metadata\RestMetadataFactory;
use Symfony\Component\ExpressionLanguage\Expression;
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

    protected function updateObject(object $object, CrudOperation $method, array $data)
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

    protected function updateProperty(
        object $object,
        CrudOperation $method,
        PropertyMetadata $propertyMetadata,
        mixed $value
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

    private function updateByReference(object $object, PropertyMetadata $propertyMetadata, $value)
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

    protected function updatePropertyObject(
        object $object,
        CrudOperation $method,
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

    protected function isUpdateable(object $object, CrudOperation $method, PropertyMetadata $propertyMetadata): bool
    {
        if (CrudOperation::UPDATE === $method && $propertyMetadata->isPuttable()) {
            return $this->isGranted($object, $propertyMetadata->getPuttable());
        }

        if (CrudOperation::CREATE === $method && $propertyMetadata->isPostable()) {
            return $this->isGranted($object, $propertyMetadata->getPostable());
        }

        return false;
    }

    private function isGranted($object, ?Writeable $writeable): bool
    {
        if (null === $writeable) {
            return true;
        }

        /* If no Security is enabled always deny access */
        if (null === $this->authorizationChecker) {
            return false;
        }

        if (null !== $writeable->granted) {
            return $this->authorizationChecker->isGranted($writeable->granted);
        }

        if (null !== $writeable->grantedExpression) {
            return $this->authorizationChecker->isGranted(new Expression($writeable->grantedExpression), $object);
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

        return match ($type) {
            Types::DATETIME_MUTABLE, Types::DATE_MUTABLE, Types::TIME_MUTABLE => new DateTime($value),
            default => $value,
        };
    }

    private function isUpdateableByReference(PropertyMetadata $propertyMetadata, CrudOperation $method)
    {
        if (
            CrudOperation::UPDATE === $method
            && null !== $propertyMetadata->getPuttable() && true === $propertyMetadata->getPuttable()->byReference
        ) {
            return true;
        }

        if (
            CrudOperation::CREATE === $method
            && null !== $propertyMetadata->getPostable() && true === $propertyMetadata->getPostable()->byReference
        ) {
            return true;
        }

        return false;
    }

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
}

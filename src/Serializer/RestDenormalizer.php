<?php

namespace SdsDev\RestBundleDoctrine\Serializer;

use BadMethodCallException;
use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use SdsDev\RestBundleDoctrine\Metadata\Common\CrudOperation;
use SdsDev\RestBundleDoctrine\Defaults\Defaults;
use SdsDev\RestBundleDoctrine\Metadata\PropertyMetadata;
use SdsDev\RestBundleDoctrine\Metadata\RestMetadataFactory;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RestDenormalizer implements DenormalizerInterface
{
    const DDR_REST_METHOD = 'ddrRestMethod';
    const DDR_REST_ENTITY = 'ddrRestEntity';

    public function __construct(
        private RestMetadataFactory $metadataFactory,
        private PropertyAccessorInterface $propertyAccessor,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (!array_key_exists(self::DDR_REST_METHOD, $context)) {
            throw new BadMethodCallException('No REST Method specified');
        }

        $method = $context[self::DDR_REST_METHOD];
        $entity = array_key_exists(self::DDR_REST_ENTITY, $context) ? $context[self::DDR_REST_ENTITY] : null;

        if (null === $entity) {
            $entity = new $type;
        }

        $this->updateObject($entity, $method, $data);

        return $entity;
    }

    /**
     * {@inheritdoc}
     * @param mixed $data
     * @param string $type
     * @param null $format
     * @param array $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return Defaults::SERIALIZE_FORMAT === $format;
    }

    protected function updateObject(object $object, CrudOperation $method, array $data): void
    {
        $classMetadata = $this->metadataFactory->getMetadataForClass(get_class($object));

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
        object &$object,
        CrudOperation $method,
        PropertyMetadata $propertyMetadata,
        mixed $value
    ): void {
        if(!empty($propertyMetadata->getEntityClass())){
            $this->updateObjectByReference($object, $method, $propertyMetadata, $value);
        }
        else if (array_key_exists($propertyMetadata->getType(), Type::getTypesMap())) {
            $convertedValue = $this->convert($propertyMetadata->getType(), $value);
            if(method_exists($object, 'set' . ucfirst($propertyMetadata->name))) {
                $this->propertyAccessor->setValue($object, $propertyMetadata->name, $convertedValue);
            }
            else{
                $reflectionClass = new \ReflectionClass(get_class($object));
                $reflectionProperty = $reflectionClass->getProperty($propertyMetadata->name);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $convertedValue);
            }

        } else {
            $this->updatePropertyObject($object, $method, $propertyMetadata, $value);
        }
    }

    protected function updatePropertyObject(
        object &$object,
        CrudOperation $method,
        PropertyMetadata $propertyMetadata,
        $value
    ): void {
        $propertyObject = $this->propertyAccessor->getValue($object, $propertyMetadata->name);
        if (null === $propertyObject) {
            $type = $propertyMetadata->getType();
            $propertyObject = new $type;
        }

        $this->updateObject($propertyObject, $method, $value);
        $this->propertyAccessor->setValue($object, $propertyMetadata->name, $propertyObject);
    }

    protected function updateObjectByReference(
        object &$object,
        CrudOperation $method,
        PropertyMetadata $propertyMetadata,
        $value
    ) : void
    {
        if (null === $value) {
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, null);
        } else if(!is_array($value)) {
            $type = $propertyMetadata->getType();
            $reference = $this->entityManager->getReference($type, $value);
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, $reference);
        }
        else if(!empty($value)){
            $type = $propertyMetadata->getType();
//            $classMetadata = $this->entityManager->getClassMetadata($type);
//            $identifier = $classMetadata->getIdentifier();
            $references = [];
            foreach ($value as $val){
                $references[] = $this->entityManager->getReference($type, $val);
            }

            $this->propertyAccessor->setValue($object, $propertyMetadata->name, $references);
        }
    }

    protected function isUpdateable(object $object, CrudOperation $method, PropertyMetadata $propertyMetadata): bool
    {
        if (CrudOperation::UPDATE === $method && $propertyMetadata->isPuttable()) {
            return true;
        }

        if (CrudOperation::CREATE === $method && $propertyMetadata->isPostable()) {
            return true;
        }

        return false;
    }

    private function convert(?string $type, mixed $value): mixed
    {
        if (null === $value) {
            return $value;
        }

        return match ($type) {
            Types::DATETIME_MUTABLE, Types::DATE_MUTABLE, Types::TIME_MUTABLE => new DateTime($value),
            default => $value,
        };
    }

    public function getSupportedTypes(?string $format): array
    {
        if(Defaults::SERIALIZE_FORMAT === $format)
            return [ '*' => true];

        return [];
    }
}

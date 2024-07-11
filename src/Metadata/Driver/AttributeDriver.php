<?php

namespace Niebvelungen\RestBundleDoctrine\Metadata\Driver;

use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Niebvelungen\RestBundleDoctrine\Helper\StringHelper;
use Niebvelungen\RestBundleDoctrine\Metadata\ClassMetadata;
use Niebvelungen\RestBundleDoctrine\Metadata\PropertyMetadata;
use Metadata\Driver\DriverInterface;
use ReflectionAttribute;
use ReflectionClass;

class AttributeDriver implements DriverInterface
{
    const ORM_TYPES = [ManyToMany::class, OneToMany::class, ManyToOne::class];

    const DOCTRINE_EXCLUDE_FIELDS = ['lazyObjectState'];
    const MAPPED_TYPES = [
        "int" => "integer",
        "bool" => "boolean"
    ];

    private $doctrineDriver;

    public function __construct(DriverInterface $doctrineDriver)
    {
        $this->doctrineDriver = $doctrineDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(ReflectionClass $class): \Metadata\ClassMetadata
    {
        /** @var ClassMetadata $ddrRestClassMetadata */
        $ddrRestClassMetadata = $this->doctrineDriver->loadMetadataForClass($class);
        if (null === $ddrRestClassMetadata) {
            $ddrRestClassMetadata = new ClassMetadata($class->getName());
        }

        foreach ($class->getProperties() as $reflectionProperty) {
            $propertyMetadata = $ddrRestClassMetadata->getPropertyMetadata($reflectionProperty->getName());
            if (null === $propertyMetadata) {
                $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());
            }

            $type = $reflectionProperty->getType()->getName();
            if(str_starts_with('?', $type))
                $type = str_replace('?', '', $type);

            $type = self::MAPPED_TYPES[$type] ?? $type;

            $propertyMetadata->setType($type);

            $attributes = array_filter($reflectionProperty->getAttributes(), function (ReflectionAttribute $attribute) {
                return in_array($attribute->getName(), self::ORM_TYPES);
            });

            if(!empty($attributes)){
                $this->parseIncludable($propertyMetadata);
                $nonNullClass = str_starts_with('?', $reflectionProperty->getType()->getName())
                    ? substr($reflectionProperty->getType()->getName(), 1)
                    : $reflectionProperty->getType()->getName();
                $propertyMetadata->setEntityClass($nonNullClass);
            }

            $propertyMetadata->setPuttable(true);
            $propertyMetadata->setPostable(true);

            if (in_array($reflectionProperty->getName(), self::DOCTRINE_EXCLUDE_FIELDS)) {
                $propertyMetadata->setExcluded(true);
            }

            $ddrRestClassMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $ddrRestClassMetadata;
    }

    public function parseIncludable(PropertyMetadata $propertyMetadata): void
    {
        $paths = [StringHelper::camelCaseToSnakeCase($propertyMetadata->name)];

        $propertyMetadata->setIncludable(true);
        $propertyMetadata->setIncludablePaths($paths);
    }
}

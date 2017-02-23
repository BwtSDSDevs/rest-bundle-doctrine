<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Driver;

use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use Symfony\Component\Yaml\Yaml;

class YamlDriver extends AbstractFileDriver
{
    /**
     * @var DriverInterface
     */
    private $doctrineDriver;

    public function __construct(FileLocatorInterface $locator, DriverInterface $doctrineDriver)
    {
        parent::__construct($locator);
        $this->doctrineDriver = $doctrineDriver;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        /** @var ClassMetadata $ddrRestClassMetadata */
        $classMetadata = $this->doctrineDriver->loadMetadataForClass($class);
        if (null === $ddrRestClassMetadata) {
            $classMetadata = new ClassMetadata($class->getName());
        }

        $config = Yaml::parse(file_get_contents($file));

        if (key($config) !== $class->name) {
            throw new \RuntimeException(
                sprintf('Class definition mismatch for "%s" in "%s": %s', $class->getName(), $file, key($config))
            );
        }

        $propertyConfigs = [];
        if (array_key_exists('properties', $config[key($config)])) {
            $propertyConfigs = $config[key($config)]['properties'];
        }
        foreach ($class->getProperties() as $reflectionProperty) {

            $propertyMetadata = $ddrRestClassMetadata->getPropertyMetadata($reflectionProperty->getName());
            if (null === $propertyMetadata) {
                $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());
            }

            if (array_key_exists($reflectionProperty->getName(), $propertyConfigs)) {
                $propertyConfig = $propertyConfigs[$reflectionProperty->getName()];
                if (array_key_exists('puttable', $propertyConfig) && true === $propertyConfig['puttable']) {
                    $propertyMetadata->setPuttable(true);
                }
                if (array_key_exists('postable', $propertyConfig) && true === $propertyConfig['postable']) {
                    $propertyMetadata->setPostable(true);
                }
            }
            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'yml';
    }
}

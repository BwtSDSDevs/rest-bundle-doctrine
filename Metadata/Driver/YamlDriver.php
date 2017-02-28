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
        if (null === $classMetadata) {
            $classMetadata = new ClassMetadata($class->getName());
        }

        $config = Yaml::parse(file_get_contents($file));
        $className = key($config);

        if ($className !== $class->name) {
            throw new \RuntimeException(
                sprintf('Class definition mismatch for "%s" in "%s": %s', $class->getName(), $file, key($config))
            );
        }

        $config = $config[$className];
        if (!is_array($config)) {
            $config = [];
        }

        if (array_key_exists('rootResource', $config) && true === $config['rootResource']) {
            $classMetadata->setRestResource(true);
        }

        if (array_key_exists('service', $config)) {
            $classMetadata->setService($config['service']);
        }

        $fieldConfigs = [];
        if (array_key_exists('fields', $config)) {
            $fieldConfigs = $config['fields'];
        }

        foreach ($class->getProperties() as $reflectionProperty) {

            $propertyName = $reflectionProperty->getName();
            $propertyMetadata = $this->getOrCreatePropertymetadata($classMetadata, $propertyName);

            if (array_key_exists($propertyName, $fieldConfigs)) {
                $fieldConfig = $fieldConfigs[$propertyName];
                $this->parseFieldConfig($propertyName, $fieldConfig, $propertyMetadata);
                unset($fieldConfigs[$propertyName]);
            }

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        /* Parse unbacked field definitions */
        foreach ($fieldConfigs as $name => $fieldConfig) {
            $propertyMetadata = $this->getOrCreatePropertymetadata($classMetadata, $name);
            $this->parseFieldConfig($propertyName, $fieldConfig, $propertyMetadata);
            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }

    protected function parseFieldConfig(string $name, array $fieldConfig, PropertyMetadata $propertyMetadata): void
    {
        if (null !== $value = $this->getBool('puttable', $fieldConfig)) {
            $propertyMetadata->setPuttable($value);
        }

        if (null !== $value = $this->getBool('excluded', $fieldConfig)) {
            $propertyMetadata->setExcluded($value);
        }

        if (null !== $value = $this->getBool('postable', $fieldConfig)) {
            $propertyMetadata->setPostable($value);
        }

        if (array_key_exists('includable', $fieldConfig)) {
            $value = $fieldConfig['includable'];
            if (is_array($value)) {
                $propertyMetadata->setIncludable(true);
                $propertyMetadata->setIncludablePaths($value);
            } elseif (true === $value) {
                $propertyMetadata->setIncludable(true);
                $propertyMetadata->setIncludablePaths([$name]);
            }
        }
    }

    private function getBool(string $key, array $haystack, bool $required = false)
    {
        if (!array_key_exists($key, $haystack)) {
            if ($required) {
                throw new \RuntimeException(sprintf('Value %s is required', $key));
            }

            return null;
        }

        $value = $haystack[$key];
        if (!is_bool($value)) {
            throw new \RuntimeException(sprintf('Value %s must be of type bool', $key));
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'rest.yml';
    }

    protected function getOrCreatePropertymetadata(ClassMetadata $classMetadata, $propertyName): PropertyMetadata
    {
        $propertyMetadata = $classMetadata->getPropertyMetadata($propertyName);
        if (null === $propertyMetadata) {
            $propertyMetadata = new PropertyMetadata($classMetadata->name, $propertyName);

            return $propertyMetadata;
        }

        return $propertyMetadata;
    }
}

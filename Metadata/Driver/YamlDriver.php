<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Driver;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Postable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Puttable;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class YamlDriver extends AbstractFileDriver
{
    private DriverInterface $doctrineDriver;

    public function __construct(FileLocatorInterface $locator, DriverInterface $doctrineDriver)
    {
        parent::__construct($locator);
        $this->doctrineDriver = $doctrineDriver;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(ReflectionClass $class, $file): ?\Metadata\ClassMetadata
    {
        /** @var ClassMetadata $ddrRestClassMetadata */
        $classMetadata = $this->doctrineDriver->loadMetadataForClass($class);
        if (null === $classMetadata) {
            $classMetadata = new ClassMetadata($class->getName());
        }

        $config = Yaml::parse(file_get_contents($file));
        $className = key($config);

        if ($className !== $class->name) {
            throw new RuntimeException(
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
        if (array_key_exists('controller', $config)) {
            $classMetadata->controller = $config['controller'];
        }
        if (array_key_exists('idField', $config)) {
            $classMetadata->idField = $config['idField'];
        }
        if (array_key_exists('pathPrefix', $config)) {
            $classMetadata->pathPrefix = $config['pathPrefix'];
        }
        if (array_key_exists('namePrefix', $config)) {
            $classMetadata->namePrefix = $config['namePrefix'];
        }

        $classMetadata->setMethods($this->parseMethods($config));

        $fieldConfigs = [];
        if (array_key_exists('fields', $config)) {
            $fieldConfigs = $config['fields'];
        }

        foreach ($class->getProperties() as $reflectionProperty) {

            $propertyName = $reflectionProperty->getName();
            $propertyMetadata = $this->getOrCreatePropertyMetadata($classMetadata, $propertyName);

            if (array_key_exists($propertyName, $fieldConfigs)) {
                $fieldConfig = $fieldConfigs[$propertyName];
                $this->parseFieldConfig($propertyName, $fieldConfig, $propertyMetadata);
                unset($fieldConfigs[$propertyName]);
            }

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        /* Parse unbacked field definitions */
        foreach ($fieldConfigs as $name => $fieldConfig) {
            $propertyMetadata = $this->getOrCreatePropertyMetadata($classMetadata, $name);
            $this->parseFieldConfig($name, $fieldConfig, $propertyMetadata);
            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }

    protected function parseFieldConfig(string $name, array $fieldConfig, PropertyMetadata $propertyMetadata): void
    {
        $propertyMetadata->setPostable(Postable::parse($fieldConfig['postable'] ?? null));
        $propertyMetadata->setPuttable(Puttable::parse($fieldConfig['puttable'] ?? null));

        if (null !== $value = $fieldConfig['type'] ?? null) {
            $propertyMetadata->setType($value);
        }

        if (null !== $value = $this->getBool('excluded', $fieldConfig)) {
            $propertyMetadata->setExcluded($value);
        }

        if (null !== $value = $this->getBool('virtual', $fieldConfig)) {
            $propertyMetadata->setVirtual($value);
        }

        if (null !== $subResourceConfig = $fieldConfig['subResource'] ?? null) {
            $propertyMetadata->setSubResource(true);
            $propertyMetadata->setMethods($this->parseMethods($subResourceConfig));
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
        $value = $this->getArrayValue($key, $haystack, $required);
        if (null === $value) {
            return null;
        }

        if (!is_bool($value)) {
            throw new RuntimeException(sprintf('Value %s must be of type bool', $key));
        }

        return $value;
    }

    private function getArrayValue(string $key, array $haystack, bool $required = false)
    {
        if (!array_key_exists($key, $haystack)) {
            if ($required) {
                throw new RuntimeException(sprintf('Value %s is required', $key));
            }

            return null;
        }

        return $haystack[$key];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'rest.yaml';
    }

    protected function getOrCreatePropertyMetadata(ClassMetadata $classMetadata, $propertyName): PropertyMetadata
    {
        $propertyMetadata = $classMetadata->getPropertyMetadata($propertyName);
        return $propertyMetadata ?? new PropertyMetadata($classMetadata->name, $propertyName);
    }

    /**
     * @param array $config
     *
     * @return Method[]
     */
    private function parseMethods(array $config)
    {
        if (!array_key_exists('methods', $config)) {
            return null;
        }

        $methods = [];
        $methodsConfig = $config['methods'];
        foreach ($methodsConfig as $name => $config) {
            $method = Method::parse($name, $config);
            $methods[$method->name] = $method;
        }

        return $methods;
    }
}

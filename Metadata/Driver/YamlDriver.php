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

        $propertyConfigs = [];
        if (array_key_exists('fields', $config)) {
            $propertyConfigs = $config['fields'];
        }

        foreach ($class->getProperties() as $reflectionProperty) {

            $propertyMetadata = $classMetadata->getPropertyMetadata($reflectionProperty->getName());
            if (null === $propertyMetadata) {
                $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());
            }

            if (array_key_exists($reflectionProperty->getName(), $propertyConfigs)) {

                $propertyConfig = $propertyConfigs[$reflectionProperty->getName()];

                if (array_key_exists('puttable', $propertyConfig) && true === $propertyConfig['puttable']) {
                    $propertyMetadata->setPuttable(true);
                }

                if (array_key_exists('excluded', $propertyConfig) && true === $propertyConfig['excluded']) {
                    $propertyMetadata->setExcluded(true);
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
        return 'rest.yml';
    }
}

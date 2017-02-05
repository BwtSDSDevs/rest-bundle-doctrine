<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Yaml\Yaml;

class YamlDriver extends AbstractFileDriver
{
    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $classMetadata = new ClassMetadata($class->getName());
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
            $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());
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

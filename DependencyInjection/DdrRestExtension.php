<?php

namespace Dontdrinkandroot\RestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DdrRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $directories = [];
        foreach ($config['metadata']['directories'] as $directory) {
            $directories[$directory['namespace_prefix']] = $directory['path'];
        }

        $container
            ->getDefinition('ddr_rest.metadata.file_locator')
            ->setArguments([$directories]);

        $container->setParameter('ddr.rest.access_token_class', $config['access_token_class']);
        $container->setParameter('ddr.rest.authentication_provider_key', $config['authentication_provider_key']);
    }
}

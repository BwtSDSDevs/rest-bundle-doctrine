<?php

namespace Dontdrinkandroot\RestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DdrRestExtension extends Extension
{
    const ACCESS_TOKEN_CLASS = 'access_token_class';
    const AUTHENTICATION_PROVIDER_KEY = 'authentication_provider_key';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ddr_rest.paths', $config['paths']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $directories = [];
        $directories['Dontdrinkandroot\RestBundle'] = realpath(__DIR__ . '/../Resources/config/rest/');
        if (array_key_exists('metadata', $config) && array_key_exists('directories', $config['metadata'])) {
            foreach ($config['metadata']['directories'] as $directory) {
                $directories[$directory['namespace_prefix']] = $directory['path'];
            }
        }

        $container
            ->getDefinition('ddr_rest.metadata.file_locator')
            ->setArguments([$directories]);

        if (array_key_exists('security', $config)) {
            $this->loadSecurityConfig($config['security'], $container, $loader);
        }
    }

    private function loadSecurityConfig(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
    {
        $accessTokenClass = $config[self::ACCESS_TOKEN_CLASS];
        $authenticationProviderKey = $config[self::AUTHENTICATION_PROVIDER_KEY];
        if (null === $accessTokenClass && null === $authenticationProviderKey) {
            return;
        }

        if (
            (null === $accessTokenClass && null !== $authenticationProviderKey)
            || (null !== $accessTokenClass && null === $authenticationProviderKey)
        ) {
            throw new \RuntimeException(
                sprintf(
                    'You need to provide values for "%s" AND "%s"',
                    self::ACCESS_TOKEN_CLASS,
                    self::AUTHENTICATION_PROVIDER_KEY
                )
            );
        }

        $container->setParameter('ddr_rest.access_token_class', $accessTokenClass);
        $container->setParameter('ddr_rest.authentication_provider_key', $authenticationProviderKey);
        $container->setParameter('ddr_rest.security.enabled', true);

        $loader->load('services_security.yml');
    }
}

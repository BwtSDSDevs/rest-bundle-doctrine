<?php

namespace Dontdrinkandroot\RestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DdrRestExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ddr.rest.access_token_class', $config['access_token_class']);
        $container->setParameter('ddr.rest.authentication_provider_key', $config['authentication_provider_key']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

//        $securityConfig = [
//            'firewalls' => [
//                'api' => [
//                    'stateless' => true,
//                    'pattern' => '^' . $config['api_path'],
//                    'simple_preauth' => [
//                        'authenticator' => 'ddr.fetchtool.security.auth_token_authenticator'
//                    ]
//                ]
//            ]
//        ];
//
//        $container->prependExtensionConfig('security', $securityConfig);

//        $fosRestConfig = [
//            'format_listener' => [
//                'rules' => [
//                    [
//                        'path'             => '^/' . $config['api_path'],
//                        'priorities'       => ['json', 'xml', 'html'],
//                        'prefer_extension' => true
//                    ],
//                    [
//                        'path' => '^/',
//                        'stop' => true
//                    ]
//                ]
//            ]
//        ];
//        $container->prependExtensionConfig('fos_rest', $fosRestConfig);
    }
}

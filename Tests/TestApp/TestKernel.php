<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * {@inheritdoc}
     */
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/ddr_rest_bundle/cache/';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/ddr_rest_bundle/logs/';
    }

    private function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        $configDir = $this->getConfigDir();
        $container->import($configDir . '/packages.yaml');
        $container->import($configDir . '/' . $this->environment . '/packages.yaml');
        $container->import($configDir . '/services.yaml');
        $container->import($configDir . '/' . $this->environment . '/services.yaml');
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getConfigDir();
        $routes->import($configDir . '/' . $this->environment . '/routes.yaml');
    }
}

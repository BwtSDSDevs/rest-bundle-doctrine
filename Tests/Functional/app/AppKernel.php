<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\app;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @var array
     */
    private $bundleClasses;

    public function __construct($environment, $debug, $bundleClasses = [])
    {
        parent::__construct($environment, $debug);
        $this->bundleClasses = $bundleClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $contents = require $this->getProjectDir() . '/Tests/Functional/app/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/ddr_rest_bundle/cache/';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return sys_get_temp_dir() . '/ddr_rest_bundle/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $resource = $this->getRootDir() . '/config/' . $this->getEnvironment() . '/config.yml';
        $loader->load($resource);
    }
}

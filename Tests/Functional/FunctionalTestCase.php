<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Tests\Functional\app\AppKernel;
use Dontdrinkandroot\RestBundle\Tests\RestTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class FunctionalTestCase extends RestTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $fileSystem = new Filesystem();
        $fileSystem->remove('/tmp/ddrrestbundle');
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}

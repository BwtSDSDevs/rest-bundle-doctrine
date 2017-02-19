<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Tests\Functional\app\AppKernel;
use Dontdrinkandroot\RestBundle\Tests\RestTestCase;

abstract class FunctionalTestCase extends RestTestCase
{
    protected static function getKernelClass()
    {
        return 'Dontdrinkandroot\RestBundle\Tests\Functional\app\AppKernel';
    }

    protected static function createKernel(array $options = array())
    {
        return new AppKernel(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }
}

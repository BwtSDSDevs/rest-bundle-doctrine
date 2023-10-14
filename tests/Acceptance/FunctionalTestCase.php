<?php

namespace Dontdrinkandroot\RestBundle\Tests\Acceptance;

use Dontdrinkandroot\RestBundle\Tests\RestTestCase;

abstract class FunctionalTestCase extends RestTestCase
{
    public static function setUpBeforeClass(): void
    {
//        parent::setUpBeforeClass();
//
//        $fileSystem = new Filesystem();
//        $fileSystem->remove('/tmp/ddr_rest_bundle');
    }
}

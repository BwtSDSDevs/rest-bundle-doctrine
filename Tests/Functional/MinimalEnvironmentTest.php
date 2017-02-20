<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MinimalEnvironmentTest extends FunctionalTestCase
{
    protected $environment = 'minimal';

    public function testList()
    {
        $client = $this->makeClient();

        $client->request(
            Request::METHOD_GET,
            '/rest/minimalentities',
            [],
            [],
            []
        );
        $content = $this->assertJsonResponse($client->getResponse());
        $this->assertCount(0, $content);
    }

    public function testBla()
    {
        $client = $this->makeClient();

        $this->expectException(NotFoundHttpException::class);

        $client->request(
            Request::METHOD_GET,
            '/rest/test',
            [],
            [],
            []
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected static function getBundleClasses()
    {
        return [
            FrameworkBundle::class
        ];
    }
}

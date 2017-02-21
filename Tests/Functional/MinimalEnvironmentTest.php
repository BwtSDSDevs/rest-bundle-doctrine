<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\MinimalEntity;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\MinimalEntities;
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
        $this->assertCount(50, $content);
    }

    public function testGet()
    {
        $client = $this->makeClient();
        /** @var MinimalEntity $entity */
        $entity = $this->referenceRepository->getReference('minimal-entity-10');

        $client->request(Request::METHOD_GET, sprintf('/rest/minimalentities/%s', $entity->getId()));
        $content = $this->assertJsonResponse($client->getResponse());
        $this->assertEquals($entity->getId(), $content['id']);
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
        return [MinimalEntities::class];
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

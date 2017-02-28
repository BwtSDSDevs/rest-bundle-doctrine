<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\MinimalEntity;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\MinimalEntities;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
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
            ['page' => 2, 'perPage' => 10],
            [],
            []
        );
        $response = $client->getResponse();
        $content = $this->assertJsonResponse($response);
        $this->assertCount(10, $content);
        $this->assertEquals(10, $content[0]['integerValue']);

        $headers = $response->headers;
        $this->assertEquals(2, $headers->get('x-pagination-current-page'));
        $this->assertEquals(10, $headers->get('x-pagination-per-page'));
        $this->assertEquals(5, $headers->get('x-pagination-total-pages'));
        $this->assertEquals(49, $headers->get('x-pagination-total'));
    }

    public function testGet()
    {
        $client = $this->makeClient();
        /** @var MinimalEntity $entity */
        $entity = $this->referenceRepository->getReference('minimal-entity-10');

        $client->request(Request::METHOD_GET, sprintf('/rest/minimalentities/%s', $entity->getId()));
        $content = $this->assertJsonResponse($client->getResponse());
        $this->assertEquals($entity->getId(), $content['id']);
        $this->assertEquals(10, $content['integerValue']);
    }

    public function testPost()
    {
        $client = $this->makeClient();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request(
            Request::METHOD_POST,
            '/rest/minimalentities',
            [],
            [],
            [json_encode(['integerValue' => 33])]
        );
    }

    public function testPut()
    {
        $client = $this->makeClient();
        /** @var MinimalEntity $entity */
        $entity = $this->referenceRepository->getReference('minimal-entity-10');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request(
            Request::METHOD_PUT,
            sprintf('/rest/minimalentities/%s', $entity->getId()),
            [],
            [],
            [json_encode(['integerValue' => 33])]
        );
    }

    public function testDelete()
    {
        $client = $this->makeClient();
        /** @var MinimalEntity $entity */
        $entity = $this->referenceRepository->getReference('minimal-entity-10');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request(
            Request::METHOD_DELETE,
            sprintf('/rest/minimalentities/%s', $entity->getId())
        );
    }

    public function testUnmappedPath()
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
}

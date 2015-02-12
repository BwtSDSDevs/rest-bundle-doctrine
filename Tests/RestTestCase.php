<?php

namespace Dontdrinkandroot\RestBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

abstract class RestTestCase extends WebTestCase
{

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        /** @var ORMExecutor $executor */
        $executor = $this->loadFixtures($this->getFixtureClasses());
        $this->referenceRepository = $executor->getReferenceRepository();

        $this->client = static::createClient();
    }

    /**
     * @param Response $response
     * @param int      $statusCode
     *
     * @return array
     */
    protected function assertJsonResponse(Response $response, $statusCode = 200)
    {
        $content = $response->getContent();
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $content
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );

        return json_decode($content, true);
    }

    /**
     * @param string $route
     * @param string $method
     * @param array  $parameters
     * @param array  $headers
     * @param array  $files
     *
     * @return Response
     */
    protected function requestJson(
        $route,
        $method = 'GET',
        array $parameters = [],
        array $headers = [],
        array $files = []
    ) {
        $mergedHeaders = [
            'HTTP_ACCEPT' => 'application/json',
        ];
        foreach ($headers as $key => $value) {
            $mergedHeaders['HTTP_' . $key] = $value;
        }
        $this->client->request('GET', $route, $parameters, $files, $mergedHeaders);

        return $this->client->getResponse();
    }

    /**
     * @return string[]
     */
    protected abstract function getFixtureClasses();
}

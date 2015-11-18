<?php

namespace Dontdrinkandroot\RestBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
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
     * @param array $data
     */
    protected function assertLinksAndUnset(array &$data)
    {
        $this->assertArrayHasKey('_links', $data);
        unset($data['_links']);
    }

    /**
     * @deprecated
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     * @param array  $headers
     * @param array  $files
     *
     * @return Response
     */
    protected function requestJson(
        $url,
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
        $this->client->request($method, $url, $parameters, $files, $mergedHeaders);

        return $this->client->getResponse();
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     *
     * @return null|Response
     */
    protected function doGetCall($url, array $parameters = [], array $headers = [])
    {
        $this->client->request(
            Request::METHOD_GET,
            $url,
            $parameters,
            [],
            $this->transformHeaders($headers)
        );

        return $this->client->getResponse();
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array       $headers
     * @param string|null $content
     * @param array       $files
     *
     * @return null|Response
     */
    protected function doPostCall(
        $url,
        array $parameters = [],
        array $headers = [],
        $content = null,
        array $files = []
    ) {
        $this->client->request(
            Request::METHOD_POST,
            $url,
            $parameters,
            $files,
            $this->transformHeaders($headers),
            $content
        );

        return $this->client->getResponse();
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array       $headers
     * @param string|null $content
     *
     * @return null|Response
     */
    protected function doPutCall($url, array $parameters = [], array $headers = [], $content = null)
    {
        $this->client->request(
            Request::METHOD_PUT,
            $url,
            $parameters,
            [],
            $this->transformHeaders($headers),
            $content
        );

        return $this->client->getResponse();
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     *
     * @return null|Response
     */
    protected function doDeleteCall($url, array $parameters = [], array $headers = [])
    {
        $this->client->request(
            Request::METHOD_DELETE,
            $url,
            $parameters,
            [],
            $this->transformHeaders($headers)
        );

        return $this->client->getResponse();
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    protected function transformHeaders(array $headers)
    {
        $transformedHeaders = [
            'HTTP_ACCEPT'  => 'application/json',
            'CONTENT_TYPE' => 'application/json'
        ];
        foreach ($headers as $key => $value) {
            $transformedHeaders['HTTP_' . $key] = $value;
        }

        return $transformedHeaders;
    }

    /**
     * @return string[]
     */
    abstract protected function getFixtureClasses();
}

<?php

namespace Dontdrinkandroot\RestBundle\Tests;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class RestTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected ReferenceRepository $referenceRepository;

    protected function loadClientAndFixtures(array $fixtureClasses = [], string $environment = 'test')
    {
        $this->client = static::createClient(['environment' => $environment]);
        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->referenceRepository = $databaseToolCollection->get()->loadFixtures(
            $fixtureClasses
        )->getReferenceRepository();

        return $this->referenceRepository;
    }

    /**
     * @param Response $response
     * @param int      $statusCode
     *
     * @return array
     */
    protected function assertJsonResponse(Response $response, $statusCode = 200, $detailedOutput = true)
    {
        $content = $response->getContent();
        if (Response::HTTP_NO_CONTENT !== $statusCode) {
            $this->assertTrue(
                $response->headers->contains('Content-Type', 'application/json'),
                sprintf('JSON content type missing, given: %s', $response->headers->get('Content-Type'))
            );
        }

        $decodedContent = json_decode($content, true);
        if ($detailedOutput && $statusCode !== $response->getStatusCode()) {
            var_dump($decodedContent);
        }

        $this->assertEquals($statusCode, $response->getStatusCode());

        return $decodedContent;
    }

    protected function assertHasKeyAndUnset($key, array &$data, $notNull = true)
    {
        $this->assertArrayHasKey($key, $data);
        if ($notNull) {
            $this->assertNotNull($data[$key]);
        }
        unset($data[$key]);
    }

    /**
     * @param array $data
     */
    protected function assertLinksAndUnset(array &$data)
    {
        $this->assertHasKeyAndUnset('_links', $data);
    }

    /**
     * @param array $expected
     * @param array $actual
     * @param bool  $linksExpected
     */
    protected function assertContentEquals(array $expected, array $actual, $linksExpected = false)
    {
        if ($linksExpected) {
            $this->assertLinksAndUnset($actual);
        }
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual, 'The content does not match');
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     *
     * @return null|Response
     */
    protected function performGet(KernelBrowser $client, $url, array $parameters = [], array $headers = [])
    {
        $client->request(
            Request::METHOD_GET,
            $url,
            $parameters,
            [],
            $this->transformHeaders($headers)
        );

        return $client->getResponse();
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     * @param array  $content
     * @param array  $files
     *
     * @return null|Response
     */
    protected function performPost(
        KernelBrowser $client,
        $url,
        array $parameters = [],
        array $headers = [],
        array $content = [],
        array $files = []
    ) {
        $client->request(
            Request::METHOD_POST,
            $url,
            $parameters,
            $files,
            $this->transformHeaders($headers),
            json_encode($content)
        );

        return $client->getResponse();
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     * @param array  $content
     *
     * @return null|Response
     */
    protected function performPut(
        KernelBrowser $client,
        $url,
        array $parameters = [],
        array $headers = [],
        array $content = []
    ) {
        $client->request(
            Request::METHOD_PUT,
            $url,
            $parameters,
            [],
            $this->transformHeaders($headers),
            json_encode($content)
        );

        return $client->getResponse();
    }

    /**
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     *
     * @return null|Response
     */
    protected function performDelete(KernelBrowser $client, $url, array $parameters = [], array $headers = [])
    {
        $client->request(
            Request::METHOD_DELETE,
            $url,
            $parameters,
            [],
            $this->transformHeaders($headers)
        );

        return $client->getResponse();
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
     * @param Response $response
     * @param int      $page
     * @param int      $perPage
     * @param int      $totalPages
     * @param int      $total
     */
    protected function assertPagination($response, $page, $perPage, $totalPages, $total)
    {
        $headers = $response->headers;

        $this->assertEquals($page, $headers->get('x-pagination-current-page'));
        $this->assertEquals($perPage, $headers->get('x-pagination-per-page'));
        $this->assertEquals($totalPages, $headers->get('x-pagination-total-pages'));
        $this->assertEquals($total, $headers->get('x-pagination-total'));
    }
}

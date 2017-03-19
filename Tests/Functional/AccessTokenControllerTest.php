<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Security\AbstractAccessTokenAuthenticator;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class AccessTokenControllerTest extends FunctionalTestCase
{
    protected $environment = 'secured';

    public function testCreateToken()
    {
        $client = $this->makeClient();
        $response = $this->performPost(
            $client,
            '/rest/accesstokens',
            [],
            [],
            [
                'username' => 'employee1',
                'password' => 'employee1'
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertNotNull($content['token']);
        $this->assertNotNull($content['expiry']);
    }

    public function testListTokensUnauthenticated()
    {
        $client = $this->makeClient();
        $response = $this->performGet($client, 'rest/accesstokens');
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testListTokens()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');

        $client = $this->makeClient();
        $response = $this->performGet(
            $client,
            '/rest/accesstokens',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content);
        $this->assertEquals('user-user', $content[0]['token']);
    }

    public function testDeleteTokenUnauthenticated()
    {
        $client = $this->makeClient();
        $response = $this->performDelete(
            $client,
            '/rest/accesstokens/user-user'
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testDeleteNonExistingToken()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');

        $client = $this->makeClient();
        $response = $this->performDelete(
            $client,
            '/rest/accesstokens/asdfdasf',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NOT_FOUND);
    }

    public function testDeleteToken()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');
        $client = $this->makeClient();

        $response = $this->performDelete(
            $client,
            '/rest/accesstokens/' . $accessToken->getToken(),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        /* Cannot be called anymore as token is deleted */
        $response = $this->performGet(
            $client,
            '/rest/accesstokens',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return string[]
     */
    protected function getFixtureClasses()
    {
        return [Users::class];
    }
}

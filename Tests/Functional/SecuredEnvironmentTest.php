<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Security\AbstractAccessTokenAuthenticator;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\SecuredEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Symfony\Component\HttpFoundation\Response;

class SecuredEnvironmentTest extends FunctionalTestCase
{
    protected $environment = 'secured';

    public function testListUnauthorized()
    {
        $client = $this->makeClient();

        $response = $this->doGetCall($client, '/rest/secured');
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testList()
    {
        $client = $this->makeClient();

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');

        $response = $this->doGetCall(
            $client,
            '/rest/secured',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertCount(1, $content);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class, SecuredEntities::class];
    }
}

<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Security\AbstractAccessTokenAuthenticator;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\SecuredEntity;
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

    public function testGetUnauthorized()
    {
        $client = $this->makeClient();

        $entity = $this->referenceRepository->getReference('secured-entity-0');

        $response = $this->doGetCall(
            $client,
            sprintf('/rest/secured/%s', $entity->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testGet()
    {
        $client = $this->makeClient();

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');
        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-0');

        $response = $this->doGetCall(
            $client,
            sprintf('/rest/secured/%s', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);

        $expectedContent = [
            'id'            => $entity->getId(),
            'uuid'          => $entity->getUuid(),
            'dateTimeField' => '2015-03-04 13:12:11',
            'dateField'     => '2016-01-02',
            'timeField'     => '03:13:37'
        ];

        $this->assertContentEquals($expectedContent, $content, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class, SecuredEntities::class];
    }
}

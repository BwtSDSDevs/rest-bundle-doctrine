<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Security\AbstractAccessTokenAuthenticator;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\InheritedEntity;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\SecuredEntity;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\SubResourceEntity;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\InheritedEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\SecuredEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Symfony\Component\HttpFoundation\Response;

class SecuredEnvironmentTest extends FunctionalTestCase
{
    protected $environment = 'secured';

    public function testListUnauthorized()
    {
        $client = $this->makeClient();

        $response = $this->performGet($client, '/rest/secured');
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testList()
    {
        $client = $this->makeClient();

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');

        $response = $this->performGet(
            $client,
            '/rest/secured',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertCount(2, $content);
    }

    public function testGetUnauthorized()
    {
        $client = $this->makeClient();

        $entity = $this->referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
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

        $response = $this->performGet(
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

    public function testPutUnauthorized()
    {
        $client = $this->makeClient();

        $entity = $this->referenceRepository->getReference('secured-entity-0');

        /* No User */

        $response = $this->performPut(
            $client,
            sprintf('/rest/secured/%s', $entity->getId())
        );
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);

        /* Insufficient Privileges */

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');
        $response = $this->performPut(
            $client,
            sprintf('/rest/secured/%s', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $this->assertJsonResponse($response, Response::HTTP_FORBIDDEN);
    }

    public function testPut()
    {
        $client = $this->makeClient();

        $entity = $this->referenceRepository->getReference('secured-entity-0');

        $data = [
            'dateTimeField' => '2011-02-03 04:05:06',
            'dateField'     => '2012-05-31',
            'timeField'     => '12:34:56'
        ];

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');
        $response = $this->performPut(
            $client,
            sprintf('/rest/secured/%s', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()],
            $data
        );
        $content = $this->assertJsonResponse($response);

        $expectedContent = $data;
        $expectedContent['id'] = $entity->getId();
        $expectedContent['uuid'] = $entity->getUuid();

        $this->assertContentEquals($expectedContent, $content, false);
    }

    public function testGetWithSubResources()
    {
        $client = $this->makeClient();

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');
        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
            $client,
            sprintf('/rest/secured/%s', $entity->getId()),
            ['include' => 'subResources'],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertCount(5, $content['subResources']);
    }

    public function testListSubResources()
    {
        $client = $this->makeClient();

        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
            $client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            ['page' => 1, 'perPage' => 3]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(3, $content);
        $this->assertPagination($response, 1, 3, 2, 5);
    }

    public function testAddSubResource()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');

        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-1');

        /** @var SubResourceEntity $subResourceEntity */
        $subResourceEntity = $this->referenceRepository->getReference('subresource-entity-11');

        $client = $this->makeClient();

        $response = $this->performPut(
            $client,
            sprintf('/rest/secured/%s/subresources/%s', $entity->getId(), $subResourceEntity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $client,
            sprintf('/rest/secured/%s/subresources', $entity->getId())
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content);
    }

    public function testRemoveSubResource()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');

        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-0');

        /** @var SubResourceEntity $subResourceEntity */
        $subResourceEntity = $this->referenceRepository->getReference('subresource-entity-2');

        $client = $this->makeClient();

        $response = $this->performDelete(
            $client,
            sprintf('/rest/secured/%s/subresources/%s', $entity->getId(), $subResourceEntity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $client,
            sprintf('/rest/secured/%s/subresources', $entity->getId())
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(4, $content);
    }

    public function testSubResourcesList()
    {
        $client = $this->makeClient();
        $response = $this->performGet($client, '/rest/subresourceentities');
        $content = $this->assertJsonResponse($response);
        $this->assertCount(33, $content);
    }

    public function testGetInheritedEntity()
    {
        $client = $this->makeClient();

        /** @var InheritedEntity $entity */
        $entity = $this->referenceRepository->getReference(InheritedEntities::INHERITED_ENTITY_0);
        $response = $this->performGet($client, sprintf('/rest/inheritedentities/%s', $entity->getId()));

        $content = $this->assertJsonResponse($response);
        $this->assertContentEquals(['id' => $entity->getId(), 'excludedFieldTwo' => 'two'], $content, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class, SecuredEntities::class, InheritedEntities::class];
    }
}

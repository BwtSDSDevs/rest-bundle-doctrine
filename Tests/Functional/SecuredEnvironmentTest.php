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

    public function testPostUnauthorized()
    {
        $client = $this->makeClient();
        $response = $this->performPost($client, '/rest/secured');
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testPost()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');
        $client = $this->makeClient();

        $response = $this->performPost(
            $client,
            '/rest/secured',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()],
            [
                'integerField' => 23,
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertHasKeyAndUnset('id', $content, true);
        $this->assertHasKeyAndUnset('uuid', $content, true);
        $this->assertContentEquals(
            [
                'dateField'      => null,
                'dateTimeField'  => null,
                'embeddedEntity' => [
                    'fieldString'  => null,
                    'fieldInteger' => null
                ],
                'integerField'   => 23,
                'timeField'      => null,
            ],
            $content,
            false
        );
    }

    public function testPostInvalid()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');

        $client = $this->makeClient();
        $response = $this->performPost(
            $client,
            '/rest/secured',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()],
            ['integerField' => 'thisisnointeger']
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST, true);
        $this->assertContentEquals(
            [
                [
                    'propertyPath' => "integerField",
                    'message'      => "This value should be of type integer.",
                    'value'        => "thisisnointeger"
                ]
            ],
            $content,
            false
        );
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
            'id'             => $entity->getId(),
            'uuid'           => $entity->getUuid(),
            'dateTimeField'  => '2015-03-04 13:12:11',
            'dateField'      => '2016-01-02',
            'timeField'      => '03:13:37',
            'integerField'   => null,
            'embeddedEntity' => [
                'fieldString'  => null,
                'fieldInteger' => null
            ]
        ];

        $this->assertContentEquals($expectedContent, $content, false);
    }

    public function testDeleteUnauthorized()
    {
        $entity = $this->referenceRepository->getReference('secured-entity-0');
        $client = $this->makeClient();
        $response = $this->performDelete($client, sprintf('/rest/secured/%s', $entity->getId()));
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testDelete()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');
        $entity = $this->referenceRepository->getReference('secured-entity-0');
        $client = $this->makeClient();
        $response = $this->performDelete(
            $client,
            sprintf('/rest/secured/%s', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($client, sprintf('/rest/secured/%s', $entity->getId()));
        $this->assertJsonResponse($response, Response::HTTP_NOT_FOUND);
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
            'dateTimeField'  => '2011-02-03 04:05:06',
            'dateField'      => '2012-05-31',
            'timeField'      => '12:34:56',
            'embeddedEntity' => [
                'fieldString'  => 'haha',
                'fieldInteger' => 23
            ]
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
        $expectedContent['integerField'] = null;

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
            ['include' => 'subResources,subResources._links'],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertCount(5, $content['subResources']);
        $id = $content['subResources'][0]['id'];
        $this->assertEquals(
            sprintf('http://localhost/rest/subresourceentities/%s', $id),
            $content['subResources'][0]['_links']['self']['href']
        );
    }

    public function testListSubResources()
    {
        $client = $this->makeClient();

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');
        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
            $client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            ['page' => 1, 'perPage' => 3],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
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
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content);
    }

    public function testAddParent()
    {
        /** @var AccessToken $adminToken */
        $userToken = $this->referenceRepository->getReference('token-user-user');
        /** @var AccessToken $adminToken */
        $adminToken = $this->referenceRepository->getReference('token-user-admin');

        /** @var SecuredEntity $parent */
        $parent = $this->referenceRepository->getReference('secured-entity-1');

        /** @var SubResourceEntity $child */
        $child = $this->referenceRepository->getReference('subresource-entity-0');

        $client = $this->makeClient();

        $response = $this->performPut(
            $client,
            sprintf('/rest/subresourceentities/%s/parententity/%s', $child->getId(), $parent->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $adminToken->getToken()]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT, true);

        $response = $this->performGet(
            $client,
            sprintf('/rest/subresourceentities/%s', $child->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $userToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertContentEquals(
            [
                'id'           => $child->getId(),
                'parentEntity' => [
                    'id'   => $parent->getId(),
                    'uuid' => $parent->getUuid()
                ]
            ],
            $content,
            false
        );
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
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(4, $content);
    }

    public function testRemoveParent()
    {
        /** @var AccessToken $adminToken */
        $userToken = $this->referenceRepository->getReference('token-user-user');
        /** @var AccessToken $adminToken */
        $adminToken = $this->referenceRepository->getReference('token-user-admin');

        /** @var SecuredEntity $parent */
        $parent = $this->referenceRepository->getReference('secured-entity-1');

        /** @var SubResourceEntity $child */
        $child = $this->referenceRepository->getReference('subresource-entity-2');

        $client = $this->makeClient();

        $response = $this->performDelete(
            $client,
            sprintf('/rest/subresourceentities/%s/parententity', $child->getId(), $parent->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $adminToken->getToken()]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT, true);

        $response = $this->performGet(
            $client,
            sprintf('/rest/subresourceentities/%s', $child->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $userToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertContentEquals(
            [
                'id'           => $child->getId(),
                'parentEntity' => null
            ],
            $content,
            false
        );
    }

    public function testSubResourcesListUnauthorized()
    {
        $client = $this->makeClient();
        $response = $this->performGet($client, '/rest/subresourceentities');
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testSubResourcesList()
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');
        $client = $this->makeClient();
        $response = $this->performGet(
            $client,
            '/rest/subresourceentities',
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(33, $content);
    }

    public function testPostSubresourceUnauthorized()
    {
        $client = $this->makeClient();

        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-1');

        $response = $this->performPost($client, sprintf('/rest/secured/%s/subresources', $entity->getId()));
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testPostSubresource()
    {
        $client = $this->makeClient();

        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');

        /** @var SecuredEntity $entity */
        $entity = $this->referenceRepository->getReference('secured-entity-1');

        $response = $this->performPost(
            $client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertNotNull($content['id']);
        $this->assertNotNull($content['parentEntity']);
        $this->assertEquals($entity->getId(), $content['parentEntity']['id']);
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
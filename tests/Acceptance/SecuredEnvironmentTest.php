<?php

namespace Niebvelungen\RestBundleDoctrine\Tests\Acceptance;

use Niebvelungen\RestBundleDoctrine\Tests\TestApp\DataFixtures\InheritedEntities;
use Niebvelungen\RestBundleDoctrine\Tests\TestApp\DataFixtures\SecuredEntities;
use Niebvelungen\RestBundleDoctrine\Tests\TestApp\DataFixtures\Users;
use Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity\InheritedEntity;
use Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity\SecuredEntity;
use Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity\SubResourceEntity;
use Symfony\Component\HttpFoundation\Response;

class SecuredEnvironmentTest extends FunctionalTestCase
{
    public function testListUnauthorized(): void
    {
        $this->client = self::createClient(['environment' => 'secured']);

        $response = $this->performGet($this->client, '/rest/secured');
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testList(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        $response = $this->performGet(
            $this->client,
            '/rest/secured',
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertCount(2, $content);
    }

    public function testPostUnauthorized(): void
    {
        $this->client = self::createClient(['environment' => 'secured']);
        $response = $this->performPost($this->client, '/rest/secured');
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testPost(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');

        $response = $this->performPost(
            $this->client,
            '/rest/secured',
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ],
            [
                'integerField' => 23,
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertHasKeyAndUnset('id', $content, true);
        $this->assertHasKeyAndUnset('uuid', $content, true);
        $this->assertContentEquals(
            [
                'dateField' => null,
                'dateTimeField' => null,
                'embeddedEntity' => [
                    'fieldString' => null,
                    'fieldInteger' => null
                ],
                'integerField' => 23,
                'timeField' => null,
            ],
            $content
        );
    }

    public function testPostInvalid()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');

        $response = $this->performPost(
            $this->client,
            '/rest/secured',
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ],
            ['integerField' => 'thisisnointeger']
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST);
        $this->assertContentEquals(
            [
                [
                    'propertyPath' => "integerField",
                    'message' => "This value should be of type integer.",
                    'value' => "thisisnointeger"
                ]
            ],
            $content
        );
    }

    public function testGetUnauthorized(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([SecuredEntities::class], 'secured');

        $entity = $referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/secured/%s', $entity->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testGet()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/secured/%s', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
        );
        $content = $this->assertJsonResponse($response);

        $expectedContent = [
            'id' => $entity->getId(),
            'uuid' => $entity->getUuid(),
            'dateTimeField' => '2015-03-04 13:12:11',
            'dateField' => '2016-01-02',
            'timeField' => '03:13:37',
            'integerField' => null,
            'embeddedEntity' => [
                'fieldString' => null,
                'fieldInteger' => null
            ]
        ];

        $this->assertContentEquals($expectedContent, $content, false);
    }

    public function testDeleteUnauthorized()
    {
        $referenceRepository = $this->loadClientAndFixtures([SecuredEntities::class], 'secured');
        $entity = $referenceRepository->getReference('secured-entity-0');
        $response = $this->performDelete($this->client, sprintf('/rest/secured/%s', $entity->getId()));
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testDelete()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');
        $entity = $referenceRepository->getReference('secured-entity-0');
        $entityId = $entity->getId();
        $response = $this->performDelete(
            $this->client,
            sprintf('/rest/secured/%s', $entityId),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($this->client, sprintf('/rest/secured/%s', $entityId));
        $this->assertJsonResponse($response, Response::HTTP_NOT_FOUND);
    }

    public function testPutUnauthorized()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        $entity = $referenceRepository->getReference('secured-entity-0');

        /* No User */

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/secured/%s', $entity->getId())
        );
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);

        /* Insufficient Privileges */

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/secured/%s', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
        );
        $this->assertJsonResponse($response, Response::HTTP_FORBIDDEN);
    }

    public function testPut()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        $entity = $referenceRepository->getReference('secured-entity-0');

        $data = [
            'dateTimeField' => '2011-02-03 04:05:06',
            'dateField' => '2012-05-31',
            'timeField' => '12:34:56',
            'embeddedEntity' => [
                'fieldString' => 'haha',
                'fieldInteger' => 23
            ]
        ];

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/secured/%s', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ],
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
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/secured/%s', $entity->getId()),
            ['include' => 'subResources,subResources._links'],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
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
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-0');

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            ['page' => 1, 'perPage' => 3],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(3, $content);
        $this->assertPagination($response, 1, 3, 2, 5);
    }

    public function testAddSubResource()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');
        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-1');

        /** @var SubResourceEntity $subResourceEntity */
        $subResourceEntity = $referenceRepository->getReference('subresource-entity-11');

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/secured/%s/subresources/%s', $entity->getId(), $subResourceEntity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content);
    }

    public function testAddParent()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');
        /** @var SecuredEntity $parent */
        $parent = $referenceRepository->getReference('secured-entity-1');

        /** @var SubResourceEntity $child */
        $child = $referenceRepository->getReference('subresource-entity-0');

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/subresourceentities/%s/parententity/%s', $child->getId(), $parent->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT, true);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/subresourceentities/%s', $child->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertContentEquals(
            [
                'id' => $child->getId(),
                'parentEntity' => [
                    'id' => $parent->getId(),
                    'uuid' => $parent->getUuid()
                ],
                'text' => null
            ],
            $content,
            false
        );
    }

    public function testRemoveSubResource()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-0');

        /** @var SubResourceEntity $subResourceEntity */
        $subResourceEntity = $referenceRepository->getReference('subresource-entity-2');

        $response = $this->performDelete(
            $this->client,
            sprintf('/rest/secured/%s/subresources/%s', $entity->getId(), $subResourceEntity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(4, $content);
    }

    public function testRemoveParent()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        /** @var SecuredEntity $parent */
        $parent = $referenceRepository->getReference('secured-entity-1');

        /** @var SubResourceEntity $child */
        $child = $referenceRepository->getReference('subresource-entity-2');

        $response = $this->performDelete(
            $this->client,
            sprintf('/rest/subresourceentities/%s/parententity', $child->getId(), $parent->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT, true);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/subresourceentities/%s', $child->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
        );
        $content = $this->assertJsonResponse($response);

        $this->assertContentEquals(
            [
                'id' => $child->getId(),
                'parentEntity' => null,
                'text' => null
            ],
            $content,
            false
        );
    }

    public function testSubResourcesListUnauthorized()
    {
        $this->client = self::createClient(['environment' => 'secured']);
        $response = $this->performGet($this->client, '/rest/subresourceentities');
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testSubResourcesList()
    {
        $this->client = self::createClient(['environment' => 'secured']);
        $response = $this->performGet(
            $this->client,
            '/rest/subresourceentities',
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(33, $content);
    }

    public function testPostSubresourceUnauthorized()
    {
        $referenceRepository = $this->loadClientAndFixtures([SecuredEntities::class], 'secured');

        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-1');

        $response = $this->performPost($this->client, sprintf('/rest/secured/%s/subresources', $entity->getId()));
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testPostSubresource()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-1');

        $response = $this->performPost(
            $this->client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ],
            ['text' => 'TestText']
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertHasKeyAndUnset('id', $content);
        $this->assertContentEquals(
            [
                'parentEntity' => [
                    'id' => $entity->getId(),
                    'uuid' => $entity->getUuid()
                ],
                'text' => 'TestText'
            ],
            $content,
            false
        );
    }

    public function testPostSubresourceAsForm()
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SecuredEntities::class], 'secured');

        /** @var SecuredEntity $entity */
        $entity = $referenceRepository->getReference('secured-entity-1');

        $response = $this->performPost(
            $this->client,
            sprintf('/rest/secured/%s/subresources', $entity->getId()),
            ['text' => 'TestText'],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertHasKeyAndUnset('id', $content);
        $this->assertContentEquals(
            [
                'parentEntity' => [
                    'id' => $entity->getId(),
                    'uuid' => $entity->getUuid()
                ],
                'text' => 'TestText'
            ],
            $content,
            false
        );
    }

    public function testGetInheritedEntity()
    {
        $referenceRepository = $this->loadClientAndFixtures([InheritedEntities::class], 'secured');

        /** @var InheritedEntity $entity */
        $entity = $referenceRepository->getReference(InheritedEntities::INHERITED_ENTITY_0);
        $response = $this->performGet($this->client, sprintf('/rest/inheritedentities/%s', $entity->getId()));

        $content = $this->assertJsonResponse($response);
        $this->assertContentEquals(['id' => $entity->getId(), 'excludedFieldTwo' => 'two'], $content, false);
    }
}

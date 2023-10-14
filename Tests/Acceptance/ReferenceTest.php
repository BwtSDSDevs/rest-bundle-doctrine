<?php

namespace Dontdrinkandroot\RestBundle\Tests\Acceptance;

use Dontdrinkandroot\RestBundle\Tests\TestApp\DataFixtures\SubResourceEntities;
use Dontdrinkandroot\RestBundle\Tests\TestApp\DataFixtures\Users;
use Symfony\Component\HttpFoundation\Response;

class ReferenceTest extends FunctionalTestCase
{
    public function testPostByReference(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');

        $creator = $referenceRepository->getReference(Users::EMPLOYEE_1);

        $response = $this->performPost(
            $this->client,
            '/rest/subresourceentities',
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ],
            [
                'creator' => [
                    'id' => $creator->getId(),
                ]
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertHasKeyAndUnset('id', $content, true);
        $this->assertContentEquals(
            [
                'creator' => [
                    'id'       => $creator->getId(),
                    'username' => 'employee1',
                    'roles'    => [
                        'ROLE_USER'
                    ]
                ],
                'text'    => null
            ],
            $content
        );
    }

    public function testPutByReference(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class, SubResourceEntities::class], 'secured');

        $creator = $referenceRepository->getReference(Users::EMPLOYEE_1);
        $entity = $referenceRepository->getReference('subresource-entity-0');

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/subresourceentities/%s', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ],
            [
                'creator' => [
                    'id' => $creator->getId(),
                ]
            ]
        );
        $content = $this->assertJsonResponse($response);
        $this->assertHasKeyAndUnset('id', $content, true);
        $this->assertContentEquals(
            [
                'creator' => [
                    'id'       => $creator->getId(),
                    'username' => 'employee1',
                    'roles'    => [
                        'ROLE_USER'
                    ]
                ],
                'text'    => null
            ],
            $content
        );
    }
}

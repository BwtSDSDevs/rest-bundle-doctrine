<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Security\AbstractAccessTokenAuthenticator;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\SubResourceEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Symfony\Component\HttpFoundation\Response;

class ReferenceTest extends FunctionalTestCase
{
    protected $environment = 'secured';

    public function testPostByReference()
    {
        $creator = $this->referenceRepository->getReference(Users::EMPLOYEE_1);

        $client = $this->makeClient(
            false,
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ]
        );

        $response = $this->performPost(
            $client,
            '/rest/subresourceentities',
            [],
            [],
            [
                'creator' => [
                    'id' => $creator->getId(),
                ]
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED, true);
        $this->assertHasKeyAndUnset('id', $content, true);
        $this->assertContentEquals(
            [
                'creator' => [
                    'id'       => $creator->getId(),
                    'username' => 'employee1',
                    'roles'    => [
                        'ROLE_USER'
                    ]
                ]
            ],
            $content
        );
    }

    public function testPutByReference()
    {
        $creator = $this->referenceRepository->getReference(Users::EMPLOYEE_1);
        $entity = $this->referenceRepository->getReference('subresource-entity-0');

        $client = $this->makeClient(
            false,
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ]
        );

        $response = $this->performPut(
            $client,
            sprintf('/rest/subresourceentities/%s', $entity->getId()),
            [],
            [],
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
                ]
            ],
            $content
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class, SubResourceEntities::class];
    }
}

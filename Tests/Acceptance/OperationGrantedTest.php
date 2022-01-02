<?php

namespace Acceptance;

use Dontdrinkandroot\RestBundle\Tests\Acceptance\FunctionalTestCase;
use Dontdrinkandroot\RestBundle\Tests\TestApp\DataFixtures\Users;
use Symfony\Component\HttpFoundation\Response;

class OperationGrantedTest extends FunctionalTestCase
{
    public function testUnauthorized(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');
        $this->performPost(
            $this->client,
            '/rest/users',
            [],
            [],
            [
                'username' => 'username',
                'role'     => 'ROLE_ADMIN'
            ]
        );
        $response = $this->client->getResponse();
        $content = $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testForbidden(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');
        $this->performPost(
            $this->client,
            '/rest/users',
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ],
            [
                'username' => 'username',
                'role'     => 'ROLE_ADMIN'
            ]
        );
        $response = $this->client->getResponse();
        $content = $this->assertJsonResponse($response, Response::HTTP_FORBIDDEN);
    }

    public function testGranted(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');
        $this->performPost(
            $this->client,
            '/rest/users',
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ],
            [
                'username' => 'username',
                'role'     => 'ROLE_ADMIN'
            ]
        );
        $response = $this->client->getResponse();
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED);
    }
}

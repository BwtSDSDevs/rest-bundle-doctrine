<?php

namespace Dontdrinkandroot\RestBundle\Tests\Acceptance;

use Dontdrinkandroot\RestBundle\Tests\TestApp\DataFixtures\Groups;
use Dontdrinkandroot\RestBundle\Tests\TestApp\DataFixtures\Users;
use Dontdrinkandroot\RestBundle\Tests\TestApp\Entity\Group;
use Dontdrinkandroot\RestBundle\Tests\TestApp\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class AssociationTest extends FunctionalTestCase
{
    public function testAddManyToOne()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var User $supervisor */
        $supervisor = $referenceRepository->getReference(Users::SUPERVISOR);
        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_2);

        /* Also Testing for Virtual/Includable roles */
        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'roles']);
        $content = $this->assertJsonResponse($response, 200, true);
        $this->assertNull($content['supervisor']);

        $this->assertNotNull($content['roles']);
        $this->assertCount(1, $content['roles']);
        $this->assertEquals('ROLE_USER', $content['roles'][0]);

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/users/%s/supervisor/%s', $user->getId(), $supervisor->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()));
        $content = $this->assertJsonResponse($response);
        $this->assertNotNull($content['supervisor']);
        $this->assertEquals($supervisor->getId(), $content['supervisor']['id']);
    }

    public function testRemoveManyToOne()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_1);

        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()));
        $content = $this->assertJsonResponse($response);
        $this->assertNotNull($content['supervisor']);

        $response = $this->performDelete(
            $this->client,
            sprintf('/rest/users/%s/supervisor', $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()));
        $content = $this->assertJsonResponse($response);
        $this->assertNull($content['supervisor']);
    }

    public function testAddOneToMany()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var User $supervisor */
        $supervisor = $referenceRepository->getReference(Users::SUPERVISOR);
        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_2);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['subordinates']);

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/users/%s/subordinates/%s', $supervisor->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(2, $content['subordinates']);
    }

    public function testRemoveOneToMany()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var User $supervisor */
        $supervisor = $referenceRepository->getReference(Users::SUPERVISOR);
        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_1);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['subordinates']);

        $response = $this->performDelete(
            $this->client,
            sprintf('/rest/users/%s/subordinates/%s', $supervisor->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $this->client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['subordinates']);
    }

    public function testAddManyToManyOwning()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var Group $group */
        $group = $referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_2);

        $response = $this->performGet($this->client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['users']);

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/groups/%s/users/%s', $group->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($this->client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(2, $content['users']);
    }

    public function testRemoveManyToManyOwning()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var Group $group */
        $group = $referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_1);

        $response = $this->performGet($this->client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['users']);

        $response = $this->performDelete(
            $this->client,
            sprintf('/rest/groups/%s/users/%s', $group->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($this->client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['users']);
    }

    public function testAddManyToManyInverse()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var Group $group */
        $group = $referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_2);

        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['groups']);

        $response = $this->performPut(
            $this->client,
            sprintf('/rest/users/%s/groups/%s', $user->getId(), $group->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['groups']);
    }

    public function testRemoveManyToManyInverse()
    {
        $referenceRepository = $this->loadClientAndFixtures([Groups::class], 'secured');

        /** @var Group $group */
        $group = $referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $referenceRepository->getReference(Users::EMPLOYEE_1);

        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['groups']);

        $response = $this->performDelete(
            $this->client,
            sprintf('/rest/users/%s/groups/%s', $user->getId(), $group->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($this->client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['groups']);
    }
}

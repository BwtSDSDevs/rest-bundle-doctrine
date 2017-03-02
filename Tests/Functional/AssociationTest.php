<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\Group;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\User;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Groups;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Symfony\Component\HttpFoundation\Response;

class AssociationTest extends FunctionalTestCase
{
    protected $environment = 'minimal';

    public function testAddManyToOne()
    {
        /** @var User $supervisor */
        $supervisor = $this->referenceRepository->getReference(Users::SUPERVISOR);
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_2);
        $client = $this->makeClient();

        /* Also Testing for Virtual/Includable roles */
        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'roles']);
        $content = $this->assertJsonResponse($response);
        $this->assertNull($content['supervisor']);

        $this->assertNotNull($content['roles']);
        $this->assertCount(1, $content['roles']);
        $this->assertEquals('ROLE_USER', $content['roles'][0]);

        $response = $this->performPut(
            $client,
            sprintf('/rest/users/%s/supervisor/%s', $user->getId(), $supervisor->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()));
        $content = $this->assertJsonResponse($response);
        $this->assertNotNull($content['supervisor']);
        $this->assertEquals($supervisor->getId(), $content['supervisor']['id']);
    }

    public function testRemoveManyToOne()
    {
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_1);
        $client = $this->makeClient();

        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()));
        $content = $this->assertJsonResponse($response);
        $this->assertNotNull($content['supervisor']);

        $response = $this->performDelete(
            $client,
            sprintf('/rest/users/%s/supervisor', $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()));
        $content = $this->assertJsonResponse($response);
        $this->assertNull($content['supervisor']);
    }

    public function testAddOneToMany()
    {
        /** @var User $supervisor */
        $supervisor = $this->referenceRepository->getReference(Users::SUPERVISOR);
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_2);
        $client = $this->makeClient();

        $response = $this->performGet(
            $client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['subordinates']);

        $response = $this->performPut(
            $client,
            sprintf('/rest/users/%s/subordinates/%s', $supervisor->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(2, $content['subordinates']);
    }

    public function testRemoveOneToMany()
    {
        /** @var User $supervisor */
        $supervisor = $this->referenceRepository->getReference(Users::SUPERVISOR);
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_1);
        $client = $this->makeClient();

        $response = $this->performGet(
            $client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['subordinates']);

        $response = $this->performDelete(
            $client,
            sprintf('/rest/users/%s/subordinates/%s', $supervisor->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet(
            $client,
            sprintf('/rest/users/%s', $supervisor->getId()),
            ['include' => 'subordinates']
        );
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['subordinates']);
    }

    public function testAddManyToManyOwning()
    {
        /** @var Group $group */
        $group = $this->referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_2);

        $client = $this->makeClient();
        $response = $this->performGet($client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['users']);

        $response = $this->performPut(
            $client,
            sprintf('/rest/groups/%s/users/%s', $group->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(2, $content['users']);
    }

    public function testRemoveManyToManyOwning()
    {
        /** @var Group $group */
        $group = $this->referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_1);

        $client = $this->makeClient();
        $response = $this->performGet($client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['users']);

        $response = $this->performDelete(
            $client,
            sprintf('/rest/groups/%s/users/%s', $group->getId(), $user->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($client, sprintf('/rest/groups/%s', $group->getId()), ['include' => 'users']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['users']);
    }

    public function testAddManyToManyInverse()
    {
        /** @var Group $group */
        $group = $this->referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_2);

        $client = $this->makeClient();
        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['groups']);

        $response = $this->performPut(
            $client,
            sprintf('/rest/users/%s/groups/%s', $user->getId(), $group->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['groups']);
    }

    public function testRemoveManyToManyInverse()
    {
        /** @var Group $group */
        $group = $this->referenceRepository->getReference(Groups::EMPLOYEES);
        /** @var User $user */
        $user = $this->referenceRepository->getReference(Users::EMPLOYEE_1);

        $client = $this->makeClient();
        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(1, $content['groups']);

        $response = $this->performDelete(
            $client,
            sprintf('/rest/users/%s/groups/%s', $user->getId(), $group->getId())
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_NO_CONTENT);

        $response = $this->performGet($client, sprintf('/rest/users/%s', $user->getId()), ['include' => 'groups']);
        $content = $this->assertJsonResponse($response);
        $this->assertCount(0, $content['groups']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Groups::class];
    }
}

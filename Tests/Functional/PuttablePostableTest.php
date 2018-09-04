<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Dontdrinkandroot\RestBundle\Security\AbstractAccessTokenAuthenticator;
use Dontdrinkandroot\RestBundle\Tests\Functional\FunctionalTestCase;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\PuttablePostableAnnotationEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Symfony\Component\HttpFoundation\Response;

class PuttablePostableTest extends FunctionalTestCase
{
    protected $environment = 'secured';

    public function testPutAnon()
    {
        $referenceRepository = $this->loadFixtures([PuttablePostableAnnotationEntities::class])->getReferenceRepository(
        );

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $client = $this->makeClient();
        $response = $this->performPut(
            $client,
            sprintf('/rest/puttablepostableannotationentities/%s', $entity->getId()),
            [],
            [],
            [
                'puttableByAll'   => 'changedPuttableByAll',
                'puttableByUser'  => 'changedPuttableByUser',
                'puttableByAdmin' => 'changedPuttableByAdmin',
                'postableByAll'   => 'changedPostableByAll',
                'postableByUser'  => 'changedPostableByUser',
                'postableByAdmin' => 'changedPostableByAdmin',
            ]
        );
        $content = $this->assertJsonResponse($response, 200, true);
        $this->assertContentEquals(
            [
                'id'              => $entity->getId(),
                'puttableByAll'   => 'changedPuttableByAll',
                'puttableByUser'  => 'puttableByUser',
                'puttableByAdmin' => 'puttableByAdmin',
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => 'postableByAdmin',
            ],
            $content
        );
    }

    public function testPostAnon()
    {
        $referenceRepository = $this->loadFixtures([PuttablePostableAnnotationEntities::class])->getReferenceRepository(
        );
        $client = $this->makeClient();
        $response = $this->performPost(
            $client,
            sprintf('/rest/puttablepostableannotationentities'),
            [],
            [],
            [
                'puttableByAll'   => 'puttableByAll',
                'puttableByUser'  => 'puttableByUser',
                'puttableByAdmin' => 'puttableByAdmin',
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => 'postableByAdmin',
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED, true);
        $this->assertHasKeyAndUnset('id', $content);
        $this->assertContentEquals(
            [
                'puttableByAdmin' => null,
                'puttableByAll'   => null,
                'puttableByUser'  => null,
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => null,
                'postableByAdmin' => null,
            ],
            $content
        );
    }

    public function testPutUser()
    {
        $referenceRepository = $this->loadFixtures(
            [Users::class, PuttablePostableAnnotationEntities::class]
        )->getReferenceRepository();

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $client = $this->makeClient(
            false,
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );
        $response = $this->performPut(
            $client,
            sprintf('/rest/puttablepostableannotationentities/%s', $entity->getId()),
            [],
            [],
            [
                'puttableByAll'   => 'changedPuttableByAll',
                'puttableByUser'  => 'changedPuttableByUser',
                'puttableByAdmin' => 'changedPuttableByAdmin',
                'postableByAll'   => 'changedPostableByAll',
                'postableByUser'  => 'changedPostableByUser',
                'postableByAdmin' => 'changedPostableByAdmin',
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_OK, true);
        $this->assertContentEquals(
            [
                'id'              => $entity->getId(),
                'puttableByAll'   => 'changedPuttableByAll',
                'puttableByUser'  => 'changedPuttableByUser',
                'puttableByAdmin' => 'puttableByAdmin',
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => 'postableByAdmin',
            ],
            $content
        );
    }

    public function testPostUser()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeClient(
            false,
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ]
        );

        $response = $this->performPost(
            $client,
            sprintf('/rest/puttablepostableannotationentities'),
            [],
            [],
            [
                'puttableByAll'   => 'puttableByAll',
                'puttableByUser'  => 'puttableByUser',
                'puttableByAdmin' => 'puttableByAdmin',
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => 'postableByAdmin',
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED, true);
        $this->assertHasKeyAndUnset('id', $content);
        $this->assertContentEquals(
            [
                'puttableByAdmin' => null,
                'puttableByAll'   => null,
                'puttableByUser'  => null,
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => null,
            ],
            $content
        );
    }

    public function testPutAdmin()
    {
        $referenceRepository = $this->loadFixtures(
            [Users::class, PuttablePostableAnnotationEntities::class]
        )->getReferenceRepository();

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $client = $this->makeClient(
            false,
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ]
        );
        $response = $this->performPut(
            $client,
            sprintf('/rest/puttablepostableannotationentities/%s', $entity->getId()),
            [],
            [],
            [
                'puttableByAll'   => 'changedPuttableByAll',
                'puttableByUser'  => 'changedPuttableByUser',
                'puttableByAdmin' => 'changedPuttableByAdmin',
                'postableByAll'   => 'changedPostableByAll',
                'postableByUser'  => 'changedPostableByUser',
                'postableByAdmin' => 'changedPostableByAdmin',
            ]
        );
        $content = $this->assertJsonResponse($response, 200, true);
        $this->assertContentEquals(
            [
                'id'              => $entity->getId(),
                'puttableByAll'   => 'changedPuttableByAll',
                'puttableByUser'  => 'changedPuttableByUser',
                'puttableByAdmin' => 'changedPuttableByAdmin',
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => 'postableByAdmin',
            ],
            $content
        );
    }

    public function testPostAdmin()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();

        $client = $this->makeClient(
            false,
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ]
        );

        $response = $this->performPost(
            $client,
            sprintf('/rest/puttablepostableannotationentities'),
            [],
            [],
            [
                'puttableByAll'   => 'puttableByAll',
                'puttableByUser'  => 'puttableByUser',
                'puttableByAdmin' => 'puttableByAdmin',
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => 'postableByAdmin',
            ]
        );
        $content = $this->assertJsonResponse($response, Response::HTTP_CREATED, true);
        $this->assertHasKeyAndUnset('id', $content);
        $this->assertContentEquals(
            [
                'puttableByAdmin' => null,
                'puttableByAll'   => null,
                'puttableByUser'  => null,
                'postableByAll'   => 'postableByAll',
                'postableByUser'  => 'postableByUser',
                'postableByAdmin' => 'postableByAdmin',
            ],
            $content
        );
    }
}

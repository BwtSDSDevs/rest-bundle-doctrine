<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Dontdrinkandroot\RestBundle\Tests\Functional\DataFixtures\PuttablePostableAnnotationEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\DataFixtures\Users;
use Dontdrinkandroot\RestBundle\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class PuttablePostableTest extends FunctionalTestCase
{
    public function testPutAnon()
    {
        $referenceRepository = $this->loadClientAndFixtures([PuttablePostableAnnotationEntities::class], 'secured');

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $response = $this->performPut(
            $this->client,
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
        $referenceRepository = $this->loadClientAndFixtures([PuttablePostableAnnotationEntities::class], 'secured');
        $response = $this->performPost(
            $this->client,
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
        $referenceRepository = $this->loadClientAndFixtures(
            [Users::class, PuttablePostableAnnotationEntities::class],
            'secured'
        );

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $response = $this->performPut(
            $this->client,
            sprintf('/rest/puttablepostableannotationentities/%s', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ],
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
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');

        $response = $this->performPost(
            $this->client,
            sprintf('/rest/puttablepostableannotationentities'),
            [],
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW'   => 'user',
            ],
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
        $referenceRepository = $this->loadClientAndFixtures(
            [Users::class, PuttablePostableAnnotationEntities::class],
            'secured'
        );

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $response = $this->performPut(
            $this->client,
            sprintf('/rest/puttablepostableannotationentities/%s', $entity->getId()),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ],
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
        $referenceRepository = $this->loadClientAndFixtures([Users::class], 'secured');

        $response = $this->performPost(
            $this->client,
            sprintf('/rest/puttablepostableannotationentities'),
            [],
            [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ],
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

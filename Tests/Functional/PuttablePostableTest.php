<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Dontdrinkandroot\RestBundle\Security\AbstractAccessTokenAuthenticator;
use Dontdrinkandroot\RestBundle\Tests\Functional\FunctionalTestCase;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\PuttablePostableAnnotationEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Symfony\Component\HttpFoundation\Response;

class AssociationTest extends FunctionalTestCase
{
    protected $environment = 'secured';

    public function testPutAnon()
    {
        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $this->referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
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
        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $this->referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $client = $this->makeClient();
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $this->referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $client = $this->makeClient();
        $response = $this->performPut(
            $client,
            sprintf('/rest/puttablepostableannotationentities/%s', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()],
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
        $client = $this->makeClient();
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-user');

        $response = $this->performPost(
            $client,
            sprintf('/rest/puttablepostableannotationentities'),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()],
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
        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $this->referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $client = $this->makeClient();
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');

        /** @var PuttablePostableAnnotationEntity $entity */
        $entity = $this->referenceRepository->getReference(PuttablePostableAnnotationEntities::PUTTABLE_POSTABLE_1);
        $client = $this->makeClient();
        $response = $this->performPut(
            $client,
            sprintf('/rest/puttablepostableannotationentities/%s', $entity->getId()),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()],
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
        $client = $this->makeClient();
        /** @var AccessToken $accessToken */
        $accessToken = $this->referenceRepository->getReference('token-user-admin');

        $response = $this->performPost(
            $client,
            sprintf('/rest/puttablepostableannotationentities'),
            [],
            [AbstractAccessTokenAuthenticator::DEFAULT_TOKEN_HEADER_NAME => $accessToken->getToken()],
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

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class, PuttablePostableAnnotationEntities::class];
    }
}

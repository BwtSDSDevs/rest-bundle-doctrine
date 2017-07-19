<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Dontdrinkandroot\RestBundle\Service\AccessTokenService;
use Dontdrinkandroot\RestBundle\Tests\Functional\app\AppKernel;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\User;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class AccessTokenServiceTest extends WebTestCase
{
    protected $environment = 'secured';

    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $fileSystem = new Filesystem();
        $fileSystem->remove('/tmp/ddrrestbundle');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        /** @var ORMExecutor $executor */
        $executor = $this->loadFixtures([Users::class]);
        $this->referenceRepository = $executor->getReferenceRepository();
    }

    public function testCleanUpAccessTokensCommand()
    {
        $output = $this->runCommand('ddr:rest:access-token:cleanup');
        $this->assertEquals('1 tokens removed', trim($output));
    }

    public function testCleanUpExpiredTokens()
    {
        /** @var User $user */
        $user = $this->referenceRepository->getReference('user-user');
        /** @var AccessTokenService $accessTokenService */
        $accessTokenService = $this->getContainer()->get('ddr_rest.service.access_token');

        $accessTokens = $accessTokenService->listByUser($user);
        $this->assertCount(2, $accessTokens);

        $accessTokenService->cleanUpExpiredTokens();

        $accessTokens = $accessTokenService->listByUser($user);
        $this->assertCount(1, $accessTokens);
    }

    public function testGetExpiredToken()
    {
        /** @var User $user */
        $user = $this->referenceRepository->getReference('user-user');
        /** @var AccessTokenService $accessTokenService */
        $accessTokenService = $this->getContainer()->get('ddr_rest.service.access_token');

        $accessTokens = $accessTokenService->listByUser($user);
        $this->assertCount(2, $accessTokens);

        $accessTokenService->findUserByToken('user-user-expired');

        $accessTokens = $accessTokenService->listByUser($user);
        $this->assertCount(1, $accessTokens);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}

<?php

namespace Dontdrinkandroot\RestBundle\Security;

use Dontdrinkandroot\RestBundle\Repository\AccessTokenRepository;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DefaultAccessTokenAuthenticator extends AbstractAccessTokenAuthenticator
{
    /**
     * @var AccessTokenRepository
     */
    private $repository;

    public function __construct(AccessTokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function findUserByToken($token)
    {
        return $this->repository->findUserByToken($token);
    }
}

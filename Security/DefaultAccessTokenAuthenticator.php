<?php

namespace Dontdrinkandroot\RestBundle\Security;

use Dontdrinkandroot\RestBundle\Repository\AccessTokenRepositoryInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DefaultAccessTokenAuthenticator extends AbstractAccessTokenAuthenticator
{
    private $service;

    public function __construct(AccessTokenRepositoryInterface $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function findUserByToken($token)
    {
        return $this->service->findUserByToken($token);
    }
}

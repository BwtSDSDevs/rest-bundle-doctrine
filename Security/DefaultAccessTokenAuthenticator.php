<?php

namespace Dontdrinkandroot\RestBundle\Security;

use Dontdrinkandroot\RestBundle\Service\AccessTokenServiceInterface;

class DefaultAccessTokenAuthenticator extends AbstractAccessTokenAuthenticator
{
    private $service;

    public function __construct(AccessTokenServiceInterface $service)
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

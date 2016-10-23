<?php

namespace Dontdrinkandroot\RestBundle\Repository;

use Dontdrinkandroot\Repository\EntityRepositoryInterface;
use Dontdrinkandroot\RestBundle\Entity\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessTokenRepositoryInterface extends EntityRepositoryInterface
{
    /**
     * @param string $token
     *
     * @return UserInterface|null
     */
    public function findUserByToken($token);

    /**
     * @return AccessToken[]
     */
    public function findExpiredTokens();
}

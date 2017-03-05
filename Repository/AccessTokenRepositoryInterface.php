<?php

namespace Dontdrinkandroot\RestBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Dontdrinkandroot\RestBundle\Entity\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessTokenRepositoryInterface extends ObjectRepository
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

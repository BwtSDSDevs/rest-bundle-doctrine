<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\RestBundle\Entity\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessTokenServiceInterface
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return AccessToken
     */
    public function createAcessToken($username, $password);

    /**
     * @param string $token
     *
     * @return UserInterface|null
     */
    public function findUserByToken($token);

    /**
     * @return int
     */
    public function cleanUpExpiredTokens();

    /**
     * @param UserInterface $user
     *
     * @return AccessToken[]
     */
    public function listByUser(UserInterface $user);
}

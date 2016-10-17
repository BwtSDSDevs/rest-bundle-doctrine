<?php

namespace Dontdrinkandroot\RestBundle\Entity;

use Dontdrinkandroot\Entity\GeneratedIntegerIdEntity;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AccessToken extends GeneratedIntegerIdEntity
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTime|null
     */
    private $expiry;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param \DateTime|null $expiry
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
    }

    /**
     * @return UserInterface
     */
    abstract public function getUser();

    /**
     * @param $user UserInterface
     */
    abstract public function setUser($user);
}

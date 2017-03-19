<?php

namespace Dontdrinkandroot\RestBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class UserCredentials
{
    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username)
    {
        $this->username = $username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password)
    {
        $this->password = $password;
    }
}

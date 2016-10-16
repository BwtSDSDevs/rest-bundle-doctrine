<?php

namespace Dontdrinkandroot\RestBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserCredentials
{
    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $password;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}

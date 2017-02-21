<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity()
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $role;

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        /* Using bcrypt */
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        /* Noop */
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function setRole(string $role)
    {
        $this->role = $role;
    }
}

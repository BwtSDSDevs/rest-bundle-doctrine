<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity(
 *     repositoryClass="Dontdrinkandroot\Service\DoctrineCrudService"
 * )
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="subordinates")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var User|null
     */
    private $supervisor;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="supervisor")
     *
     * @var Collection|User[]
     */
    private $subordinates;

    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="users")
     *
     * @var Collection|Group[]
     */
    private $groups;

    function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->subordinates = new ArrayCollection();
    }

    /**
     * @REST\Virtual()
     * @REST\Includable()
     *
     * {@inheritdoc}
     */
    public function getRoles()
    {
        if ('ROLE_ADMIN' === $this->role) {
            return ['ROLE_ADMIN', 'ROLE_USER'];
        }

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

    /**
     * @return Collection|Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return User|null
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @param User|null $supervisor
     */
    public function setSupervisor($supervisor)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return Collection|User[]
     */
    public function getSubordinates()
    {
        return $this->subordinates;
    }
}

<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: "string", nullable: true)]
    private string $password;

    #[ORM\Column(type: "string", nullable: false)]
    private string $username;

    #[ORM\Column(type: "string", nullable: false)]
    private string $role;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "subordinates")]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $supervisor;

    /** @var Collection<array-key, User>|Selectable<User> */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: "supervisor")]
    private Collection|Selectable $subordinates;

    /** @var Collection<array-key, Group>|Selectable<Group> */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: "users")]
    private Collection|Selectable $groups;

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
    public function getRoles(): array
    {
        if ('ROLE_ADMIN' === $this->role) {
            return ['ROLE_ADMIN', 'ROLE_USER'];
        }

        return [$this->role];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
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

    public function setPassword(string $password)
    {
        $this->password = $password;
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

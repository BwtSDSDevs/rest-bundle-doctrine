<?php

namespace Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Niebvelungen\RestBundleDoctrine\Metadata\Attribute as REST;
use RuntimeException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "subordinates")]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $supervisor = null;

    /** @var Collection<array-key, User>&Selectable<array-key,User> */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: "supervisor")]
    private Collection&Selectable $subordinates;

    /** @var Collection<array-key, Group>&Selectable<array-key,Group> */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: "users")]
    private Collection&Selectable $groups;

    public function __construct(
        #[ORM\Column(type: "string", nullable: false)]
        private string $username,

        #[ORM\Column(type: "string", nullable: false)]
        private string $role
    ) {
        $this->groups = new ArrayCollection();
        $this->subordinates = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    #[REST\Virtual]
    #[REST\Includable]
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
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        /* Noop */
    }

    public function getId(): int
    {
        return $this->id ?? throw new RuntimeException('Entity not persisted');
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return Collection<array-key,Group>&Selectable<array-key,Group>
     */
    public function getGroups(): Collection&Selectable
    {
        return $this->groups;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    public function setSupervisor(?User $supervisor): void
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @return Collection<array-key,User>&Selectable<array-key,User>
     */
    public function getSubordinates(): Collection&Selectable
    {
        return $this->subordinates;
    }
}

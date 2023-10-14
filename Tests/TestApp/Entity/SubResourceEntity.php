<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SubResourceEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: SecuredEntity::class, inversedBy: "subResources")]
    private SecuredEntity $parentEntity;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $creator = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $text = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setParentEntity(?SecuredEntity $parentEntity)
    {
        $this->parentEntity = $parentEntity;
    }

    public function getParentEntity(): ?SecuredEntity
    {
        return $this->parentEntity;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }
}

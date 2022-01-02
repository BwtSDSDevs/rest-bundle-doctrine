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

    /**
     * @var SecuredEntity
     */
    #[ORM\ManyToOne(targetEntity: SecuredEntity::class, inversedBy: "subResources")]
    private $parentEntity;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $creator;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $text;

    /**
     * @return int
     */
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

    /**
     * @return User|null
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param User|null $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return null|string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param null|string $text
     */
    public function setText(?string $text): void
    {
        $this->text = $text;
    }
}

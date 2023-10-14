<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\RestBundle\Metadata\Attribute as REST;

#[REST\RootResource(
    operations: [
        new REST\Operation(CrudOperation::CREATE),
        new REST\Operation(CrudOperation::UPDATE),
    ]
)]
#[ORM\Entity]
class PuttablePostableAnnotationEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private int $id;

    #[REST\Puttable()]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $puttableByAll = null;

    #[REST\Postable()]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $postableByAll = null;

    #[REST\Puttable(granted: "ROLE_USER")]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $puttableByUser = null;

    #[REST\Postable(granted: "ROLE_USER")]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $postableByUser = null;

    #[REST\Puttable(granted: "ROLE_ADMIN")]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $puttableByAdmin = null;

    #[REST\Postable(granted: "ROLE_ADMIN")]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $postableByAdmin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPuttableByAll(): ?string
    {
        return $this->puttableByAll;
    }

    public function setPuttableByAll(?string $puttableByAll): void
    {
        $this->puttableByAll = $puttableByAll;
    }

    public function getPostableByAll(): ?string
    {
        return $this->postableByAll;
    }

    public function setPostableByAll(?string $postableByAll): void
    {
        $this->postableByAll = $postableByAll;
    }

    public function getPuttableByUser(): ?string
    {
        return $this->puttableByUser;
    }

    public function setPuttableByUser(?string $puttableByUser): void
    {
        $this->puttableByUser = $puttableByUser;
    }

    public function getPostableByUser(): ?string
    {
        return $this->postableByUser;
    }

    public function setPostableByUser(?string $postableByUser): void
    {
        $this->postableByUser = $postableByUser;
    }

    public function getPuttableByAdmin(): ?string
    {
        return $this->puttableByAdmin;
    }

    public function setPuttableByAdmin(?string $puttableByAdmin): void
    {
        $this->puttableByAdmin = $puttableByAdmin;
    }

    public function getPostableByAdmin(): ?string
    {
        return $this->postableByAdmin;
    }

    public function setPostableByAdmin(?string $postableByAdmin): void
    {
        $this->postableByAdmin = $postableByAdmin;
    }
}

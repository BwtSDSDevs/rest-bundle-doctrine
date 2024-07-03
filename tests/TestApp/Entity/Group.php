<?php

namespace Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\Common\CrudOperation;
use Niebvelungen\RestBundleDoctrine\Metadata\Attribute as REST;

#[REST\RootResource([new REST\Operation(CrudOperation::READ)])]
#[ORM\Entity]
#[ORM\Table(name: "`Group`")]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private int $id;

    #[ORM\Column(type: "string", nullable: false)]
    private string $name;

    /**
     * @var Collection<array-key,User>
     */
    #[REST\SubResource([
        new REST\Operation(CrudOperation::UPDATE),
        new REST\Operation(CrudOperation::DELETE)
    ])]
    #[REST\Includable]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: "groups")]
    private Collection $users;

    function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }
}

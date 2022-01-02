<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @REST\RootResource(
 *     methods = {
 *         @REST\Method(name="READ")
 *     }
 * )
 */
#[ORM\Entity]
#[ORM\Table(name: "`Group`")]
class Group
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", nullable: false)]
    private $name;

    /**
     * @REST\SubResource(
     *     methods = {
     *         @REST\Method("UPDATE"),
     *         @REST\Method("DELETE")
     *     }
     * )
     * @REST\Includable()
     *
     * @var Collection|User[]
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: "groups")]
    private $users;

    function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }
}

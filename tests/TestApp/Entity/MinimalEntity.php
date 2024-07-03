<?php

namespace Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\Common\CrudOperation;
use Niebvelungen\RestBundleDoctrine\Metadata\Attribute as REST;

#[REST\RootResource([
    new REST\Operation(CrudOperation::LIST),
    new REST\Operation(method: CrudOperation::READ, defaultIncludes: ["detail", "arbitrary"])
])]
#[ORM\Entity]
class MinimalEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private $id;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $integerValue;

    #[REST\Includable(["detail"])]
    private string $defaultIncludedField = 'detail';

    function __construct()
    {
        $this->ownerManyToManys = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIntegerValue(): ?int
    {
        return $this->integerValue;
    }

    public function setIntegerValue(?int $integerValue)
    {
        $this->integerValue = $integerValue;
    }

    public function getDefaultIncludedField(): string
    {
        return $this->defaultIncludedField;
    }
}

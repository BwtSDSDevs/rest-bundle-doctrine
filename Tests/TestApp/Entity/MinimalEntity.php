<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @REST\RootResource(operations={@REST\Operation("LIST"),@Rest\Operation(name="READ",defaultIncludes={"detail", "arbitrary"})})
 */
#[ORM\Entity]
class MinimalEntity
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private $id;

    /**
     * @var int|null
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $integerValue;

    /**
     * @REST\Includable("detail")
     *
     * @var string
     */
    private $defaultIncludedField = 'detail';

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

    /**
     * @return string
     */
    public function getDefaultIncludedField(): string
    {
        return $this->defaultIncludedField;
    }
}

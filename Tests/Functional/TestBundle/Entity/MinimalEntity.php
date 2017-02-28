<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @ORM\Entity(
 *     repositoryClass="Dontdrinkandroot\Service\DoctrineCrudService"
 * )
 * @REST\RootResource(methods={@REST\Method("LIST"),@Rest\Method(name="GET",defaultIncludes={"detail", "arbitrary"})})
 */
class MinimalEntity
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
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $integerValue;

    /**
     * @REST\Includable("detail")
     *
     * @var string
     */
    private $defaultIncludedField = 'detail';

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

<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Puttable;

#[ORM\Embeddable]
class EmbeddableEntity
{
    #[Puttable]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $fieldString = null;

    #[Puttable]
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $fieldInteger = null;

    public function getFieldString(): ?string
    {
        return $this->fieldString;
    }

    public function setFieldString(?string $fieldString)
    {
        $this->fieldString = $fieldString;
    }

    public function getFieldInteger(): ?int
    {
        return $this->fieldInteger;
    }

    public function setFieldInteger(?int $fieldInteger)
    {
        $this->fieldInteger = $fieldInteger;
    }
}

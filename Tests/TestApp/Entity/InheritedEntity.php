<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class InheritedEntity extends SuperEntity
{
    private ?string $subClassField;

    public function getSubClassField(): ?string
    {
        return $this->subClassField;
    }

    public function setSubClassField(?string $subClassField): void
    {
        $this->subClassField = $subClassField;
    }
}

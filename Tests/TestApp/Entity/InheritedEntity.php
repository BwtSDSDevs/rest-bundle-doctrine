<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class InheritedEntity extends SuperEntity
{
    /**
     * @var string|null
     */
    private $subClassField;

    /**
     * @return null|string
     */
    public function getSubClassField(): ?string
    {
        return $this->subClassField;
    }

    /**
     * @param null|string $subClassField
     */
    public function setSubClassField(?string $subClassField)
    {
        $this->subClassField = $subClassField;
    }

}

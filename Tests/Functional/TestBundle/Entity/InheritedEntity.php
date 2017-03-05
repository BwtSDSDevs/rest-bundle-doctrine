<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity()
 */
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

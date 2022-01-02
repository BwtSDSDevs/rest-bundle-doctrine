<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Puttable;

#[ORM\Embeddable]
class EmbeddableEntity
{
    /**
     * @Puttable()
     *
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $fieldString;

    /**
     * @Puttable()
     *
     * @var int|null
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $fieldInteger;

    /**
     * @return null|string
     */
    public function getFieldString()
    {
        return $this->fieldString;
    }

    /**
     * @param null|string $fieldString
     */
    public function setFieldString($fieldString)
    {
        $this->fieldString = $fieldString;
    }

    /**
     * @return int|null
     */
    public function getFieldInteger()
    {
        return $this->fieldInteger;
    }

    /**
     * @param int|null $fieldInteger
     */
    public function setFieldInteger($fieldInteger)
    {
        $this->fieldInteger = $fieldInteger;
    }
}

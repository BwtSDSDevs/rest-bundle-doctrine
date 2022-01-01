<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Puttable;

/**
 * @ORM\Embeddable()
 */
class EmbeddableEntity
{
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Puttable()
     *
     * @var string|null
     */
    private $fieldString;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Puttable()
     *
     * @var int|null
     */
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

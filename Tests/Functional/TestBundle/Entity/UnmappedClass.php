<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

/**
 * Unmapped Classes should be ignored.
 */
class UnmappedClass
{
    private $someField;

    /**
     * @return mixed
     */
    public function getSomeField()
    {
        return $this->someField;
    }

    /**
     * @param mixed $someField
     */
    public function setSomeField($someField)
    {
        $this->someField = $someField;
    }
}

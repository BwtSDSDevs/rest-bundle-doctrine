<?php

namespace Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity;

/**
 * Unmapped Classes should be ignored.
 */
class UnmappedClass
{
    private mixed $someField;

    public function getSomeField(): mixed
    {
        return $this->someField;
    }

    public function setSomeField(mixed $someField): void
    {
        $this->someField = $someField;
    }
}

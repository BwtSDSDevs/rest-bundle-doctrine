<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SubResource
{
    /**
     * @param Operation[] $operations
     */
    public function __construct(
        public array $operations,
        public ?string $path = null,
    ) {
    }
}

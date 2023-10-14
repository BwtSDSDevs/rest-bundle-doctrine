<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Includable
{
    /**
     * @param string[]|null $paths
     */
    public function __construct(public ?array $paths = null)
    {
    }
}

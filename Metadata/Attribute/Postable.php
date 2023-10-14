<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Postable extends Writeable
{
    public static function parse($config): ?Postable
    {
        return self::parseInstance($config, new Postable());
    }
}

<?php

namespace Niebvelungen\RestBundleDoctrine\Metadata\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Puttable extends Writeable
{
    public static function parse($config): ?Puttable
    {
        return self::parseInstance($config, new Puttable());
    }
}

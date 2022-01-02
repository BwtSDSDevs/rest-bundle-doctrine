<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Puttable extends Writeable
{
    public static function parse($config): ?Puttable
    {
        return self::parseInstance($config, new Puttable());
    }
}

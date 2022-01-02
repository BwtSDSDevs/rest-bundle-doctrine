<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Postable extends Writeable
{
    public static function parse($config): ?Postable
    {
        return self::parseInstance($config, new Postable());
    }
}

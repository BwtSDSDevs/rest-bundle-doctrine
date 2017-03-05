<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 *
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class Postable extends Writeable
{
    public static function parse($config): ?Postable
    {
        return self::parseInstance($config, new Postable());
    }
}

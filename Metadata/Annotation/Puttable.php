<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 *
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class Puttable extends Writeable
{
    public static function parse($config): ?Puttable
    {
        return parent::parseInstance($config, new Puttable());
    }
}

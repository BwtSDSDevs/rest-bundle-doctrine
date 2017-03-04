<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
abstract class Writeable
{
    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $right;

    /**
     * @var bool
     */
    public $byReference;

    protected static function parseInstance($config, $instance)
    {
        if (null === $config) {
            return null;
        }

        $instance->right = Right::parse($config['right'] ?? null);
        $instance->byReference = ParseUtils::parseBool($config['byReference'] ?? null);

        return $instance;
    }
}

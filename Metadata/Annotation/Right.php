<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 *
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class Right
{
    /**
     * @Required
     * @var array<string>
     */
    public $attributes;

    /**
     * @var string
     */
    public $propertyPath;

    public static function parse(?array $config): ?Right
    {
        if (null === $config) {
            return null;
        }

        $right = new Right();
        $right->attributes = ParseUtils::parseStringArray($config['attributes'] ?? null);
        $right->propertyPath = ParseUtils::parseString($config['propertyPath'] ?? null);

        return $right;
    }
}

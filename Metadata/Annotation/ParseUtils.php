<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ParseUtils
{
    public static function parseBool(?bool $value): ?bool
    {
        assert(null === $value || is_bool($value));

        return $value;
    }

    public static function parseString(?string $value): ?bool
    {
        assert(null === $value || is_string($value));

        return $value;
    }

    public static function parseStringArray(?array $value): ?array
    {
        assert(null === $value || is_array($value));

        return $value;
    }
}

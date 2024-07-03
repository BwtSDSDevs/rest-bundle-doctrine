<?php

namespace Niebvelungen\RestBundleDoctrine\Metadata\Attribute;

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

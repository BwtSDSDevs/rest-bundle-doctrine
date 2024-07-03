<?php declare(strict_types=1);


namespace Niebvelungen\RestBundleDoctrine\Helper;

class StringHelper
{
    public static function camelCaseToSnakeCase(string $camelCaseString): string
    {
        $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/';
        $snakeCase = preg_replace($pattern, '_', $camelCaseString);
        return strtolower($snakeCase);
    }

    public static function snakeCaseToUpperCase(string $snakeCaseString): string
    {
        $str = str_replace('-', '', ucwords($snakeCaseString, '-'));

        $str[0] = strtolower($str[0]);

        return $str;
    }
}
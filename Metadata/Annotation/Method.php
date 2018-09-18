<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Method
{
    const LIST = 'LIST';
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $right;

    /**
     * @var array<string>
     */
    public $defaultIncludes;

    public static function parse($name, $config): ?Method
    {
        assert(in_array($name, [self::LIST, self::POST, self::GET, self::PUT, self::DELETE]));
        assert(is_string($name));

        $method = new Method();
        $method->name = $name;
        if (is_bool($config) && true === $config) {
            return $method;
        }

        $method->right = Right::parse($config['right'] ?? null);
        $method->defaultIncludes = ParseUtils::parseStringArray($config['defaultIncludes'] ?? null);

        return $method;
    }
}

<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Dontdrinkandroot\Common\CrudOperation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Operation
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    public ?Right $right = null;

    /**
     * @var array<string>
     */
    public $defaultIncludes;

    public static function parse($name, $config): ?Operation
    {
        assert(
            in_array(
                $name,
                [
                    CrudOperation::LIST,
                    CrudOperation::CREATE,
                    CrudOperation::READ,
                    CrudOperation::UPDATE,
                    CrudOperation::DELETE
                ]
            )
        );
        assert(is_string($name));

        $method = new Operation();
        $method->name = $name;
        if (is_bool($config) && true === $config) {
            return $method;
        }

        $method->right = Right::parse($config['right'] ?? null);
        $method->defaultIncludes = ParseUtils::parseStringArray($config['defaultIncludes'] ?? null);

        return $method;
    }
}

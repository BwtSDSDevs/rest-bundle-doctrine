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

    /**
     * @var array<string>
     */
    public $defaultIncludes;

    public ?string $granted = null;

    public ?string $grantedExpression = null;

    public static function parse($name, $config): ?Operation
    {
        assert(
            in_array($name, [
                CrudOperation::LIST,
                CrudOperation::CREATE,
                CrudOperation::READ,
                CrudOperation::UPDATE,
                CrudOperation::DELETE
            ], true)
        );

        $method = new Operation();
        $method->name = $name;
        if (is_bool($config) && true === $config) {
            return $method;
        }

        $method->defaultIncludes = ParseUtils::parseStringArray($config['defaultIncludes'] ?? null);
        $method->granted = $config['granted'] ?? null;
        $method->grantedExpression = $config['granted_expression'] ?? null;

        return $method;
    }
}

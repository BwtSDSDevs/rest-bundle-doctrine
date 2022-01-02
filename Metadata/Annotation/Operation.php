<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\RestBundle\Metadata\PropertyNode;
use PhpParser\Builder\Property;

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

    /** @var list<PropertyNode> */
    public array $properties = [];

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

        $operation = new Operation();
        $operation->name = $name;
        if (is_bool($config) && true === $config) {
            return $operation;
        }

        $operation->defaultIncludes = ParseUtils::parseStringArray($config['defaultIncludes'] ?? null);
        $operation->granted = $config['granted'] ?? null;
        $operation->grantedExpression = $config['granted_expression'] ?? null;
        $operation->properties = static::parseProperties($config['properties'] ?? []);

        return $operation;
    }

    private static function parseProperties(array $propertyConfigs): array
    {
        $propertyNodes = [];
        foreach ($propertyConfigs as $name => $config) {
            $propertyNodes[] = PropertyNode::parse($name, $config);
        }

        return $propertyNodes;
    }
}

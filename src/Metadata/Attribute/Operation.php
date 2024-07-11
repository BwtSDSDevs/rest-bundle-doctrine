<?php

namespace Niebvelungen\RestBundleDoctrine\Metadata\Attribute;

use Niebvelungen\RestBundleDoctrine\Metadata\Common\CrudOperation;
use Niebvelungen\RestBundleDoctrine\Metadata\PropertyNode;

class Operation
{
    /**
     * @param string[]|null $defaultIncludes
     * @param PropertyNode[] $properties
     */
    public function __construct(
        public CrudOperation $method,
        public ?array $defaultIncludes = null,
        public ?string $granted = null,
        public ?string $grantedExpression = null,
        public array $properties = [],
    ) {
    }

    public static function parse($method, $config): ?Operation
    {
        $operation = new Operation(
            method: CrudOperation::from($method),
        );
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

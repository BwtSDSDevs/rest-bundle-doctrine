<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

class PropertyNode
{
    private string $name;

    private ?string $granted = null;

    private ?string $grantedExpression = null;

    /** @var list<PropertyNode> */
    private array $children = [];

    public static function parse(string $name, ?array $propertyConfig): PropertyNode
    {
        $propertyNode = new PropertyNode();
        $propertyNode->name = $name;
        $propertyConfig = $propertyConfig ?? [];
        $propertyNode->granted = $propertyConfig['granted'] ?? null;
        $propertyNode->grantedExpression = $propertyConfig['granted_expression'] ?? null;

        $children = [];
        if (isset($propertyConfig['children'])) {
            foreach ($propertyConfig['children'] as $childName => $childConfig) {
                $children[] = self::parse($childName, $childConfig);
            }
        }
        $propertyNode->children = $children;

        return $propertyNode;
    }
}

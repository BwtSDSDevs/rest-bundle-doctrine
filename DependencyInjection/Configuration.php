<?php

namespace Dontdrinkandroot\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ddr_rest');

        // @formatter:off
        $rootNode->children()
            ->scalarNode('api_path')->defaultValue('/api')->end()
        ->end();
        // @formatter:on

        return $treeBuilder;
    }
}

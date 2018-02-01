<?php

namespace Dontdrinkandroot\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
            ->arrayNode('paths')
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('metadata')
                ->children()
                    ->arrayNode('directories')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('path')->isRequired()->end()
                                ->scalarNode('namespace_prefix')->defaultValue('')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
        // @formatter:on

        return $treeBuilder;
    }
}

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
            ->scalarNode('access_token_class')->isRequired()->end()
            ->scalarNode('authentication_provider_key')->isRequired()->end()
        ->end();
        // @formatter:on

        return $treeBuilder;
    }
}

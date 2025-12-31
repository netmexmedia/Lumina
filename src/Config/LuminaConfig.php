<?php

namespace Netmex\Lumina\Config;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

final class LuminaConfig
{
    public function __invoke(NodeDefinition $root): void
    {
        $root
            ->children()
                ->scalarNode('endpoint')
                    ->defaultValue('/graphql')
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }
}
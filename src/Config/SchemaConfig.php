<?php

declare(strict_types=1);

namespace Netmex\Lumina\Config;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

final class SchemaConfig
{
    public function __invoke(NodeDefinition $root): void
    {
        $root
            ->children()
                ->arrayNode('schema')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('directory')
                            ->defaultValue('%kernel.project_dir%/src/graphql')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
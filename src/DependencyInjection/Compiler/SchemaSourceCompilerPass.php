<?php

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SchemaSourceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SchemaSourceRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(SchemaSourceRegistry::class);

        $sources = [];

        foreach ($container->findTaggedServiceIds('lumina.schema_source') as $id => $tags) {
            $sources[] = new Reference($id);
        }

        $registry->setArgument(0, $sources);
    }
}
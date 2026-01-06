<?php

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Directives\Registery\DirectiveRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DirectiveSchemaSDLCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(DirectiveRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(DirectiveRegistry::class);

        foreach ($container->findTaggedServiceIds('lumina.schema_sdl') as $id => $_) {
            $registry->addMethodCall('registerSDL', [
                new Reference($id),
            ]);
        }
    }
}

// Ok so, The SchemaCompiler
// calls both
// SchemaSDLLoaderInterface and SchemaDocumentLoaderInterface
// So i now need to figure out what wec can remove or refactor
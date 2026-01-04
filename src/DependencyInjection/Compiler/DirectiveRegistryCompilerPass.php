<?php

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Schema\Directives\DirectiveRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DirectiveRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(DirectiveRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(DirectiveRegistry::class);

        // Field directives
        foreach ($container->findTaggedServiceIds('lumina.directive.field') as $id => $_) {
            $registry->addMethodCall('registerField', [
                new Reference($id),
            ]);
        }

        // Argument directives
        foreach ($container->findTaggedServiceIds('lumina.directive.argument') as $id => $_) {
            $registry->addMethodCall('registerArgument', [
                new Reference($id),
            ]);
        }
    }
}
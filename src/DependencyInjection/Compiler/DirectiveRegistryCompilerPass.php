<?php

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Directives\Registery\DirectiveRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DirectiveRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(DirectiveRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(DirectiveRegistry::class);

        foreach ($container->findTaggedServiceIds('lumina.directive') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $className = $definition->getClass();

            $registry->addMethodCall('register', arguments: [
                $className::name(),
                $className,
            ]);

        }
    }
}
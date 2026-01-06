<?php

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Directives\DirectiveRegistry;
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

        foreach ($container->findTaggedServiceIds('lumina.directive') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $className = $definition->getClass();

            $registry->addMethodCall('register', [
                new Reference($id),
            ]);

            $registry->addMethodCall('add', [
                $className::name(),
                $className,
            ]);

        }
    }
}
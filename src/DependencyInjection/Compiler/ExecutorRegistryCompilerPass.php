<?php

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Schema\Execution\ExecutorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ExecutorRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->findDefinition(ExecutorRegistry::class);

        foreach ($container->findTaggedServiceIds('lumina.executor') as $id => $_) {
            $registry->addMethodCall('register', [
                new Reference($id),
            ]);
        }
    }
}

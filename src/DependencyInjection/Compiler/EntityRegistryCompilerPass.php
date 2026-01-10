<?php

declare(strict_types=1);

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\DependencyInjection\Registry\EntityRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EntityRegistryCompilerPass implements CompilerPassInterface
{
    use Psr4RegistryCompilerPassTrait;

    public function process(ContainerBuilder $container): void
    {
        $this->registerPsr4NamespaceClasses(
            container: $container,
            registryClass: EntityRegistry::class,
            subNamespace: 'Entity',
            interface: null // null = register all classes
        );
    }
}

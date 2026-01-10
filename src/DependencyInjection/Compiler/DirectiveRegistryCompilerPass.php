<?php

declare(strict_types=1);

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Contracts\DirectiveInterface;
use Netmex\Lumina\DependencyInjection\Registry\EntityRegistry;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DirectiveRegistryCompilerPass implements CompilerPassInterface
{
    use Psr4RegistryCompilerPassTrait;

    public function process(ContainerBuilder $container): void
    {
        $this->registerPsr4NamespaceClasses(
            container: $container,
            registryClass: DirectiveRegistry::class,
            subNamespace: [
                'Directives/Definition',    // bundle
                'GraphQL/Directives',       // project
            ],
            interface: DirectiveInterface::class,
            serviceTag: 'lumina.directive'
        );

        foreach ($container->findTaggedServiceIds('lumina.directive') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setEntityRegistry', [$container->getDefinition(EntityRegistry::class)]);
        }

        if (!$container->has(EntityRegistry::class)) {
            throw new \RuntimeException('EntityRegistry not yet registered!');
        }

    }
}

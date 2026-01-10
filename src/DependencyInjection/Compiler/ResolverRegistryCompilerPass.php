<?php

declare(strict_types=1);

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\DependencyInjection\Registry\ResolverRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ResolverRegistryCompilerPass implements CompilerPassInterface
{
    use Psr4RegistryCompilerPassTrait;

    public function process(ContainerBuilder $container): void
    {
        $this->registerPsr4NamespaceClasses(
            container: $container,
            registryClass: ResolverRegistry::class,
            subNamespace: [
                'Resolvers',
                'Netmex\\Lumina\\Resolvers',
            ],
            interface: null,
            serviceTag: 'lumina.resolver'
        );
    }
}

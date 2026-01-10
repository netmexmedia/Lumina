<?php

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Contracts\ValidatorInterface;
use Netmex\Lumina\Validators\ValidatorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ValidatorRegistryCompilerPass implements CompilerPassInterface
{
    use Psr4RegistryCompilerPassTrait;

    public function process(ContainerBuilder $container): void
    {
        $this->registerPsr4NamespaceClasses(
            $container,
            ValidatorRegistry::class,
            'Validators',
            ValidatorInterface::class
        );
    }
}

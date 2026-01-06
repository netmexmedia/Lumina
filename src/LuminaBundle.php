<?php

namespace Netmex\Lumina;

use Netmex\Lumina\Config\LuminaConfig;
use Netmex\Lumina\Config\SchemaConfig;
use Netmex\Lumina\DependencyInjection\Compiler\DirectiveRegistryCompilerPass;
use Netmex\Lumina\DependencyInjection\Compiler\DirectiveSchemaSDLCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class LuminaBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new DirectiveRegistryCompilerPass()
        );

        $container->addCompilerPass(
            new DirectiveSchemaSDLCompilerPass()
        );
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $root = $definition->rootNode();

        (new LuminaConfig())($root);
        (new SchemaConfig())($root);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Core services
        $container->import(__DIR__ . '/../config/services.yaml');

        // Optional future files
        // $container->import(__DIR__ . '/../config/graphql.yaml');

        $container->parameters()->set('lumina.endpoint', $config['endpoint']);
        $container->parameters()->set(
            'lumina.schema.directory',
            $config['schema']['directory']
        );

    }
}

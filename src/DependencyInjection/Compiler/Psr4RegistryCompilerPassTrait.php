<?php

declare(strict_types=1);

namespace Netmex\Lumina\DependencyInjection\Compiler;

use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

trait Psr4RegistryCompilerPassTrait
{
    /**
     * Auto-register PSR-4 classes in one or multiple namespaces.
     *
     * Skips abstract classes, interfaces, and classes with scalar constructor arguments.
     *
     * @param string|string[] $subNamespace
     */
    private function registerPsr4NamespaceClasses(
        ContainerBuilder $container,
        string $registryClass,
        string|array $subNamespace,
        string $interface,
        ?string $serviceTag = null
    ): void {
        if (!$container->has($registryClass)) {
            return;
        }

        $registry = $container->findDefinition($registryClass);
        $subNamespaces = (array) $subNamespace;

        $autoloadFile = $container->getParameter('kernel.project_dir') . '/vendor/composer/autoload_psr4.php';
        if (!file_exists($autoloadFile)) {
            return;
        }

        $namespaces = require $autoloadFile;

        foreach ($namespaces as $namespace => $paths) {
            foreach ($paths as $path) {
                foreach ($subNamespaces as $sub) {
                    $this->processNamespaceDirectory($container, $registry, $interface, $namespace, $path, $sub, $serviceTag);
                }
            }
        }
    }

    /**
     * Process a single namespace directory: scan files and register classes.
     */
    private function processNamespaceDirectory(
        ContainerBuilder $container,
        Definition $registry,
        string $interface,
        string $namespace,
        string $basePath,
        string $subPath,
        ?string $serviceTag
    ): void {
        $dir = $basePath . '/' . $subPath;
        if (!is_dir($dir)) {
            return;
        }

        foreach ($this->getPhpFiles($dir) as $file) {
            $this->processFile($container, $registry, $interface, $namespace, $basePath, $file, $serviceTag);
        }
    }

    /**
     * Yield all PHP files in a directory recursively.
     */
    private function getPhpFiles(string $dir): iterable
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file;
            }
        }
    }

    /**
     * Process a single PHP file: register class if valid.
     */
    private function processFile(
        ContainerBuilder $container,
        Definition $registry,
        string $interface,
        string $namespace,
        string $basePath,
        \SplFileInfo $file,
        ?string $serviceTag
    ): void {
        $relativePath = str_replace($basePath . '/', '', $file->getPathname());
        $className = $namespace . str_replace('/', '\\', substr($relativePath, 0, -4));

        if (!class_exists($className) || !is_subclass_of($className, $interface)) {
            return;
        }

        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract() || $reflection->isInterface() || $this->hasScalarConstructorArgs($reflection)) {
            return;
        }

        try {
            $identifier = method_exists($className, 'name') ? $className::name() : $reflection->getShortName();
        } catch (\ReflectionException) {
            return;
        }

        $this->registerClass($container, $registry, $className, $identifier, $serviceTag);
    }

    /**
     * Check if a class constructor has scalar arguments.
     */
    private function hasScalarConstructorArgs(ReflectionClass $reflection): bool
    {
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return false;
        }

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type && $type->isBuiltin()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register a class in the container and the registry.
     */
    private function registerClass(
        ContainerBuilder $container,
        Definition $registry,
        string $className,
        string $identifier,
        ?string $serviceTag
    ): void {
        $registry->addMethodCall('register', [$identifier, $className]);

        $definition = new Definition($className);
        $definition->setAutowired(true);
        $definition->setPublic(true);

        if ($serviceTag !== null) {
            $definition->addTag($serviceTag);
        }

        $container->setDefinition($className, $definition);
    }
}

<?php

declare(strict_types=1);

namespace Netmex\Lumina\DependencyInjection\Compiler;

use Netmex\Lumina\Contracts\PermissionInterface;
use Netmex\Lumina\Permissions\PermissionRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ReflectionClass;

final class PermissionRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(PermissionRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(PermissionRegistry::class);

        $autoloadFile = $container->getParameter('kernel.project_dir') . '/vendor/composer/autoload_psr4.php';
        if (!file_exists($autoloadFile)) {
            return;
        }

        $namespaces = require $autoloadFile;

        foreach ($namespaces as $namespace => $paths) {
            foreach ($paths as $path) {
                $permissionsDir = $path . '/Permissions';
                if (!is_dir($permissionsDir)) {
                    continue;
                }

                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($permissionsDir));
                foreach ($files as $file) {
                    if (!$file->isFile() || $file->getExtension() !== 'php') {
                        continue;
                    }

                    $relativePath = str_replace($path . '/', '', $file->getPathname());
                    $className = $namespace . str_replace('/', '\\', substr($relativePath, 0, -4));

                    if (!class_exists($className)) {
                        continue; // skip if autoload fails
                    }

                    if (!is_subclass_of($className, PermissionInterface::class)) {
                        continue;
                    }

                    // Determine identifier
                    try {
                        if (method_exists($className, 'name')) {
                            $identifier = $className::name();
                        } else {
                            $identifier = (new ReflectionClass($className))->getShortName();
                        }
                        $identifier = strtolower($identifier);
                    } catch (\ReflectionException $e) {
                        continue;
                    }

                    $registry->addMethodCall('register', [$identifier, $className]);
                }
            }
        }
    }
}

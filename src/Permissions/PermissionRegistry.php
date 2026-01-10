<?php

namespace Netmex\Lumina\Permissions;

class PermissionRegistry
{
    private array $permissions = [];

    public function register(string $name, string $className): void
    {
        $this->permissions[$name] = $className;
    }

    public function resolve(string $name): string
    {
        if (!isset($this->permissions[$name])) {
            throw new \LogicException("Permission identifier '{$name}' is not registered.");
        }

        return $this->permissions[$name];
    }
}
<?php

namespace Netmex\Lumina\DependencyInjection\Registry;

class EntityRegistry
{
    private array $entities = [];

    public function register(string $shortName, string $fqcn): void
    {
        $this->entities[$shortName] = $fqcn;
    }

    public function get(string $shortName): ?string
    {
        return $this->entities[$shortName] ?? null;
    }
}
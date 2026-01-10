<?php

namespace Netmex\Lumina\DependencyInjection\Registry;

class ResolverRegistry
{
    private array $resolvers = [];

    public function register(string $shortName, string $fqcn): void
    {
        $this->resolvers[$shortName] = $fqcn;
    }

    public function get(string $shortName): ?string
    {
        return $this->resolvers[$shortName] ?? null;
    }
}
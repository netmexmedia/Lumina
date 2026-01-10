<?php

namespace Netmex\Lumina\DependencyInjection;

use Netmex\Lumina\DependencyInjection\Registry\ResolverRegistry;

trait UseResolverRegistry
{
    private ?ResolverRegistry $resolveRegistry = null;

    public function setResolverRegistry(ResolverRegistry $registry): void
    {
        $this->resolveRegistry = $registry;
    }

    protected function getResolverRegistry(): ResolverRegistry
    {
        if (!$this->resolveRegistry) {
            throw new \RuntimeException('ResolverRegistry not set in directive.');
        }
        return $this->resolveRegistry;
    }

    protected function getResolver(string $shortName): ?string
    {
        return $this->getResolverRegistry()->get($shortName);
    }
}

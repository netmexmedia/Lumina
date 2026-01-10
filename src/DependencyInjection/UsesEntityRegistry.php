<?php

namespace Netmex\Lumina\DependencyInjection;

use Netmex\Lumina\DependencyInjection\Registry\EntityRegistry;

trait UsesEntityRegistry
{
    private ?EntityRegistry $entityRegistry = null;

    public function setEntityRegistry(EntityRegistry $registry): void
    {
        $this->entityRegistry = $registry;
    }

    protected function getEntityRegistry(): EntityRegistry
    {
        if (!$this->entityRegistry) {
            throw new \RuntimeException('EntityRegistry not set in directive.');
        }
        return $this->entityRegistry;
    }

    protected function resolveEntityFQCN(string $shortName): ?string
    {
        return $this->getEntityRegistry()->get($shortName);
    }
}

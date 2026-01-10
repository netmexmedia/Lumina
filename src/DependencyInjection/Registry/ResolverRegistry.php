<?php

declare(strict_types=1);

namespace Netmex\Lumina\DependencyInjection\Registry;

final class ResolverRegistry
{
    /** @var array<string, class-string> */
    private array $resolvers = [];

    public function register(string $identifier, string $class): void
    {
        $this->resolvers[$identifier] = $class;
    }

    public function resolve(string $identifier): string
    {
        if (!isset($this->resolvers[$identifier])) {
            throw new \LogicException("Resolver '{$identifier}' is not registered.");
        }

        return $this->resolvers[$identifier];
    }

    public function all(): array
    {
        return $this->resolvers;
    }
}

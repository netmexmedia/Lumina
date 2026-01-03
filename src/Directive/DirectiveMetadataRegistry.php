<?php

namespace Netmex\Lumina\Directive;

final class DirectiveMetadataRegistry
{
    private array $map = [];

    public function add(string $location, string $name, array $args = []): void
    {
        $this->map[$location][] = [
            'name' => $name,
            'args' => $args,
        ];
    }

    public function get(string $location): array
    {
        return $this->map[$location] ?? [];
    }

    public function all(): array
    {
        return $this->map;
    }
}
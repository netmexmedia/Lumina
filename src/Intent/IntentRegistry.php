<?php

namespace Netmex\Lumina\Intent;

use Netmex\Lumina\Directives\Definition\AbstractDirective;

// TODO: This isnt quite right, as multiple intents may exist for a single key
final class IntentRegistry
{
    /** @var array<string, AbstractDirective[]> */
    private array $directives = [];

    public function add(string $key, AbstractDirective $directive): void
    {
        $this->directives[$key][] = $directive;
    }

    /** @return AbstractDirective[] */
    public function get(string $key): array
    {
        return $this->directives[$key] ?? [];
    }

    /** @return array<string, AbstractDirective[]> */
    public function all(): array
    {
        return $this->directives;
    }
}

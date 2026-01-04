<?php

namespace Netmex\Lumina\Intent;

final class IntentRegistry
{
    /** @var array<string, QueryIntent> */
    private array $intents = [];

    public function add(QueryIntent $intent): void
    {
        $this->intents[$intent->type . '.' . $intent->field] = $intent;
    }

    public function get(string $type, string $field): ?QueryIntent
    {
        return $this->intents["$type.$field"] ?? null;
    }

    public function all(): array
    {
        return array_values($this->intents);
    }
}

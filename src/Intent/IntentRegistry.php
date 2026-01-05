<?php

namespace Netmex\Lumina\Intent;

final class IntentRegistry
{
    /** @var array<string, Intent> */
    private array $intents = [];

    public function add(Intent $intent): void
    {
        $this->intents[$intent->type . '.' . $intent->field] = $intent;
    }

    public function get(string $type, string $field): ?Intent
    {
        return $this->intents["$type.$field"] ?? null;
    }

    public function all(): array
    {
        return array_values($this->intents);
    }
}

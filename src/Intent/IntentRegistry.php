<?php

namespace Netmex\Lumina\Intent;

final class IntentRegistry
{
    /** @var array<string, Intent> Key format: type.field */
    private array $intents = [];

    public function add(Intent $intent): void
    {
        $key = $intent->typeName . '.' . $intent->fieldName;
        $this->intents[$key] = $intent;
    }

    public function get(string $typeName, string $fieldName): ?Intent
    {
        $key = $typeName . '.' . $fieldName;
        return $this->intents[$key] ?? null;
    }

    /** @return Intent[] */
    public function all(): array
    {
        return $this->intents;
    }
}

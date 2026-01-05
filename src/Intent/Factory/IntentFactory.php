<?php

namespace Netmex\Lumina\Intent\Factory;

use Netmex\Lumina\Intent\QueryIntent;

final class IntentFactory
{
    public function create(string $type, string $field): QueryIntent
    {
        return new QueryIntent(
            type: $type,
            field: $field
        );
    }
}
<?php

namespace Netmex\Lumina\Intent\Factory;

use Netmex\Lumina\Intent\Intent;

final class IntentFactory
{
    public function create(string $type, string $field): Intent
    {
        return new Intent(
            type: $type,
            field: $field
        );
    }
}
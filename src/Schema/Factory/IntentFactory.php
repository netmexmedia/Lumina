<?php

namespace Netmex\Lumina\Schema\Factory;

use Netmex\Lumina\Contracts\IntentFactoryInterface;
use Netmex\Lumina\Intent\Intent;

final class IntentFactory implements IntentFactoryInterface
{
    public function create(string $typeName, string $fieldName, array $typeDirectives = []): Intent
    {
        $intent = new Intent($typeName, $fieldName);

        foreach ($typeDirectives as $directive) {
            $intent->addTypeModifier($directive->name(), $directive);
        }

        return $intent;
    }
}
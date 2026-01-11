<?php

namespace Netmex\Lumina\Schema\Factory;

use Netmex\Lumina\Contracts\IntentFactoryInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentMetaData;

final class IntentFactory implements IntentFactoryInterface
{
    public function create(string $typeName, string $fieldName, array $typeDirectives = []): Intent
    {
        $intent = new Intent($typeName, $fieldName);
        $metaData = new IntentMetaData();
        $intent->addMetaData($metaData);


        foreach ($typeDirectives as $directive) {
            $intent->addTypeModifier($directive->name(), $directive);
        }

        return $intent;
    }
}
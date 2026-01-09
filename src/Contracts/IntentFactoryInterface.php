<?php

namespace Netmex\Lumina\Contracts;

use Netmex\Lumina\Intent\Intent;

interface IntentFactoryInterface
{
    public function create(string $typeName, string $fieldName, array $typeDirectives = []): Intent;
}
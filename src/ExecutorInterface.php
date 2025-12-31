<?php

namespace Netmex\Lumina;

interface ExecutorInterface
{
    public function execute(string $query, array $variables = [], ?string $operation = null): array;
}
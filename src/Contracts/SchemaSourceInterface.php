<?php

declare(strict_types=1);

namespace Netmex\Lumina\Contracts;

interface SchemaSourceInterface
{
    public function load(): string;
}

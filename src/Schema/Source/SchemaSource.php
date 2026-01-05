<?php

namespace Netmex\Lumina\Schema\Source;

interface SchemaSource
{
    public function load(): string;
}
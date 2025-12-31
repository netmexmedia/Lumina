<?php

namespace Netmex\Lumina;

interface SchemaSource
{
    public function load(): string;
}
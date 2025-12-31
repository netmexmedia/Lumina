<?php

namespace Netmex\Lumina;

use GraphQL\Type\Schema;

interface SchemaBuilderInterface
{
    public function build(): Schema;
}
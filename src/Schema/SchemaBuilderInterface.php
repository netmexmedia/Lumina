<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Type\Schema;

interface SchemaBuilderInterface
{
    public function build(): Schema;
}
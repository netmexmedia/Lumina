<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Language\AST\DocumentNode;

interface SchemaDocumentLoaderInterface
{
    public function load(): DocumentNode;
}
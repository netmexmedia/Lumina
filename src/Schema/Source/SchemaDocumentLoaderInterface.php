<?php

namespace Netmex\Lumina\Schema\Source;

use GraphQL\Language\AST\DocumentNode;

interface SchemaDocumentLoaderInterface
{
    public function load(): DocumentNode;
}
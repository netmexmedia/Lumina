<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\QueryIntent;

interface FieldDirective
{
    public function name(): string;

    public function applyToField(QueryIntent $intent, FieldDefinitionNode $field, ObjectTypeDefinitionNode $parentType): void;
}

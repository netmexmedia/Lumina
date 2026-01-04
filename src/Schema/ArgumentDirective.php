<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\QueryIntent;

interface ArgumentDirective
{
    public function name(): string;

    public function applyToArgument(QueryIntent $intent, InputValueDefinitionNode $argument, FieldDefinitionNode $field, ObjectTypeDefinitionNode $parentType): void;
}
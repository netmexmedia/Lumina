<?php

namespace Netmex\Lumina\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\Intent;

interface FieldDirective
{
    public function name(): string;

    public function applyToField(Intent $intent, FieldDefinitionNode $field, ObjectTypeDefinitionNode $parentType): void;
}

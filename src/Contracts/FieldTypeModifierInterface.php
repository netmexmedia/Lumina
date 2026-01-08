<?php

namespace Netmex\Lumina\Contracts;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;

interface FieldTypeModifierInterface
{
    public function modifyFieldType(FieldDefinitionNode $fieldNode, DocumentNode $document): void;
}
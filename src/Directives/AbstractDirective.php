<?php

namespace Netmex\Lumina\Directives;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;

abstract class AbstractDirective implements DirectiveInterface
{
    public DirectiveNode $directiveNode;
    public ScalarTypeDefinitionNode|ScalarTypeExtensionNode|ObjectTypeDefinitionNode|ObjectTypeExtensionNode|InterfaceTypeDefinitionNode|InterfaceTypeExtensionNode|UnionTypeDefinitionNode|UnionTypeExtensionNode|EnumTypeDefinitionNode|EnumTypeExtensionNode|InputObjectTypeDefinitionNode|InputObjectTypeExtensionNode|FieldDefinitionNode|InputValueDefinitionNode|EnumValueDefinitionNode $definitionNode;
    public array $directiveArguments;

    public static function name(): string
    {
        return static::class;
    }

    public function nodeName(): string
    {
        return $this->definitionNode->name->value;
    }

    public function modelClass(): ?string
    {
        $type = $this->definitionNode->type;

        while (!$type instanceof NamedTypeNode) {
            $type = $type->type;
        }

        return $type->name->value;
    }

    public function getDirectiveArgument(string $key, $default = null)
    {
        return $this->directiveArguments[$key] ?? $default;
    }

}
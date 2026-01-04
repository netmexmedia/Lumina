<?php

namespace Netmex\Lumina\Schema\AST;

use Doctrine\ORM\Mapping\Entity;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\SchemaExtensionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeExtensionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;

class ASTBuilder
{
    public const EXTENSION_TO_DEFINITION_CLASS = [
        ObjectTypeExtensionNode::class => ObjectTypeDefinitionNode::class,
        InputObjectTypeExtensionNode::class => InputObjectTypeDefinitionNode::class,
        InterfaceTypeExtensionNode::class => InterfaceTypeDefinitionNode::class,
        ScalarTypeExtensionNode::class => ScalarTypeDefinitionNode::class,
        EnumTypeExtensionNode::class => EnumTypeDefinitionNode::class,
        UnionTypeExtensionNode::class => UnionTypeDefinitionNode::class,
    ];
    protected ASTDocument $ASTDocument;

    // TODO: For now we directly pass in schema to the builder, later we can refactor to use registry
    public function build(string $schema): ASTDocument
    {
        $this->ASTDocument = ASTDocument::fromSource($schema);

        // Needed to add more to the AST Document
        $this->applyTypeDefinitionManipulators();
        $this->applyTypeExtensionManipulators();
        $this->applyFieldManipulators();
        $this->applyArgManipulators();
        $this->applyInputFieldManipulators();

        dd($this->ASTDocument);
        return $this->ASTDocument;
    }

    private function applyTypeDefinitionManipulators(): void
    {
    }

    private function applyTypeExtensionManipulators(): void
    {
    }

    private function applyFieldManipulators(): void
    {
        foreach ($this->ASTDocument->types as $typeName => $typeNode) {
            if (!$typeNode instanceof ObjectTypeDefinitionNode) {
                continue;
            }

            foreach ($typeNode->fields as $fieldNode) {
                foreach ($fieldNode->directives as $directive) {
                    if ($directive->name->value === 'all') {
                        // RECORD intent, do not execute
                        $this->ASTDocument->addFieldDirective(
                            type: $typeName,
                            field: $fieldNode->name->value,
                            directive: 'all',
                            metadata: [
                                'returnType' => $fieldNode->type->name->value,
                            ]
                        );
                    }
                }
            }
        }
    }

    private function applyArgManipulators(): void
    {
        foreach ($this->ASTDocument->types as $typeName => $typeNode) {
            foreach ($typeNode->fields as $fieldNode) {
                foreach ($fieldNode->arguments as $argNode) {
                    foreach ($argNode->directives as $directive) {
                        if ($directive->name->value === 'where') {
                            $this->ASTDocument->addWhereArg(
                                type: $typeName,
                                field: $fieldNode->name->value,
                                arg: $argNode->name->value
                            );
                        }
                    }
                }
            }
        }
    }

    private function applyInputFieldManipulators(): void
    {
    }

}
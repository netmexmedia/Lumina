<?php

namespace Netmex\Lumina\Directive;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;

final class DirectiveMetadataBuilder
{
    public function build(DocumentNode $ast, DirectiveMetadataRegistry $registry): void
    {
        foreach ($ast->definitions as $definition) {
            if (
                !$definition instanceof ObjectTypeDefinitionNode &&
                !$definition instanceof ObjectTypeExtensionNode
            ) {
                continue;
            }

            $typeName = $definition->name->value;


            foreach ($definition->fields ?? [] as $field) {
                $this->collectFieldDirectives($registry, $typeName, $field);
                $this->collectArgumentDirectives($registry, $typeName, $field);
            }
        }
    }

    private function collectFieldDirectives(
        DirectiveMetadataRegistry $registry,
        string $typeName,
        FieldDefinitionNode $field
    ): void {
        $fieldName = $field->name->value;
        $location = "{$typeName}.{$fieldName}";

        foreach ($field->directives as $directive) {
            $registry->add(
                $location,
                $directive->name->value,
                $this->parseArgs($directive->arguments)
            );
        }
    }

    private function collectArgumentDirectives(
        DirectiveMetadataRegistry $registry,
        string $typeName,
        FieldDefinitionNode $field
    ): void {
        $fieldName = $field->name->value;

        foreach ($field->arguments as $arg) {
            $argName = $arg->name->value;
            $location = "{$typeName}.{$fieldName}.{$argName}";

            foreach ($arg->directives as $directive) {
                $registry->add(
                    $location,
                    $directive->name->value,
                    $this->parseArgs($directive->arguments)
                );
            }
        }
    }

    private function parseArgs(iterable $arguments): array
    {
        $out = [];

        foreach ($arguments as $arg) {
            $out[$arg->name->value] = $arg->value->value;
        }

        return $out;
    }
}

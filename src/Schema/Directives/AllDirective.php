<?php

namespace Netmex\Lumina\Schema\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use Netmex\Lumina\Intent\QueryIntent;
use Netmex\Lumina\Schema\FieldDirective;
use Netmex\Lumina\SchemaSDLContributorInterface;

final class AllDirective implements FieldDirective, SchemaSDLContributorInterface
{
    public function name(): string
    {
        return 'all';
    }

    public function definition(): string
    {
        return <<<'GRAPHQL'
            directive @all(
                model: String
            ) on FIELD_DEFINITION
        GRAPHQL;
    }

    public function applyToField(QueryIntent $intent, FieldDefinitionNode $field, ObjectTypeDefinitionNode $parentType): void
    {
        $intent->strategy = QueryIntent::STRATEGY_ALL;

        $namedType = $this->unwrapType($field->type);

        // GraphQL type name (e.g. "Test")
        $graphqlType = $namedType->name->value;

        // TEMP: convention-based mapping
        $intent->model = 'App\\Entity\\' . $graphqlType;
    }

    private function unwrapType(TypeNode $type): NamedTypeNode
    {
        while (!$type instanceof NamedTypeNode) {
            $type = $type->type;
        }

        return $type;
    }
}
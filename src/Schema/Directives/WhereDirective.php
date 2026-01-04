<?php

namespace Netmex\Lumina\Schema\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\EqualsFilter;
use Netmex\Lumina\Intent\QueryIntent;
use Netmex\Lumina\Schema\ArgumentDirective;
use Netmex\Lumina\SchemaSDLContributorInterface;

final class WhereDirective implements ArgumentDirective, SchemaSDLContributorInterface
{
    public function name(): string
    {
        return 'where';
    }

    public function definition(): string
    {
        return <<<'GRAPHQL'
            directive @where(
                on: String
            ) on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    public function applyToArgument(QueryIntent $intent, InputValueDefinitionNode $argument, FieldDefinitionNode $field, ObjectTypeDefinitionNode $parentType): void {
        $intent->filters[] = new EqualsFilter(
            argument: $argument->name->value,
            column: $argument->name->value
        );
    }
}
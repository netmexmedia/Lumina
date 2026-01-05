<?php

namespace Netmex\Lumina\Directives\Definition;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Directives\ArgumentDirective;
use Netmex\Lumina\Intent\Builder\IntentBuilderInterface;
use Netmex\Lumina\Intent\EqualsFilter;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Schema\SchemaSDLContributorInterface;

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

    public function applyToArgument(Intent $intent, InputValueDefinitionNode $argument, FieldDefinitionNode $field, ObjectTypeDefinitionNode $parentType): void {
        $intent->filters[] = new EqualsFilter(
            argument: $argument->name->value,
            column: $argument->name->value
        );
    }

    // TODO hardcoded resolver registration, needs to be dynamic
    // Should also be registered
    public function intent(IntentBuilderInterface $builder, InputValueDefinitionNode $argument): void
    {
        $builder->addFilter(
            new EqualsFilter(
                argument: $argument->name->value,
                column: $argument->name->value
            )
        );
    }

    public function resolver(): QueryExecutorInterface
    {
        return new AllExecutor($this->serializer);
    }
}
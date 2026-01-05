<?php

namespace Netmex\Lumina\Directives\Definition;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use Netmex\Lumina\Directives\DirectiveContext;
use Netmex\Lumina\Directives\FieldDirective;
use Netmex\Lumina\Execution\AllExecutor;
use Netmex\Lumina\Execution\QueryExecutorInterface;
use Netmex\Lumina\Intent\Builder\IntentBuilderInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Schema\SchemaSDLContributorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package Netmex\Lumina\Directives\Definition
 * So what does a directive do?
 * It modifies the Intent associated with a field.
 *
 * For example, the @all directive indicates that the field should return all records of a certain model.
 *
 * So what should it contain?
 * Well we need to know the intent.
 * We also need to define the directive in SDL.
 * And we need to apply the directive to the field.
 * We also want to know is execution if we need to create a resolver for this field.
 */
final class AllDirective implements FieldDirective, SchemaSDLContributorInterface
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

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

    public function intent(IntentBuilderInterface $builder, DirectiveContext $context): void
    {
        $builder->strategy(Intent::STRATEGY_ALL);

        $namedType = $this->unwrapType($context->getParentNode()->type);
        $graphqlType = $namedType->name->value;

        // TODO mapping from GraphQL type to Entity class
        $builder->model('App\\Entity\\' . $graphqlType);
    }

    // Resolver is a special kind of thing, Because not all directives need resolvers
    // The reason why we do it here is because the directive defines the execution strategy
    public function resolver(): QueryExecutorInterface
    {
        return new AllExecutor($this->serializer);
    }

    // Would make sense as a utility function somewhere
    private function unwrapType(TypeNode $type): NamedTypeNode
    {
        while (!$type instanceof NamedTypeNode) {
            $type = $type->type;
        }

        return $type;
    }
}
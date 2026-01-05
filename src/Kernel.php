<?php

namespace Netmex\Lumina;

use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Netmex\Lumina\Schema\Builder\SchemaBuilder;

readonly class Kernel
{
    private SchemaBuilderInterface $schemaBuilder;
    private ContextBuilderInterface $contextBuilder;

    public function __construct(SchemaBuilder $schemaBuilder, ContextBuilderInterface $contextBuilder) {
        $this->schemaBuilder = $schemaBuilder;
        $this->contextBuilder = $contextBuilder;
    }

    public function execute(GraphQLRequest $request): ExecutionResult
    {
        $schema = $this->schemaBuilder->schema();
        $context = $this->contextBuilder->build();

        $result = GraphQL::executeQuery(
            schema: $schema,
            source: $request->query,
            rootValue: null,
            contextValue: $context,
            variableValues: $request->variables,
            operationName: $request->operationName,
            fieldResolver: null,
            validationRules: null
        );

        return $result;
    }
}
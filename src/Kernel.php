<?php

declare(strict_types=1);

namespace Netmex\Lumina;

use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use Netmex\Lumina\Contracts\ContextBuilderInterface;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Netmex\Lumina\Schema\Compiler\FieldResolverCompiler;
use Netmex\Lumina\Schema\Compiler\IntentCompiler;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;

readonly class Kernel
{
    private SchemaSourceRegistry $schemaSourceRegistry;
    private IntentCompiler $intentCompiler;
    private FieldResolverCompiler $fieldResolverCompiler;
    private ContextBuilderInterface $contextBuilder;

    public function __construct(SchemaSourceRegistry $schemaSourceRegistry, IntentCompiler $intentCompiler, FieldResolverCompiler $fieldResolverCompiler, ContextBuilderInterface $contextBuilder) {
        $this->schemaSourceRegistry = $schemaSourceRegistry;
        $this->intentCompiler = $intentCompiler;
        $this->fieldResolverCompiler = $fieldResolverCompiler;

        $this->contextBuilder = $contextBuilder;
    }

    public function execute(GraphQLRequest $request): ExecutionResult
    {
        $schema = $this->schemaSourceRegistry->schema();
        $context = $this->contextBuilder->build();

        $this->intentCompiler->compile();
        $this->fieldResolverCompiler->compile();

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
<?php

declare(strict_types=1);

namespace Netmex\Lumina;

use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use Netmex\Lumina\Contracts\ContextBuilderInterface;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Netmex\Lumina\Schema\Execution\ExecutionResolver;
use Netmex\Lumina\Schema\SchemaCompiler;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;

readonly class Kernel
{
    private SchemaSourceRegistry $schemaSourceRegistry;
    private SchemaCompiler $schemaCompiler;
    private ExecutionResolver $fieldResolverCompiler;
    private ContextBuilderInterface $contextBuilder;

    public function __construct(SchemaSourceRegistry $schemaSourceRegistry, SchemaCompiler $schemaCompiler, ExecutionResolver $fieldResolverCompiler, ContextBuilderInterface $contextBuilder) {
        $this->schemaSourceRegistry = $schemaSourceRegistry;
        $this->schemaCompiler = $schemaCompiler;
        $this->fieldResolverCompiler = $fieldResolverCompiler;

        $this->contextBuilder = $contextBuilder;
    }

    public function execute(GraphQLRequest $request): ExecutionResult
    {
        $this->schemaSourceRegistry->buildDocumentFromSdl();
        $this->schemaCompiler->compile();
        $this->fieldResolverCompiler->register();

        $result = GraphQL::executeQuery(
            schema: $this->schemaSourceRegistry->getSchema(),
            source: $request->query,
            rootValue: null,
            contextValue: $this->contextBuilder->build(),
            variableValues: $request->variables,
            operationName: $request->operationName,
            fieldResolver: null,
            validationRules: null
        );

        return $result;
    }
}
<?php

declare(strict_types=1);

namespace Netmex\Lumina;

use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use Netmex\Lumina\Contracts\ContextBuilderInterface;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Netmex\Lumina\Schema\Execution\ExecutionResolver;
use Netmex\Lumina\Schema\SchemaVisitor;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;

readonly class Kernel
{
    private SchemaVisitor $schemaVisitor;
    private SchemaSourceRegistry $schemaSourceRegistry;
    private ExecutionResolver $fieldResolverCompiler;
    private ContextBuilderInterface $contextBuilder;

    public function __construct(SchemaVisitor $schemaVisitor, SchemaSourceRegistry $schemaSourceRegistry, ExecutionResolver $fieldResolverCompiler, ContextBuilderInterface $contextBuilder) {
        $this->schemaVisitor = $schemaVisitor;

        $this->schemaSourceRegistry = $schemaSourceRegistry;

        $this->fieldResolverCompiler = $fieldResolverCompiler;

        $this->contextBuilder = $contextBuilder;
    }

    public function execute(GraphQLRequest $request): ExecutionResult
    {
        $this->schemaSourceRegistry->buildDocumentFromSdl();
        $this->schemaVisitor->visit();
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
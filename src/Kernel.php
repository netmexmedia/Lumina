<?php

namespace Netmex\Lumina;

use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use Netmex\Lumina\Context\ContextBuilderInterface;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Netmex\Lumina\Schema\Compiler\SchemaCompiler;
use Netmex\Lumina\Schema\SchemaBuilderInterface;

readonly class Kernel
{
    private SchemaCompiler $compiler;
    private ContextBuilderInterface $contextBuilder;

    public function __construct(SchemaCompiler $compiler, ContextBuilderInterface $contextBuilder) {
        $this->compiler = $compiler;
        $this->contextBuilder = $contextBuilder;
    }

    public function execute(GraphQLRequest $request): ExecutionResult
    {
        $schema = $this->compiler->schema();
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